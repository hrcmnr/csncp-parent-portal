<?php
// db_connection.php
$host = 'localhost'; // Database host
$dbname = 'csn-parent-portal'; // database name
$user = 'root'; // Database username 
$password = ''; // Database password

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>