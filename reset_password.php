<?php
require 'db_connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $query = $conn->prepare("SELECT username FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $query->execute([$token]);

    if ($query->rowCount() > 0) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Update the password in the database
            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $update->execute([$password, $token]);

            echo "Password updated successfully!";
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>

<form action="" method="POST">
    <label for="password">New Password:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Reset Password</button>
</form>
