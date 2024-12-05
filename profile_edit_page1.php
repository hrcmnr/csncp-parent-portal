<?php
session_start();
require 'db_connection.php'; // Include your database connection script

$updateMessage = ""; // Initialize the update message variable

// Fetch existing profile data for the logged-in user
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE username = :username");
$stmt->execute([':username' => $username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input data
    $gender = $_POST['gender'];
    $religion = htmlspecialchars(trim($_POST['religion']));
    $pwd_id = htmlspecialchars(trim($_POST['pwd_id']));
    $address = htmlspecialchars(trim($_POST['address']));
    $barangay = htmlspecialchars(trim($_POST['barangay']));
    $diagnosis = htmlspecialchars(trim($_POST['diagnosis']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $middle_name = htmlspecialchars(trim($_POST['middle_name']));
    $birthdate = $_POST['birthdate'];
    $age = (int)$_POST['age'];
    $nationality = htmlspecialchars(trim($_POST['nationality']));
    
    // Check if the age is within a valid range
    if ($age < 0 || $age > 120) {
        $updateMessage = "Please enter a valid age.";
    } else {
        // Update profile information in the database
        $sql = "UPDATE profiles 
                SET gender = :gender, religion = :religion, pwd_id = :pwd_id, address = :address, barangay = :barangay, diagnosis = :diagnosis,
                    last_name = :last_name, first_name = :first_name, middle_name = :middle_name, birthdate = :birthdate, age = :age, nationality = :nationality
                WHERE username = :username";
        $stmt = $pdo->prepare($sql);
    
        // Bind form data to SQL statement
        try {
            $stmt->execute([
                ':gender' => $gender,
                ':religion' => $religion,
                ':pwd_id' => $pwd_id,
                ':address' => $address,
                ':barangay' => $barangay,
                ':diagnosis' => $diagnosis,
                ':last_name' => $last_name,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':birthdate' => $birthdate,
                ':age' => $age,
                ':nationality' => $nationality,
                ':username' => $username,
            ]);

            // Check if any rows were affected
            if ($stmt->rowCount() > 0) {
                $updateMessage = "Profile updated successfully!";
            } else {
                // No rows updated, perform an INSERT
                $insertSql = "INSERT INTO profiles (username, gender, religion, pwd_id, address, barangay, diagnosis,
                    last_name, first_name, middle_name, birthdate, age, nationality) 
                    VALUES (:username, :gender, :religion, :pwd_id, :address, :barangay, :diagnosis,
                    :last_name, :first_name, :middle_name, :birthdate, :age, :nationality)";
                $insertStmt = $pdo->prepare($insertSql);
                
                $insertStmt->execute([
                    ':username' => $username,
                    ':gender' => $gender,
                    ':religion' => $religion,
                    ':pwd_id' => $pwd_id,
                    ':address' => $address,
                    ':barangay' => $barangay,
                    ':diagnosis' => $diagnosis,
                    ':last_name' => $last_name,
                    ':first_name' => $first_name,
                    ':middle_name' => $middle_name,
                    ':birthdate' => $birthdate,
                    ':age' => $age,
                    ':nationality' => $nationality,
                ]);

                $updateMessage = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $updateMessage = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Fetch announcements from the database
try {
    $stmt = $pdo->query("SELECT title, body, date FROM announcements ORDER BY date DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching announcements: " . $e->getMessage();
}

// Fetch requests for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE username = :username ORDER BY date DESC");
$stmt->execute([':username' => $username]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events
try {
    $stmt = $pdo->query("SELECT eventid, title, description, date, time, location, type, max_slots FROM events ORDER BY date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . $e->getMessage();
}

// Limit the number of displayed requests to a maximum of 3
$displayedRequests = array_slice($requests, 0, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom CSS -->
</head>
<body>

<?php include 'parent_sidebar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-white text-dark">
            <h3 class="mb-0">Pansariling Impormasyon</h3>
        </div>
        <div class="card-body">
            <form action="profile_edit_page1.php" method="POST">
                <div class="row">
                    <!-- Last Name -->
                    <div class="form-group col-md-6">
                        <label for="last_name">Apelyido ng Bata *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required value="<?= $profile['last_name'] ?? ''; ?>">
                    </div>
                    <!-- First Name -->
                    <div class="form-group col-md-6">
                        <label for="first_name">Pangalan ng Bata *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required value="<?= $profile['first_name'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <!-- Middle Name -->
                    <div class="form-group col-md-6">
                        <label for="middle_name">Gitnang Pangalan ng Bata *</label>
                        <input type="text" id="middle_name" name="middle_name" class="form-control" required value="<?= $profile['middle_name'] ?? ''; ?>">
                    </div>
                    <!-- Birthdate -->
                    <div class="form-group col-md-6">
                        <label for="birthdate">Kaarawan (Buwan, Araw, Taon) *</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" required value="<?= $profile['birthdate'] ?? ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <!-- Age -->
                    <div class="form-group col-md-6">
                        <label for="age">Edad *</label>
                        <input type="number" id="age" name="age" class="form-control" required value="<?= $profile['age'] ?? ''; ?>">
                    </div>
                    <!-- Nationality -->
                    <div class="form-group col-md-6">
                        <label for="nationality">Nasyonalidad *</label>
                        <input type="text" id="nationality" name="nationality" class="form-control" required value="<?= $profile['nationality'] ?? ''; ?>">
                    </div>
                </div>

                 <!-- Kasarian -->
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="gender">Kasarian *</label><br>
                        <input type="radio" id="male" name="gender" value="Lalaki" required <?= (isset($profile['gender']) && $profile['gender'] == 'Lalaki') ? 'checked' : ''; ?>>
                        <label for="male">Lalaki</label>
                        <input type="radio" id="female" name="gender" value="Babae" required <?= (isset($profile['gender']) && $profile['gender'] == 'Babae') ? 'checked' : ''; ?>>
                        <label for="female">Babae</label>
                    </div>
                </div>

                <!-- Relihiyon -->
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="religion">Relihiyon *</label>
                        <input type="text" id="religion" name="religion" class="form-control" required value="<?= $profile['religion'] ?? ''; ?>">
                    </div>
                    <!-- PWD -->
                    <div class="form-group col-md-6">
                        <label for="pwd_id">PWD ID *</label>
                        <input type="text" id="pwd_id" name="pwd_id" class="form-control" value="<?= $profile['pwd_id'] ?? ''; ?>">
                    </div>
                </div>

                <!-- Tirahan -->
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="address">Tirahan *</label>
                        <input type="text" id="address" name="address" class="form-control" required value="<?= $profile['address'] ?? ''; ?>">
                    </div>
                    <!-- Barangay -->
                    <div class="form-group col-md-6">
                        <label for="barangay">Barangay *</label>
                        <input type="text" id="barangay" name="barangay" class="form-control" required value="<?= $profile['barangay'] ?? ''; ?>">
                    </div>
                </div>

                <!-- Diagnosis -->
                <div class="form-group">
                    <label for="diagnosis">Diagnosis *</label>
                    <textarea id="diagnosis" name="diagnosis" class="form-control" required><?= $profile['diagnosis'] ?? ''; ?></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">I-save ang Impormasyon</button>
                </div>
            </form>

            <?php if (!empty($updateMessage)): ?>
                <div class="alert alert-info mt-3">
                    <?= $updateMessage; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
