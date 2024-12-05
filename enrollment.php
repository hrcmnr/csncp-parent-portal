<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Assuming username is stored in session when user logs in
$username = $_SESSION['username'];

// Fetch all future events with available slots from the database
try {
    $sql = "SELECT e.*, 
                   (SELECT COUNT(*) FROM enrollments WHERE event_id = e.eventid) AS enrolled_count 
            FROM events e 
            WHERE e.date >= CURDATE()"; // Only select future events
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle event selection and enrollment
$event = null;
$alreadyEnrolled = false;
$slotsFilled = false;

if (isset($_GET['eventid'])) {
    $eventid = intval($_GET['eventid']);

    // Fetch the selected event from the database
    try {
        $sql = "SELECT e.*, 
                       (SELECT COUNT(*) FROM enrollments WHERE event_id = e.eventid) AS enrolled_count 
                FROM events e 
                WHERE e.eventid = :eventid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['eventid' => $eventid]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure $event is not false before accessing its elements
        if ($event) {
            // Check if slots are filled
            $slotsFilled = $event['enrolled_count'] >= $event['max_slots'];
        } else {
            echo "<div class='alert alert-danger'>Event not found or has been removed.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error fetching event details: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }

    // Check if the user is already enrolled in this event if $event is valid
    if ($event) {
        try {
            $sql = "SELECT * FROM enrollments WHERE username = :username AND event_id = :event_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'event_id' => $eventid]);
            $alreadyEnrolled = $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error checking enrollment: " . htmlspecialchars($e->getMessage()) . "</div>";
            exit;
        }
    }
}

// Handle enrollment if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eventid']) && !$alreadyEnrolled && !$slotsFilled) {
    $eventid = intval($_POST['eventid']);

    // Insert enrollment record into the database
    try {
        $sql = "INSERT INTO enrollments (username, event_id, status) VALUES (:username, :event_id, 'Enrolled')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username, 'event_id' => $eventid]);

        // Redirect to history page after enrollment
        header("Location: history.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error enrolling in event: " . htmlspecialchars($e->getMessage()) . "</div>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Events</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Enroll in Events</h1>
        
        <form method="GET" action="" class="mb-4">
            <div class="form-group">
                <label for="event">Select an Event:</label>
                <select name="eventid" id="event" class="form-control" required onchange="this.form.submit()">
                    <option value="">-- Select Event --</option>
                    <?php if (isset($events) && is_array($events)): ?>
                        <?php foreach ($events as $row): ?>
                            <?php if ($row['enrolled_count'] < $row['max_slots']): ?>
                                <option value="<?php echo htmlspecialchars($row['eventid']); ?>" 
                                    <?php echo (isset($event['eventid']) && $event['eventid'] == $row['eventid']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['title']) . ' - ' . htmlspecialchars($row['date']) . ' at ' . htmlspecialchars($row['time']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </form>

        <!-- Display event details if an event is selected -->
        <?php if (isset($event) && $event && isset($event['eventid'])): ?>
            <div id="event-details" class="card mt-4">
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                    <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                    <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                    <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p class="card-text"><strong>Type:</strong> <?php echo htmlspecialchars($event['type']); ?></p>
                    <p class="card-text"><strong>Max Slots:</strong> <?php echo htmlspecialchars($event['max_slots']); ?></p>
                    <p class="card-text"><strong>Slots Available:</strong> <?php echo $event['max_slots'] - $event['enrolled_count']; ?></p>
                </div>
            </div>

            <!-- Enroll Button -->
            <div class="mt-4">
                <form method="POST" action="">
                    <input type="hidden" name="eventid" value="<?php echo htmlspecialchars($event['eventid'] ?? ''); ?>">
                    <?php if (isset($alreadyEnrolled) && $alreadyEnrolled): ?>
                        <button type="button" class="btn btn-outline-secondary btn-lg" disabled>Already Enrolled</button>
                    <?php elseif (isset($slotsFilled) && $slotsFilled): ?>
                        <button type="button" class="btn btn-outline-secondary btn-lg" disabled>Slots Filled</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary btn-lg">Enroll</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <!-- Display message if no event is selected -->
            <div class="alert alert-info mt-4">Please select an event to view details.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

