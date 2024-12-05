<?php
require 'send_email.php'; 
require 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $query = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $query->execute([$email]);

    if ($query->rowCount() > 0) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $resetLink = "http://yourwebsite.com/reset_password.php?token=$token";

        // Store the token in the database
        $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->execute([$token, $expiry, $email]);

        // Send the reset link to the user
        $subject = "Password Reset Request";
        $content = "
            <p>Hello,</p>
            <p>You requested a password reset. Click the link below to reset your password:</p>
            <a href='$resetLink'>$resetLink</a>
            <p>If you did not request this, please ignore this email.</p>
        ";

        if (sendEmail($email, $subject, $content)) {
            echo "Password reset link sent! Please check your email.";
        } else {
            echo "Failed to send email. Please try again later.";
        }
    } else {
        echo "No account found with this email.";
    }
}
