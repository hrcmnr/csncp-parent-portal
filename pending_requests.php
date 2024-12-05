<?php
// Start the session
session_start();

// Include database connection
include 'db_connection.php';

// Fetch all records from the requests_form table
try {
    $sql = "SELECT request_form_id, username, request_date, request_type, description, status FROM requests_form ORDER BY request_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching requests: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

// Handle actions (Done or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $requestId = $_POST['request_form_id'];

    try {
        if ($action === 'done') {
            // Mark request as Done
            $sql = "UPDATE requests_form SET status = 'Done' WHERE request_form_id = :id";
        } elseif ($action === 'delete') {
            // Delete the request from the database
            $sql = "DELETE FROM requests_form WHERE request_form_id = :id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: pending_requests.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error updating request: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Pending Requests</h1>

        <?php if ($requests && count($requests) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle shadow-sm bg-white rounded">
                    <thead class="table-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Username</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php $index = 1; ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td class="text-center"><?php echo $index++; ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($request['username']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars(date("F j, Y", strtotime($request['request_date']))); ?>
                        <td class="text-center"><?php echo htmlspecialchars($request['request_type']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($request['description']); ?></td>
                        <td class="text-center">
                            <?php if ($request['status'] === 'Pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="request_form_id" value="<?php echo htmlspecialchars($request['request_form_id']); ?>">
                                    <input type="hidden" name="action" value="done">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Mark as Done
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="request_form_id" value="<?php echo htmlspecialchars($request['request_form_id']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            <?php elseif ($request['status'] === 'Done'): ?>
                                <span class="badge badge-success">Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending requests found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.2.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
