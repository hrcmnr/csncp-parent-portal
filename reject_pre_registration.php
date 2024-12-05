<?php
session_start();
require 'db_connection.php'; // Include your database connection script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Delete the pre-registration
    $stmt = $pdo->prepare("DELETE FROM pre_registration WHERE id = :id");
    $stmt->execute(['id' => $id]);

    header('Location: admin_dashboard.php'); // Redirect back to the admin dashboard
    exit();
} else {
    echo "Invalid request.";
}
?>
