<?php
session_start();
require 'db_connection.php'; // Include the database connection

// Get the username from the session
$username = $_SESSION['username'];

// Prepare the SQL statement to fetch all requests for the logged-in user
$sql = "SELECT * FROM requests WHERE username = :username ORDER BY date DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':username', $username);
$stmt->execute();
$allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'parent_sidebar.php'; ?> <!-- Include the sidebar here -->

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Inbox</h1>
    <div class="row">
        <?php if (empty($allRequests)): ?>
            <div class="col-md-12">
                <div class="alert alert-warning" role="alert">
                    No requests found.
                </div>
            </div>
        <?php else: 
            foreach ($allRequests as $request): ?>
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
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
