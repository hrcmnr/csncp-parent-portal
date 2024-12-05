<?php
include 'session_start.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-4 d-flex flex-column">
        <!-- Logo -->
        <img src="../parent-portal/img/csnlogo-removebg.png" alt="Admin Logo" class="img-fluid mb-4">
        <h4 class="text-center">Parent Portal - Admin</h4> <!-- Added Parent Portal header -->
        <h2 class="text-center"><?php echo htmlspecialchars($_SESSION['username']); ?></h2> <!-- Changed to h2 for username -->
        <div class="flex-grow-1"> <!-- This wrapper will take up available space -->
    <a href="admin_dashboard.php" class="d-block py-2"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="registered_users.php" class="d-block py-2"><i class="fas fa-users"></i> Registered Users</a>
    <a href="pending_requests.php" class="d-block py-2"><i class="fas fa-file-invoice"></i> Pending Requests</a>
    <a href="create_announcement.php" class="d-block py-2"><i class="fas fa-bullhorn"></i> Announcement</a>
    <a href="create_event.php" class="d-block py-2"><i class="fas fa-calendar-alt"></i> Create Event</a>
    <a href="generate_request.php" class="d-block py-2"><i class="fas fa-file-alt"></i> Generate Request</a>
</div>

<a href="index.php" class="d-block py-2"><i class="fas fa-sign-out-alt"></i> Log out</a>
</div>
</body>
</html>
