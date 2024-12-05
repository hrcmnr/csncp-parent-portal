<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Initialize message variables
$success_message = '';
$error_message = '';

// Get the event ID from the URL
if (!isset($_GET['eventid'])) {
    $error_message = "Event ID is required.";
    exit;
}
$eventid = intval($_GET['eventid']);

// Fetch the event details
try {
    $sql = "SELECT * FROM events WHERE eventid = :eventid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['eventid' => $eventid]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $error_message = "Event not found.";
        exit;
    }
} catch (PDOException $e) {
    $error_message = "Error fetching event details: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['status'])) {
    $username = $_POST['username'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE enrollments SET status = :status WHERE username = :username AND event_id = :event_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['status' => $status, 'username' => $username, 'event_id' => $eventid]);
        $success_message = "Status updated successfully.";
    } catch (PDOException $e) {
        $error_message = "Error updating status: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch enrolled users for the specified event
try {
    $sql = "SELECT enrollments.username, enrollments.status 
            FROM enrollments
            JOIN users ON enrollments.username = users.username
            WHERE enrollments.event_id = :event_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['event_id' => $eventid]);
    $enrolledUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching enrolled users: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Users - <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php include 'admin_sidebar.php'; ?> <!-- Include the admin sidebar here -->

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Enrolled Users for Event: <?php echo htmlspecialchars($event['title']); ?></h1>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>

        <!-- Display success message if set -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Display error message if set -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive mt-4">
            <table class="table table-hover table-bordered mx-auto">
                <thead>
                    <tr>
                        <th class="text-center">Username</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($enrolledUsers) > 0): ?>
                        <?php foreach ($enrolledUsers as $user): ?>
                            <tr class="<?php echo htmlspecialchars($user['status']) == 'present' ? 'table-success' : (htmlspecialchars($user['status']) == 'absent' ? 'table-danger' : 'table-secondary'); ?>">
                                <td class="text-center"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($user['status']); ?></td>
                                <td class="text-center">
                                    <form method="POST" action="">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="enrolled" <?php if ($user['status'] === 'enrolled') echo 'selected'; ?>>Enrolled</option>
                                            <option value="present" <?php if ($user['status'] === 'present') echo 'selected'; ?>>Present</option>
                                            <option value="absent" <?php if ($user['status'] === 'absent') echo 'selected'; ?>>Absent</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No users enrolled in this event.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between">
            <a href="create_event.php" class="btn btn-secondary mt-3">Back to Events</a>
            <!-- Printer icon button -->
            <a href="download_stats.php?event_id=<?php echo $eventid; ?>" class="btn btn-outline-success mt-3" id="printButton">
                <i class="fas fa-print"></i> <!-- Printer icon -->
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
