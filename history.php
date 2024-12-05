<?php
// Start the session
session_start();

// Retrieve the username from the session
$username = $_SESSION['username'];

// Include database connection
include 'db_connection.php';

// Set the default order to DESC (Most recent first)
$order = isset($_SESSION['order']) ? $_SESSION['order'] : 'DESC';

// Check if the filter icon was clicked to toggle order
if (isset($_GET['toggle'])) {
    $order = ($order == 'DESC') ? 'ASC' : 'DESC';  // Toggle the order
    $_SESSION['order'] = $order;  // Save the new order in session
}

try {
    // Fetch enrolled events for the current user, ordered by date (most recent first or oldest first depending on order)
    $sql = "SELECT events.title, events.date, events.time, events.location, events.type, enrollments.status
            FROM enrollments
            JOIN events ON enrollments.event_id = events.eventid
            WHERE enrollments.username = :username
            ORDER BY events.date $order";  // Order by most recent or oldest date depending on $order
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $enrolledEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching enrollment history: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment History</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add Font Awesome for the filter icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?> <!-- Include the sidebar here -->

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Enrollment History</h1>

        <!-- Filter Icon with a toggle link -->
        <div class="mb-3">
            <a href="?toggle=1" class="btn btn-outline-secondary">
                <i class="fas fa-filter"></i>
            </a>
        </div>

        <?php if ($enrolledEvents): ?>
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Title</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Time</th>
                        <th class="text-center">Location</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $number = 1; // Initialize row numbering
                    foreach ($enrolledEvents as $event): ?>
                        <tr>
                            <td class="text-center"><?= $number++; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($event['title']); ?></td>
                            <td class="text-center"><?php echo date('F j, Y', strtotime($event['date'])); ?></td>
                            <td class="text-center"><?php echo date('g:i A', strtotime($event['time'])); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($event['location']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($event['type']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($event['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info mt-4">You have not enrolled in any events yet.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
