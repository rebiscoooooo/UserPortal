<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = $userObj->getUserById($user_id);

if (!$user_data) {
    header("Location: logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<script>
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/dashboard.css">
</head>

<body>

    <?php if (isset($_SESSION['original_admin_id'])): ?>
    <div class="alert alert-warning text-center fw-bold rounded-0 mb-0 py-2 d-flex justify-content-center align-items-center gap-3 border-bottom shadow-sm">
        <i class="fa-solid fa-user-secret fs-5 text-danger"></i>
        <span>You are currently in <span class="text-danger"><?php echo htmlspecialchars($_SESSION['user_name']); ?>'s Account</span>.</span>
        <a href="switch_account.php?revert=true" class="btn btn-dark btn-sm rounded-pill px-4 fw-semibold shadow-sm">Return to your account</a>
    </div>
    <?php endif; ?>

    <header class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="brand-text">
                <h4>USER MANAGEMENT</h4>
                <p>User Portal</p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill badge-role-user px-3 py-2">
                    USER
                </span>

                <span class="fw-bold text-dark">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>

                <button id="themeToggleBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Toggle Dark/Light Mode">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <a href="logout.php" class="btn btn-logout text-decoration-none" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </header>

    <ul class="nav nav-pills custom-navbar">
        <li class="nav-item"><a class="nav-link active" href="user_dashboard.php"><i class="fa-solid fa-house-user me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fa-solid fa-user-gear me-1"></i> My Profile</a></li>
    </ul>

    <div class="container pb-5" style="max-width: 800px;">
        <div class="card stat-card mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-address-card me-2"></i> Account Overview</h5>
            </div>
            <div class="card-body border-top pt-4">
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">ACCOUNT ID</div>
                    <div class="col-sm-8 fw-semibold text-dark">26-<?php echo str_pad($user_data['id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">FULL NAME</div>
                    <div class="col-sm-8 text-dark"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">EMAIL ADDRESS</div>
                    <div class="col-sm-8 text-dark"><?php echo htmlspecialchars($user_data['email']); ?></div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">GENDER</div>
                    <div class="col-sm-8 text-dark"><?php echo htmlspecialchars($user_data['gender']); ?></div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">ADDRESS</div>
                    <div class="col-sm-8 text-dark"><?php echo htmlspecialchars($user_data['address']); ?></div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">ACCOUNT ROLE</div>
                    <div class="col-sm-8">
                        <span class="badge badge-role-user rounded-pill px-3 py-1"><?php echo strtoupper($user_data['role']); ?></span>
                    </div>
                </div>
                <div class="row mb-3 pb-3 border-bottom border-light">
                    <div class="col-sm-4 text-muted fw-bold small">STATUS</div>
                    <div class="col-sm-8">
                        <?php if ($user_data['status'] == 'active'): ?>
                            <span class="badge bg-success-soft rounded-pill px-3 py-1 border border-success border-opacity-25"><i class="fa-solid fa-circle fa-2xs me-1"></i> Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger-soft rounded-pill px-3 py-1 border border-danger border-opacity-25"><i class="fa-regular fa-circle fa-2xs me-1"></i> Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-4 text-muted fw-bold small">DATE JOINED</div>
                    <div class="col-sm-8 text-dark"><?php echo date("F j, Y, g:i a", strtotime($user_data['created_at'])); ?></div>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-end">
                <a href="profile.php" class="btn btn-primary btn-sm px-4 fw-semibold">Update Details</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            const htmlElement = document.documentElement;

            if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }

            themeToggleBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                if(newTheme === 'dark') themeIcon.classList.replace('fa-moon', 'fa-sun');
                else themeIcon.classList.replace('fa-sun', 'fa-moon');
            });
        });
    </script>
</body>
</html>