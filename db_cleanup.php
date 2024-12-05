<?php
require 'db_connection.php'; // Include your database connection script

try {
    $sql = "DELETE FROM requests WHERE expiration_date < NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Error cleaning up expired requests: " . $e->getMessage();
}
?>
