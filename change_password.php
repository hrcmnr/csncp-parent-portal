<?php
session_start();
require 'db_connection.php'; // Include your database connection script

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve new password from the form submission
    $newPassword = $_POST['new_password'];
    $email = $_SESSION['email'];

    // Hash the new password using bcrypt
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password in the database and set needs_password_change to FALSE
    $stmt = $pdo->prepare("UPDATE users SET password = :password, needs_password_change = FALSE WHERE email = :email");

    // Execute the query with the hashed password and email
    if ($stmt->execute(['password' => $hashedPassword, 'email' => $email])) {
        // Password updated successfully, redirect to index page or wherever needed
        header('Location: index.php');
        exit();
    } else {
        // Handle error if the password was not updated
        echo "Error updating password. Please try again.";
    }
}
?>
<!-- HTML form for password change -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Change Password</h2>
        <form action="" method="POST" class="shadow p-4 rounded bg-white">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Change Password</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
