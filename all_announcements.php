<?php
session_start();
require 'db_connection.php'; // Include your database connection

// Fetch all announcements from the database
$sql = "SELECT * FROM announcements ORDER BY date DESC"; // Adjust table name if necessary
$stmt = $pdo->prepare($sql);
$stmt->execute();
$allAnnouncements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Announcements</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?> <!-- Include the sidebar here -->

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Announcements</h1>
        <div class="row">
            <?php if (empty($allAnnouncements)): ?>
                <div class="col-md-12">
                    <div class="alert alert-warning" role="alert">
                        No announcements found.
                    </div>
                </div>
            <?php else: 
                foreach ($allAnnouncements as $announcement): ?>
                    <div class="col-md-12 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($announcement['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Date:</strong> <?= htmlspecialchars($announcement['date']); ?><br>
                                    <strong>Message:</strong> <?= nl2br(htmlspecialchars($announcement['body'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; 
            endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
