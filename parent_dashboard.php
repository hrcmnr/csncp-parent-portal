<?php
include 'session_start.php';
require 'db_connection.php'; // Ensure $pdo is correctly initialized

// Fetch the announcements from the database
try {
    $sql = "SELECT title, body, date FROM announcements ORDER BY date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching announcements: " . $e->getMessage();
}

// Check if username is available in the session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Fetch requests for the logged-in user
    try {
        $sql = "SELECT * FROM requests WHERE username = :username ORDER BY date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching user requests: " . $e->getMessage();
    }
} else {
    $requests = [];
    echo "User is not logged in.";
}
// Fetch the most recently added event based on the eventid (assuming it's auto-incremented)
try {
    $sql = "SELECT eventid, title, date 
            FROM events 
            ORDER BY eventid DESC 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching most recent event: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
// Function to fetch all requests
function fetchAllRequests($pdo) {
    try {
        $query = "SELECT id, date, username, title, body, file_path FROM requests ORDER BY date DESC";
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching all requests: " . $e->getMessage();
        return [];
    }
}

// Sort the $requests array by date in descending order
usort($requests, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit displayed requests to 3
$displayedRequests = array_slice($requests, 0, 3);
$allRequests = fetchAllRequests($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?> <!-- Include the sidebar here -->

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Dashboard</h1>

    <!--<div class="container mt-4">-->
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h3>Inbox</h3>
            </div>
            <div class="col-md-6 text-right">
                <a href="inbox.php" class="btn btn-secondary">View All</a>
            </div>
        </div>
        
        <div class="row">
            <?php 
            // Limit the number of displayed requests to a maximum of 3 for the dashboard
            $displayedRequests = array_slice($requests, 0, 3); // Use $requests instead of $allRequests
            
            if (empty($requests)): ?>
                <div class="col-md-12">
                    <div class="alert alert-warning" role="alert">
                        No requests found.
                    </div>
                </div>
            <?php else: 
                foreach ($displayedRequests as $request): ?>
                    <div class="col-md-12 mb-3">
                        <div class="card shadow-sm" data-toggle="modal" data-target="#requestModal-<?= htmlspecialchars($request['id']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($request['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Date:</strong> <?= htmlspecialchars($request['date']); ?><br>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Request Details -->
                    <div class="modal fade" id="requestModal-<?= htmlspecialchars($request['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel-<?= htmlspecialchars($request['id']); ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="requestModalLabel-<?= htmlspecialchars($request['id']); ?>"><?= htmlspecialchars($request['title']); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Date:</strong> <?= htmlspecialchars($request['date']); ?></p>
                                    <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($request['body'])); ?></p>
                                    <?php if (!empty($request['file_path'])): ?>
                                        <p><strong>File:</strong> <a href="<?= htmlspecialchars($request['file_path']); ?>" download><?= htmlspecialchars($request['file_path']); ?></a></p>
                                    <?php else: ?>
                                        <p>No File Attached</p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; 
            endif; ?>
        </div>
    </div>

    <!-- Row for Announcements and Upcoming Events -->
    <div class="content p-4">
        <div class="row">
            <div class="col-md-6">
                <h4 class="mt-5 mb-4 d-flex justify-content-between align-items-center">
                    Announcements
                    <a href="all_announcements.php" class="btn btn-link">View All</a>
                </h4>
                <div class="container">
                    <?php
                    // Fetch the most recent announcement
                    $sql = "SELECT * FROM announcements ORDER BY date DESC LIMIT 1"; // Only get the latest announcement
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $recentAnnouncement = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Display the recent announcement
                    if ($recentAnnouncement): ?>
                        <div class='card mb-3 shadow-sm'>
                            <div class='card-body'>
                                <h4 class='card-title' data-toggle='modal' data-target='#announcementModal' data-title='<?= htmlspecialchars($recentAnnouncement['title']); ?>' data-body='<?= htmlspecialchars($recentAnnouncement['body']); ?>'>
                                    <?= htmlspecialchars($recentAnnouncement['title']); ?>
                                </h4>
                                <p class='card-text'>
                                    <strong>Date:</strong> <?= htmlspecialchars($recentAnnouncement['date']); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class='alert alert-info'>No announcements available at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <h4 class="mt-5 mb-4 d-flex justify-content-between align-items-center">
                    Upcoming Events
                    <a href="enrollment.php" class="btn btn-link">View All</a>
                </h4>
                <div class="container">
                    <?php if ($events): ?>
                        <?php foreach ($events as $event): ?>
                            <div class='card mb-3 shadow-sm'>
                                <div class='card-body'>
                                    <a href="enrollment.php?eventid=<?= htmlspecialchars($event['eventid']); ?>" class='card-title'>
                                        <?= htmlspecialchars($event['title']); ?>
                                    </a>
                                    <p class='card-text'><strong>Date:</strong> <?= htmlspecialchars($event['date']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class='alert alert-info'>No upcoming events available at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> 

</div> <!-- Close content -->

    <!-- Announcement Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel">Announcement Title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="announcementBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to handle modal data -->
    <script>
        $('#announcementModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var title = button.data('title'); // Extract info from data-* attributes
            var body = button.data('body'); // Extract info from data-* attributes

            var modal = $(this);
            modal.find('.modal-title').text(title); // Update the modal's title
            modal.find('#announcementBody').text(body); // Update the modal's body
        });
    </script>
</div>
<!-- Bootstrap JS and dependencies (optional) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
