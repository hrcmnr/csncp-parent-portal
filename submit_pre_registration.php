<?php
// Start the session (if needed)
session_start();

// Include the database connection file
require 'db_connection.php';

// Initialize an array to hold errors
$errors = [];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Retrieve form input values
    $parentSurname = trim($_POST['surname']);
    $parentFirstName = trim($_POST['first_name']);
    $parentMiddleName = trim($_POST['middle_name']);
    $role = trim($_POST['role']);
    $parentEmail = trim($_POST['email']);
    $childSurname = trim($_POST['child_surname']);
    $childFirstName = trim($_POST['child_first_name']);
    $childMiddleName = trim($_POST['child_middle_name']);
    $childDiagnosis = trim($_POST['child_diagnosis']);

    // Handle parent picture upload
    if (isset($_FILES['parent_picture']) && $_FILES['parent_picture']['error'] === UPLOAD_ERR_OK) {
        $parentPicture = $_FILES['parent_picture']['name'];
        $parentTmpName = $_FILES['parent_picture']['tmp_name'];
        $parentFilePath = "uploads/parents/" . basename($parentPicture);
        move_uploaded_file($parentTmpName, $parentFilePath);
    } else {
        $parentFilePath = null; // If no picture uploaded
        $errors[] = "Parent picture is required.";
    }

    // Handle child picture upload
    if (isset($_FILES['child_picture']) && $_FILES['child_picture']['error'] === UPLOAD_ERR_OK) {
        $childPicture = $_FILES['child_picture']['name'];
        $childTmpName = $_FILES['child_picture']['tmp_name'];
        $childFilePath = "uploads/children/" . basename($childPicture);
        move_uploaded_file($childTmpName, $childFilePath);
    } else {
        $childFilePath = null; // If no picture uploaded
        $errors[] = "Child picture is required.";
    }

    // Check if there are any errors
    if (empty($errors)) {
        // Insert data into database
        try {
            $sql = "INSERT INTO pre_registration 
            (parent_surname, parent_first_name, parent_middle_name, role, parent_email, parent_picture, 
            child_surname, child_first_name, child_middle_name, child_diagnosis, child_picture)
            VALUES (:parent_surname, :parent_first_name, :parent_middle_name, :role, :parent_email, :parent_picture, 
            :child_surname, :child_first_name, :child_middle_name, :child_diagnosis, :child_picture)";
            
            // Prepare the statement
            $stmt = $pdo->prepare($sql);
            
            // Bind values to the placeholders
            $stmt->execute([
                ':parent_surname' => $parentSurname,
                ':parent_first_name' => $parentFirstName,
                ':parent_middle_name' => $parentMiddleName,
                ':role' => $role,
                ':parent_email' => $parentEmail,
                ':parent_picture' => $parentFilePath,
                ':child_surname' => $childSurname,
                ':child_first_name' => $childFirstName,
                ':child_middle_name' => $childMiddleName,
                ':child_diagnosis' => $childDiagnosis,
                ':child_picture' => $childFilePath
            ]);
            
             // Removed the success message
            // echo "<div class='alert alert-success'>Pre-registration submitted successfully!</div>";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Display error messages
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Registration Status</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="css/styles_submit.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Pre-Registration Status</h2>
        <div class="status-card p-4 mt-4">
            <?php
                // Success or error messages will be displayed here
                // Example message
                echo '<div class="alert alert-success" role="alert">Your pre-registration was successful!</div>';
                // Uncomment the next line to display an error message
                // echo '<div class="alert alert-danger" role="alert">There was an error with your pre-registration.</div>';
            ?>
        </div>
        <a href="index.php" class="btn btn-secondary mt-3">Return to Home</a>
    </div>

    <!-- Bootstrap JS and dependencies (Optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

