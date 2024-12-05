<?php
// Include database connection file
include 'db_connection.php'; // Ensure this file connects to your database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the request_form_id from the POST request
    $request_form_id = isset($_POST['request_form_id']) ? intval($_POST['request_form_id']) : 0;

    // Check if request_form_id is valid
    if ($request_form_id > 0) {
        try {
            // Prepare the SQL DELETE statement using PDO
            $stmt = $pdo->prepare("DELETE FROM requests_form WHERE request_form_id = :request_form_id");
            // Bind the parameter
            $stmt->bindParam(':request_form_id', $request_form_id, PDO::PARAM_INT);

            // Execute the statement
            if ($stmt->execute()) {
                // Redirect back to the pending requests page with a success message
                header("Location: pending_requests.php?message=Request deleted successfully.");
                exit();
            } else {
                // Redirect back with an error message
                header("Location: pending_requests.php?error=Error deleting request.");
                exit();
            }
        } catch (PDOException $e) {
            // Handle any errors during the execution
            header("Location: pending_requests.php?error=" . urlencode("Database error: " . $e->getMessage()));
            exit();
        }
    } else {
        // Redirect back with an error if the ID is invalid
        header("Location: pending_requests.php?error=Invalid request ID.");
        exit();
    }
}
?>