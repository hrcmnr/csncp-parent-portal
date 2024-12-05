<?php
session_start();
require 'db_connection.php'; // Include your database connection script

// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); // Redirect to login if not admin
    exit();
}

// Fetch the specific pre-registration details based on the provided ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch the pre-registration details from the table
    $stmt = $pdo->prepare("SELECT * FROM pre_registration WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$registration) {
        echo "No pre-registration details found.";
        exit();
    }
} else {
    echo "No pre-registration ID provided.";
    exit();
}

// File upload handling for parent_picture and child_picture
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "uploads/";
    $parent_file = $target_dir . basename($_FILES["parent_picture"]["name"]);
    $child_file = $target_dir . basename($_FILES["child_picture"]["name"]);
    $uploadOkParent = 1;
    $uploadOkChild = 1;

    $parentImageFileType = strtolower(pathinfo($parent_file, PATHINFO_EXTENSION));
    $childImageFileType = strtolower(pathinfo($child_file, PATHINFO_EXTENSION));

    // Check if the parent picture is a real image
    $parentCheck = getimagesize($_FILES["parent_picture"]["tmp_name"]);
    if ($parentCheck !== false) {
        $uploadOkParent = 1;
    } else {
        echo "Parent file is not an image.";
        $uploadOkParent = 0;
    }

    // Check if the child picture is a real image
    $childCheck = getimagesize($_FILES["child_picture"]["tmp_name"]);
    if ($childCheck !== false) {
        $uploadOkChild = 1;
    } else {
        echo "Child file is not an image.";
        $uploadOkChild = 0;
    }

    // Check parent picture file size (limit to 500KB)
    if ($_FILES["parent_picture"]["size"] > 500000) {
        echo "Sorry, your parent file is too large.";
        $uploadOkParent = 0;
    }

    // Check child picture file size (limit to 500KB)
    if ($_FILES["child_picture"]["size"] > 500000) {
        echo "Sorry, your child file is too large.";
        $uploadOkChild = 0;
    }

    // Allow certain file formats (JPEG, PNG, etc.) for both images
    if ($parentImageFileType != "jpg" && $parentImageFileType != "png" && $parentImageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed for the parent picture.";
        $uploadOkParent = 0;
    }
    if ($childImageFileType != "jpg" && $childImageFileType != "png" && $childImageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG, & PNG files are allowed for the child picture.";
        $uploadOkChild = 0;
    }

    // Check if upload for both images is OK
    if ($uploadOkParent == 1 && $uploadOkChild == 1) {
        // Try to move the uploaded parent picture to the server
        if (move_uploaded_file($_FILES["parent_picture"]["tmp_name"], $parent_file)) {
            echo "The parent picture " . htmlspecialchars(basename($_FILES["parent_picture"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading the parent picture.";
        }

        // Try to move the uploaded child picture to the server
        if (move_uploaded_file($_FILES["child_picture"]["tmp_name"], $child_file)) {
            echo "The child picture " . htmlspecialchars(basename($_FILES["child_picture"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading the child picture.";
        }

        // Insert image paths into the database for both parent and child
        $stmt = $pdo->prepare("UPDATE pre_registration SET parent_picture = :parent_picture, child_picture = :child_picture WHERE id = :id");
        $stmt->execute([
            'parent_picture' => $parent_file,
            'child_picture' => $child_file,
            'id' => $id
        ]);
        echo "Image paths for parent and child saved in the database.";
    } else {
        echo "Sorry, there was an error uploading your files.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pre-Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Pre-Registration Details</h2>
        <table class="table table-bordered mt-3">
            <tbody>
                <tr>
                    <th>Parent Surname</th>
                    <td><?php echo htmlspecialchars($registration['parent_surname']); ?></td>
                </tr>
                <tr>
                    <th>Parent First Name</th>
                    <td><?php echo htmlspecialchars($registration['parent_first_name']); ?></td>
                </tr>
                <tr>
                    <th>Parent Middle Name</th>
                    <td><?php echo htmlspecialchars($registration['parent_middle_name']); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?php echo htmlspecialchars($registration['role']); ?></td>
                </tr>
                <tr>
                    <th>Parent Email</th>
                    <td><?php echo htmlspecialchars($registration['parent_email']); ?></td>
                </tr>
                <tr>
                    <th>Parent Picture</th>
                    <td>
                        <?php 
                        if (!empty($registration['parent_picture']) && file_exists($registration['parent_picture'])) {
                            echo '<img src="' . htmlspecialchars($registration['parent_picture']) . '" alt="Parent Picture" style="max-width: 150px;">';
                        } else {
                            echo 'No picture available';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Child Surname</th>
                    <td><?php echo htmlspecialchars($registration['child_surname']); ?></td>
                </tr>
                <tr>
                    <th>Child First Name</th>
                    <td><?php echo htmlspecialchars($registration['child_first_name']); ?></td>
                </tr>
                <tr>
                    <th>Child Middle Name</th>
                    <td><?php echo htmlspecialchars($registration['child_middle_name']); ?></td>
                </tr>
                <tr>
                    <th>Child Diagnosis</th>
                    <td><?php echo htmlspecialchars($registration['child_diagnosis']); ?></td>
                </tr>
                <tr>
                    <th>Child Picture</th>
                    <td>
                        <?php 
                        if (!empty($registration['child_picture']) && file_exists($registration['child_picture'])) {
                            echo '<img src="' . htmlspecialchars($registration['child_picture']) . '" alt="Child Picture" style="max-width: 150px;">';
                        } else {
                            echo 'No picture available';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
