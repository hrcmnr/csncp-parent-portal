<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

// Database connection
$servername = "localhost"; // Change if necessary
$username = "your_db_username"; // Your database username
$password = "your_db_password"; // Your database password
$dbname = "your_db_name"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eventid = intval($_POST['eventid']);
    
    // Check if the event exists and if there are available slots
    $checkEventSQL = "SELECT max_slots, slots_taken FROM events WHERE eventid = ?";
    $stmt = $conn->prepare($checkEventSQL);
    $stmt->bind_param("i", $eventid);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($max_slots, $slots_taken);
    
    if ($stmt->fetch()) {
        if ($slots_taken < $max_slots) {
            // Update the number of slots taken
            $new_slots_taken = $slots_taken + 1;
            $updateSQL = "UPDATE events SET slots_taken = ? WHERE eventid = ?";
            $updateStmt = $conn->prepare($updateSQL);
            $updateStmt->bind_param("ii", $new_slots_taken, $eventid);
            $updateStmt->execute();

            echo "Successfully enrolled in the event!";
        } else {
            echo "Sorry, this event is fully booked.";
        }
    } else {
        echo "Event not found.";
    }
    
    $stmt->close();
}

// Close the connection
$conn->close();
?>
