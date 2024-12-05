<?php
include 'db_connection.php';

// users variable indexes
$users = [];

// search variable
$searchValue = '';

// Check if a search term is submitted
if (isset($_POST['search'])) {
    $searchValue = $_POST['search_value'];

    // Fetch users from the database based on the search term
    $query = "SELECT id, username, email, role FROM users WHERE id LIKE :search_value OR username LIKE :search_value OR email LIKE :search_value OR role LIKE :search_value";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search_value' => '%' . $searchValue . '%']);
} else {
    // Fetch all users if no search term is provided
    $query = "SELECT id, username, email, role FROM users";
    $stmt = $pdo->query($query);
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update the username if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['username'];

    // Check if the new username already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :username AND id != :id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(['username' => $new_username, 'id' => $user_id]);
    $count = $checkStmt->fetchColumn();

    if ($count == 0) {
        // Update the username in the database
        $updateQuery = "UPDATE users SET username = :username WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute(['username' => $new_username, 'id' => $user_id]);
        echo "<div class='alert alert-success'>Username updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>This username is already taken.</div>";
    }

    // Refresh the list of users after the update
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file if needed -->
</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="container-fluid">
        <div class="content p-4">
            <h1 class="display-6 mb-4">Registered Users</h1>

<!-- Button Container -->
<div class="d-flex justify-content-between align-items-center mb-4 w-100">
    <!-- Refresh Button -->
    <form method="POST" class="mb-0">
        <button type="submit" class="btn btn-outline-secondary" name="refresh">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </form>

    <!-- Search Form Container -->
    <div class="ms-auto">
        <form method="POST" class="mb-0">
            <div class="input-group" style="width: 250px;">
                <input type="text" name="search_value" value="<?= htmlspecialchars($searchValue); ?>" class="form-control" placeholder="Search by Acc. ID, Username, Email, or Account Type" aria-label="Search" required>
                <button type="submit" name="search" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle shadow-sm bg-white rounded">
        <thead class="table-light">
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Username</th>
                <th class="text-center">Parent Email</th>
                <th class="text-center">Account Type</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($user['id']); ?></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" class="form-control me-2" readonly required style="width: 150px;">
                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#updateModal-<?= htmlspecialchars($user['id']); ?>">
                                Update
                            </button>
                        </div>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">
                    </td>
                    <td class="text-center"><?= htmlspecialchars($user['email']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($user['role']); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-info" data-toggle="modal" data-target="#viewModal-<?= htmlspecialchars($user['id']); ?>">
                            View Profile
                        </button>
                    </td>
                </tr>
            </div>

<!-- Modal for Viewing User Profile -->
<div class="modal fade" id="viewModal-<?= htmlspecialchars($user['id']); ?>" tabindex="-1" aria-labelledby="viewModalLabel-<?= htmlspecialchars($user['id']); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel-<?= htmlspecialchars($user['id']); ?>">User Profile: <?= htmlspecialchars($user['username']); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                // Fetch profile data for this user
                $username = $user['username'];
                $profileStmt = $pdo->prepare("SELECT * FROM profiles WHERE username = :username");
                $profileStmt->execute([':username' => $username]);
                $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
                ?>

                <form>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Last Name:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['last_name'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group col-md-6">
                        <label>First Name:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['first_name'] ?? ''); ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Middle Name:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['middle_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Birthdate:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['birthdate'] ?? ''); ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Age:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['age'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Nationality:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['nationality'] ?? ''); ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Gender:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['gender'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Religion:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['religion'] ?? ''); ?>" readonly>
                    </div>
                </div>

                    <div class="form-group ">
                        <label>PWD ID:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['pwd_id'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Address:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['address'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Barangay:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['barangay'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Diagnosis:</label>
                        <textarea class="form-control" readonly><?= htmlspecialchars($profile['diagnosis'] ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
                        <!-- Modal for Updating User Profile -->
                        <div class="modal fade" id="updateModal-<?= htmlspecialchars($user['id']); ?>" tabindex="-1" aria-labelledby="updateModalLabel-<?= htmlspecialchars($user['id']); ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel-<?= htmlspecialchars($user['id']); ?>">Update Username for <?= htmlspecialchars($user['username']); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="">
                                            <div class="form-group">
                                                <label for="current_username">Current Username:</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="new_username">New Username</label>
                                                <input type="text" class="form-control" name="username" required>
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- Optional: Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>