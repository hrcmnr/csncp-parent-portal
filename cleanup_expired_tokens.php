<?php
include 'db_connection.php';

// Delete all tokens older than 1 hour
$stmt = $pdo->prepare("DELETE FROM password_reset WHERE created_at < (NOW() - INTERVAL 1 HOUR)");
$stmt->execute();
?>
