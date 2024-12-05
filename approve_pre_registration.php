<?php
session_start();
require 'db_connection.php'; // Include your database connection script

// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); // Redirect to login if not admin
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Fetch the pre-registration details
    $stmt = $pdo->prepare("SELECT * FROM pre_registration WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registration) {
        // Set a default password
        $defaultPassword = '12345'; // Default password

        // Start a database transaction to ensure both inserts succeed or fail together
        $pdo->beginTransaction();

        try {
            // Insert into registered_parents table
            $stmt = $pdo->prepare("
                INSERT INTO registered_parents (parent_first_name, parent_surname, parent_middle_name, 
                                                role, parent_email, child_first_name, child_middle_name, 
                                                child_surname, child_diagnosis) 
                VALUES (:parent_first_name, :parent_surname, :parent_middle_name, 
                        :role, :parent_email, :child_first_name, :child_middle_name, 
                        :child_surname, :child_diagnosis)");
            $stmt->execute([
                'parent_first_name' => $registration['parent_first_name'],
                'parent_surname' => $registration['parent_surname'],
                'parent_middle_name' => $registration['parent_middle_name'], // This should now exist
                'role' => $registration['role'], // Ensure this value is fetched correctly
                'parent_email' => $registration['parent_email'],
                'child_first_name' => $registration['child_first_name'], // Adjust according to your database schema
                'child_middle_name' => $registration['child_middle_name'], // Added to ensure child_middle_name is passed
                'child_surname' => $registration['child_surname'], // Ensure this is fetched correctly
                'child_diagnosis' => $registration['child_diagnosis'],
            ]);

            // Insert into users table for login credentials
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, role, password, needs_password_change) 
                VALUES (:username, :email, :role, :password, :needs_password_change)");
            $stmt->execute([
                'username' => $registration['parent_first_name'] . ' ' . $registration['parent_surname'], // Use parent names for the username
                'email' => $registration['parent_email'],
                'role' => 'parent', // Default role for parents
                'password' => password_hash($defaultPassword, PASSWORD_DEFAULT), // Hash the default password
                'needs_password_change' => true // Flag for password change on first login
            ]);

            // Update pre-registration as approved
            $stmt = $pdo->prepare("UPDATE pre_registration SET is_approved = TRUE WHERE id = :id");
            $stmt->execute(['id' => $id]);

            // Commit the transaction
            $pdo->commit();

            // Redirect back to the admin dashboard
            header('Location: admin_dashboard.php');
            exit();
        } catch (Exception $e) {
            // Rollback the transaction on error
            $pdo->rollBack();
            echo "Failed to approve registration: " . $e->getMessage();
        }
    } else {
        echo "Pre-registration not found.";
    }
} else {
    echo "Invalid request.";
}
?>
