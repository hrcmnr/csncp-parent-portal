<?php
session_start();
include 'db_connection.php';

$searchValue = '';
$events = [];

// Event creation logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $type = $_POST['type'];
    $max_slots = $_POST['max_slots'];

    $sql = "INSERT INTO events (title, description, date, time, location, type, max_slots) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $date, $time, $location, $type, $max_slots]);
    echo "<script>alert('Event created successfully!');</script>";
}

// Search or list events
if (isset($_POST['search'])) {
    $searchValue = $_POST['search_value'];
    $sql = "SELECT * FROM events 
            WHERE title LIKE :searchValue 
            OR description LIKE :searchValue 
            OR date LIKE :searchValue 
            OR time LIKE :searchValue 
            OR location LIKE :searchValue 
            OR type LIKE :searchValue";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['searchValue' => '%' . $searchValue . '%']);
} else {
    $sql = "SELECT * FROM events";
    $stmt = $pdo->query($sql);
}

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'admin_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Event Management</h1>

        <!-- Button to trigger Create Event modal -->
        <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#createEventModal">
            Create Event
        </button>

        <!-- Search Form 
        <form method="POST" class="mb-4">
            <div class="input-group" style="width: 300px;">
                <input type="text" name="search_value" 
                    value="<?= htmlspecialchars($searchValue); ?>" 
                    class="form-control" placeholder="Search events" required>
                <button type="submit" name="search" class="btn btn-primary">
                    Search
                </button>
            </div>
        </form>-->

        <!-- Event Table -->
        <div class="table-responsive">
            <table class="table table-hover text-center align-middle shadow-sm bg-white rounded">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $number = 1;
                    foreach ($events as $event): ?>
                        <tr>
                            <td><?= $number++; ?></td>
                            <td><?= htmlspecialchars($event['title']); ?></td>
                            <td><?= htmlspecialchars(date("F j, Y", strtotime($event['date']))); ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($event['time']))); ?></td>
                            <td><?= htmlspecialchars($event['location']); ?></td>
                            <td><?= htmlspecialchars($event['type']); ?></td>
                            <td>
                                <a href="view_enrollment.php?eventid=<?= htmlspecialchars($event['eventid']); ?>" class="btn btn-outline-info">View Enrollees</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createEventModalLabel">Create Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="create_event">
                    <div class="form-group">
                        <label for="title">Event Title:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Event Description:</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="time">Time:</label>
                        <input type="time" id="time" name="time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Event Type:</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="Seminar">Seminar</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Training">Training</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="max_slots">Max Slots:</label>
                        <input type="number" id="max_slots" name="max_slots" class="form-control" required min="1">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
