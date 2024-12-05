<?php
include 'session_start.php';
include 'db_connection.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php'); // Redirect to login if not admin
    exit();
}

// Fetch counts for registered parents, pre-registrations, and pending requests
$stmt_registered = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'parent'");
$registered_parents_count = $stmt_registered->fetchColumn();

$stmt_pending_approval = $pdo->query("SELECT COUNT(*) as total FROM pre_registration WHERE is_approved = FALSE");
$pending_approvals_count = $stmt_pending_approval->fetchColumn();

$stmt_pending_requests = $pdo->query("SELECT COUNT(*) as total FROM requests_form WHERE status = 'Pending'");
$pending_requests_count = $stmt_pending_requests->fetchColumn();

// Fetch pre-registrations for approval
$stmt = $pdo->prepare("SELECT id, parent_surname, parent_first_name, parent_email FROM pre_registration WHERE is_approved = FALSE");
$stmt->execute();
$pre_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
</head>
<body>

<?php include 'admin_sidebar.php'; ?> 

<div class="container-fluid">
    <div class="content p-4">
        <h1 class="display-6 mb-4">Dashboard</h1>

        <!-- Status Cards with Modern Look -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body bg-light">
                        <h5 class="card-title text-muted">Registered Parents</h5>
                        <p class="display-5 fw-bold"><?php echo htmlspecialchars($registered_parents_count); ?></p>
                    </div>
                    <div class="card-footer text-success fw-semibold">
                        <i class="fas fa-user-check me-2"></i>Total registered parents
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body bg-light">
                        <h5 class="card-title text-muted">Pending Approvals</h5>
                        <p class="display-5 fw-bold"><?php echo htmlspecialchars($pending_approvals_count); ?></p>
                    </div>
                    <div class="card-footer text-warning fw-semibold">
                        <i class="fas fa-hourglass-half me-2"></i>Pre-registrations pending approval
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body bg-light">
                        <h5 class="card-title text-muted">Pending Requests</h5>
                        <p class="display-5 fw-bold"><?php echo htmlspecialchars($pending_requests_count); ?></p>
                    </div>
                    <div class="card-footer text-info fw-semibold">
                        <i class="fas fa-envelope-open-text me-2"></i>Requests awaiting action
                    </div>
                </div>
            </div>
        </div>

        <!-- Table with Pre-Registrations -->
        <h2 class="mt-5 mb-3">Pre-Registrations for Approval</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle shadow-sm bg-white rounded">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Surname</th>
                        <th class="text-center">First Name</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pre_registrations) > 0): ?>
                        <?php foreach ($pre_registrations as $registration): ?>
                            <td class="text-center"><?php echo htmlspecialchars($registration['parent_surname']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($registration['parent_first_name']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($registration['parent_email']); ?></td>
                                <td class="text-center">
                                    <!-- View, Approve, and Reject Buttons with spacing and icons -->
                                    <a href="view_pre_registration.php?id=<?php echo htmlspecialchars($registration['id']); ?>" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form action="approve_pre_registration.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($registration['id']); ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm me-1"><i class="fas fa-check"></i> Approve</button>
                                    </form>
                                    <form action="reject_pre_registration.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($registration['id']); ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No pre-registrations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
