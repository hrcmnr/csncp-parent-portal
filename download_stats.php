<?php
// Ensure the event_id is passed
if (!isset($_GET['event_id'])) {
    exit("Event ID is required.");
}

$event_id = intval($_GET['event_id']);

// Include the database connection
include 'db_connection.php';

// Fetch the event details
try {
    $sql = "SELECT * FROM events WHERE eventid = :event_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['event_id' => $event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        exit("Event not found.");
    }
} catch (PDOException $e) {
    exit("Error fetching event details: " . htmlspecialchars($e->getMessage()));
}

// Fetch enrolled users with their statuses
try {
    $sql = "SELECT username, status FROM enrollments WHERE event_id = :event_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['event_id' => $event_id]);
    $enrolledUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit("Error fetching enrolled users: " . htmlspecialchars($e->getMessage()));
}

// Set headers to download the file as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="event_statistics_' . $event_id . '.csv"');

// Open the output stream to write to the CSV file
$output = fopen('php://output', 'w');

// Add event details as the first section in the CSV
fputcsv($output, ["Event Details"]);
fputcsv($output, ["Title", $event['title']]);
fputcsv($output, ["Date", $event['date']]);
fputcsv($output, ["Location", $event['location']]);
fputcsv($output, ["Description", $event['description']]);
fputcsv($output, []);  // Empty line for separation

// Add the enrollment statistics section
fputcsv($output, ["Enrolled Users Statistics"]);
fputcsv($output, ["Username", "Status"]);

// Loop through enrolled users and add them to the CSV
foreach ($enrolledUsers as $user) {
    fputcsv($output, [$user['username'], $user['status']]);
}

// Close the output stream
fclose($output);
exit;
?>
