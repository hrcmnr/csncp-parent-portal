<?php
// Include your database connection file
include 'db_connection.php';

// Get the event ID from the query string
$eventid = $_GET['id'];

// Fetch the event details from the database
$query = "SELECT title, description, date, time, location, type, max_slots FROM events WHERE eventid = :eventid";
$stmt = $pdo->prepare($query);
$stmt->execute(['eventid' => $eventid]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

// If the event is not found, handle the error
if (!$event) {
    echo "Event not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']); ?> - Event Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1><?= htmlspecialchars($event['title']); ?></h1>
    <p><strong>Date:</strong> <?= htmlspecialchars($event['date']); ?></p>
    <p><strong>Time:</strong> <?= htmlspecialchars($event['time']); ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['location']); ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($event['type']); ?></p>
    <p><strong>Max Slots:</strong> <?= htmlspecialchars($event['max_slots']); ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($event['description']); ?></p>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
