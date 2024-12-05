<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Initialize variables
$request_date = '';
$request_type = '';
$description = '';
$success_message = ''; // Variable to hold success message
$error_message = ''; // Variable to hold error message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $request_date = $_POST['request_date'];
    $request_type = $_POST['request_type'];
    $description = $_POST['description'];

    // Insert into the `requests_form` table
    try {
        $sql = "INSERT INTO requests_form (username, request_date, request_type, description, status) 
                VALUES (:username, :request_date, :request_type, :description, 'Pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $_SESSION['username'], // Assuming username is stored in session
            'request_date' => $request_date,
            'request_type' => $request_type,
            'description' => $description
        ]);
        
        // Set success message
        $success_message = "Your request has been submitted and is pending review.";
    } catch (PDOException $e) {
        $error_message = "Error submitting request: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch submitted requests
try {
    $sql = "SELECT * FROM requests_form WHERE username = :username ORDER BY request_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $_SESSION['username']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching requests: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Requests</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">My Requests</h1>

        <!-- Display success message -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Display error message -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Button to open the modal -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#requestModal">Submit New Request</button>

        <!-- Modal for submitting a request -->
        <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestModalLabel">Submit Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="request_date">Request Date:</label>
                                <input type="date" name="request_date" id="request_date" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="request_type">Request Type:</label>
                                <select name="request_type" id="request_type" class="form-control" required>
                                    <option value="">-- Select Request Type --</option>
                                    <option value="Activity Report">Activity Report</option>
                                    <option value="Meeting With Teacher">Meeting With Teacher</option>
                                    <option value="Change of Schedule">Change of Schedule</option>
                                    <option value="Certification">Certification</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table to list submitted requests -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Request Date</th>
                    <th>Request Type</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['request_date']) ?></td>
                            <td><?= htmlspecialchars($request['request_type']) ?></td>
                            <td><?= htmlspecialchars($request['description']) ?></td>
                            <td><?= htmlspecialchars($request['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No requests found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
