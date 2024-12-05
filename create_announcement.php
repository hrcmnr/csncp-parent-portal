<?php
// Include database connection
include 'db_connection.php';

// Initialize a variable for success/error messages
$message = '';

try {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate input fields
        if (!empty($_POST['title']) && !empty($_POST['body']) && !empty($_POST['date']) && !empty($_POST['expiration'])) {
            $title = trim($_POST['title']);
            $body = trim($_POST['body']);
            $date = $_POST['date'];
            $expiration_option = $_POST['expiration'];

            // Calculate expiration date
            $expiration_date = null;
            switch ($expiration_option) {
                case '1_week':
                    $expiration_date = date('Y-m-d', strtotime('+1 week'));
                    break;
                case '1_month':
                    $expiration_date = date('Y-m-d', strtotime('+1 month'));
                    break;
                case '6_months':
                    $expiration_date = date('Y-m-d', strtotime('+6 months'));
                    break;
                default:
                    $message = "Invalid expiration option selected.";
            }

            if ($expiration_date) {
                // Insert the announcement into the database
                $sql = "INSERT INTO announcements (title, body, date, expiration_date) 
                        VALUES (:title, :body, :date, :expiration_date)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':body', $body);
                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':expiration_date', $expiration_date);

                if ($stmt->execute()) {
                    $message = "Announcement created successfully!";
                } else {
                    $message = "Error saving the announcement: " . $stmt->errorInfo()[2];
                }
            }
        } else {
            $message = "All fields are required.";
        }
    }

    // Fetch existing announcements where expiration date is not in the past
    $sql = "SELECT * FROM announcements WHERE expiration_date >= CURDATE() ORDER BY date DESC";
    $stmt = $pdo->query($sql);
    $announcements = $stmt->fetchAll();
} catch (Exception $e) {
    $message = "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Announcements</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Announcements</h1>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <button class="btn btn-info btn-lg mb-4" data-toggle="modal" data-target="#announcementModal">Create Announcement</button>

        <!-- Announcements Table -->
        <div class="table-responsive">
    <table class="table table-hover text-center align-middle shadow-sm bg-white rounded">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Date</th>
                <th>Expiration Date</th>
                <th>Actions</th> <!-- Added column for action buttons -->
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($announcements as $announcement): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                    <td><?php echo htmlspecialchars(date("F j, Y", strtotime($announcement['date']))); ?></td>
                    <td><?php echo htmlspecialchars(date("F j, Y - g:i A", strtotime($announcement['expiration_date']))); ?></td>
                    <td>
                        <button class="btn btn-outline-info view-announcement" 
                            data-title="<?php echo htmlspecialchars($announcement['title']); ?>" 
                            data-body="<?php echo htmlspecialchars($announcement['body']); ?>" 
                            data-date="<?php echo htmlspecialchars($announcement['date']); ?>">
                            View
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <!-- Create Announcement Modal -->
        <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="announcementModalLabel">Create Announcement</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="expiration">Expiration:</label>
                                <select id="expiration" name="expiration" class="form-control" required>
                                    <option value="1_week">1 Week</option>
                                    <option value="1_month">1 Month</option>
                                    <option value="6_months">6 Months</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="body">Announcement Body:</label>
                                <textarea id="body" name="body" rows="5" class="form-control" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Announcement Modal -->
<div class="modal fade" id="viewAnnouncementModal" tabindex="-1" aria-labelledby="viewAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Announcement Title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modalDate">Date:</p>
                <p id="modalBody"></p>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
<script>
    document.querySelectorAll('.view-announcement').forEach(function (button) {
        button.addEventListener('click', function () {
            // Populate modal content with data attributes
            document.getElementById('modalTitle').textContent = this.dataset.title;
            document.getElementById('modalDate').textContent = "Date: " + this.dataset.date;
            document.getElementById('modalBody').textContent = this.dataset.body;

            // Show the modal
            $('#viewAnnouncementModal').modal('show');
        });
    });
</script>
</body>
</html>