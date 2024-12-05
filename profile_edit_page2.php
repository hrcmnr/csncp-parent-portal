<?php
require 'db_connection.php';
session_start(); // Ensure session is started to use session variables

// Handle form submission for updating profile information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "UPDATE profiles 
            SET gender = :gender, religion = :religion, pwd_id = :pwd_id, address = :address, barangay = :barangay, diagnosis = :diagnosis
            WHERE username = :username";
    $stmt = $pdo->prepare($sql);

    // Bind form data to SQL statement
    $stmt->execute([
        ':gender' => $_POST['gender'],
        ':religion' => $_POST['religion'],
        ':pwd_id' => $_POST['pwd_id'],
        ':address' => $_POST['address'],
        ':barangay' => $_POST['barangay'],
        ':diagnosis' => $_POST['diagnosis'],
        ':username' => $_SESSION['username'],
    ]);

    echo $stmt->rowCount() ? "Profile updated successfully!" : "Error updating profile.";
}

// Fetch announcements
try {
    $stmt = $pdo->query("SELECT title, body, date FROM announcements ORDER BY date DESC");
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching announcements: " . $e->getMessage();
}

// Fetch requests for the logged-in user
$stmt = $pdo->prepare("SELECT * FROM requests WHERE username = :username ORDER BY date DESC");
$stmt->execute([':username' => $_SESSION['username']]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming events
try {
    $stmt = $pdo->query("SELECT eventid, title, description, date, time, location, type, max_slots FROM events ORDER BY date ASC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching events: " . $e->getMessage();
}

// Sort requests by date
usort($requests, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit displayed requests
$displayedRequests = array_slice($requests, 0, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Edit - Page 2</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom CSS -->
</head>
<body>
<?php include 'parent_sidebar.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-white text-dark">
            <h3 class="mb-0">Profile Edit - Page 2</h3>
        </div>
        <div class="card-body">
            <form action="profile_edit_page2.php" method="POST">
                <!-- Gender -->
                <div class="form-group">
                    <label for="gender">Kasarian *</label><br>
                    <input type="radio" id="male" name="gender" value="Lalaki" required>
                    <label for="male">Lalaki</label>
                    <input type="radio" id="female" name="gender" value="Babae" required>
                    <label for="female">Babae</label>
                </div>

                <!-- Religion -->
                <div class="form-group">
                    <label for="religion">Relihiyon *</label>
                    <input type="text" id="religion" name="religion" class="form-control" required>
                </div>

                <!-- PWD ID No -->
                <div class="form-group">
                    <label for="pwd_id">PWD ID No. *</label>
                    <input type="text" id="pwd_id" name="pwd_id" class="form-control" required>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Address *</label>
                    <input type="text" id="address" name="address" class="form-control" required>
                </div>

                <!-- Barangay -->
                <div class="form-group">
                    <label for="barangay">Barangay *</label>
                    <select id="barangay" name="barangay" class="form-control" required>
                        <option value="">Choose</option>
                        <option value="Barangay 1">Barangay 1</option>
                        <option value="Barangay 2">Barangay 2</option>
                        <!-- Add more barangays as needed -->
                    </select>
                </div>

                <!-- Diagnosis/Clinical Impression -->
                <div class="form-group">
                    <label for="diagnosis">Diagnosis/Clinical Impression *</label>
                    <input type="text" id="diagnosis" name="diagnosis" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
            </form>
        </div>
    </div>
    <div class="container mt-3">
    <a href="profile_edit_page1.php" class="btn btn-secondary">← Back to Page 1</a>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
