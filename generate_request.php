<?php
require 'db_connection.php'; // Include your database connection script

// Fetch request details based on an ID sent via AJAX
if (isset($_POST['request_id'])) {
    $requestId = $_POST['request_id'];

    // Query to fetch the request details from the 'requests_from' table
    $stmt = $pdo->prepare("SELECT title, username, body as request_body, expiration_date, file_path FROM requests_from WHERE id = ?");
    $stmt->execute([$requestId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the fetched result as a JSON response
    echo json_encode($result);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $username = $_POST['username'];
    $title = $_POST['title'];
    $body = $_POST['body'] ?? null;
    $expiration_option = $_POST['expiration_date']; // Get the expiration option
    $expiration_date = null;

    // Calculate the expiration date based on the selected option
    switch ($expiration_option) {
        case '1 week':
            $expiration_date = date('Y-m-d H:i:s', strtotime('+1 week'));
            break;
        case '1 month':
            $expiration_date = date('Y-m-d H:i:s', strtotime('+1 month'));
            break;
        case '6 months':
            $expiration_date = date('Y-m-d H:i:s', strtotime('+6 months'));
            break;
        default:
            $expiration_date = date('Y-m-d H:i:s'); // Default to current date and time if invalid option
    }

    $file_path = null;
    $uploadOk = 1;

    // Handle file upload
    if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // Ensure the upload directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size (limit to 5MB)
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
            $uploadOk = 0;
            echo "Sorry, your file is too large.";
        }

        // Allow certain file formats
        if (!in_array($fileType, ['jpg', 'png', 'pdf'])) {
            $uploadOk = 0;
            echo "Sorry, only JPG, PNG & PDF files are allowed.";
        }

        // If everything is ok, try to upload the file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $file_path = $target_file;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Insert data into the database if file validation passed
    if ($uploadOk == 1) {
        try {
            $sql = "INSERT INTO requests (date, username, title, body, expiration_date" . ($file_path ? ", file_path" : "") . ") 
                    VALUES (:date, :username, :title, :body, :expiration_date" . ($file_path ? ", :file_path" : "") . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':expiration_date', $expiration_date);
            if ($file_path) {
                $stmt->bindParam(':file_path', $file_path);
            }
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Please upload a valid file (JPG, PNG, or PDF).";
    }
}

// Fetch all requests
try {
    $sql = "SELECT id, title, username, date, expiration_date, file_path FROM requests";
    $stmt = $pdo->query($sql);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching requests: " . $e->getMessage();
}
// Pagination setup
$limit = 10; // Number of entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Database query to fetch requests with pagination
$query = "SELECT * FROM requests ORDER BY date DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute();
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Request Management</h1>
        <button class="btn btn-info btn-lg" data-toggle="modal" data-target="#generateRequestModal">Generate Request</button>
        <div class="table-responsive">
    <table class="table table-hover text-center align-middle shadow-sm bg-white rounded">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Username</th>
                <th>Date</th>
                <th>Expiration Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php $counter = 1; ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($request['title']) ?></td>
                        <td><?= htmlspecialchars($request['username']) ?></td>
                        <td><?= htmlspecialchars(date("F j, Y", strtotime($request['date']))) ?></td>
                        <td><?= htmlspecialchars(date('F j, Y - g:i A', strtotime($request['expiration_date']))) ?></td>
                        <td>
                            <button class="btn btn-outline-info btn-view-request" 
                                    data-toggle="modal" 
                                    data-target="#viewRequestModal" 
                                    data-request='<?php echo htmlspecialchars(json_encode($request), ENT_QUOTES, 'UTF-8'); ?>'>
                                View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No requests found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Controls -->
<div class="pagination">
    <?php
    // Calculate the total number of requests
    $countQuery = "SELECT COUNT(*) FROM requests";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
    $totalRequests = $countStmt->fetchColumn();
    
    // Calculate total pages
    $totalPages = ceil($totalRequests / $limit);
    
    // Display pagination links
    if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '" class="btn btn-outline-primary">Previous</a>';
    }
    
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page) {
            echo '<span class="btn btn-outline-secondary disabled">' . $i . '</span>';
        } else {
            echo '<a href="?page=' . $i . '" class="btn btn-outline-primary">' . $i . '</a>';
        }
    }

    if ($page < $totalPages) {
        echo '<a href="?page=' . ($page + 1) . '" class="btn btn-outline-primary">Next</a>';
    }
    ?>
</div>

        <!-- Generate Request Modal -->
        <div class="modal fade" id="generateRequestModal" tabindex="-1" aria-labelledby="generateRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="generateRequestModalLabel">Generate Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="generate_request.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="expiration_date">Expiration Date:</label>
                                <select id="expiration_date" name="expiration_date" class="form-control" required>
                                    <option value="1 week">1 Week</option>
                                    <option value="1 month">1 Month</option>
                                    <option value="6 months">6 Months</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="username">Parent Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="body">Request Body:</label>
                                <textarea id="body" name="body" rows="5" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="fileToUpload">Attach File:</label>
                                <input type="file" id="fileToUpload" name="fileToUpload" class="form-control-file">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Request Modal -->
        <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewRequestModalLabel">View Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6>Title:</h6>
                        <p id="view-title"></p>
                        <h6>Username:</h6>
                        <p id="view-username"></p>
                        <h6>Request:</h6>
                        <p id="view-request"></p>
                        <h6>Expiration Date:</h6>
                        <p id="view-expiration-date"></p>
                        <h6>Attached File:</h6>
                        <p id="view-file-path"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Pass request data to modal on click of view button
    $(document).on('click', '.btn-view-request', function () {
        var request = $(this).data('request');
        $('#view-title').text(request.title);
        $('#view-username').text(request.username);
        $('#view-request').text(request.request_body);
        $('#view-expiration-date').text(request.expiration_date);
        if (request.file_path) {
            $('#view-file-path').html('<a href="' + request.file_path + '" download>Download File</a>');
        } else {
            $('#view-file-path').text('No file attached.');
        }
    });
</script>
</body>
</html>
