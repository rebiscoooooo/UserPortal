<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');
$success_msg = "";
$error_msg = "";

$user_data = $userObj->getUserById($user_id);

if (isset($_POST['update_profile'])) {
    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'address' => trim($_POST['address'])
    ];

    if ($userObj->updateProfileBasic($user_id, $data)) {
        $success_msg = "Profile updated successfully!";
        $user_data['first_name'] = $data['first_name'];
        $user_data['last_name'] = $data['last_name'];
        $user_data['address'] = $data['address'];
        $_SESSION['user_name'] = $data['first_name']; 
    } else {
        $error_msg = "Failed to update profile.";
    }
}

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_new_password'];

    if (password_verify($current, $user_data['password'])) {
        if (strlen($new) >= 8 && $new === $confirm) {
            if ($userObj->updatePassword($user_id, $new)) {
                $success_msg = "Password changed successfully!";
                $user_data['password'] = password_hash($new, PASSWORD_DEFAULT);
            } else {
                $error_msg = "Failed to update password.";
            }
        } else {
            $error_msg = "New password must be at least 8 characters and match confirmation.";
        }
    } else {
        $error_msg = "Current password is incorrect.";
    }
}

if (isset($_POST['delete_account'])) {
    $userObj->deleteUser($user_id);
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
    <title>My Profile | User Management</title>
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
                <p><?php echo $is_admin ? 'System Administration' : 'User Portal'; ?></p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill <?php echo $is_admin ? 'badge-role-admin' : 'badge-role-user'; ?> px-3 py-2">
                    <?php echo $is_admin ? 'ADMINISTRATOR' : 'USER'; ?>
                </span>
                <span class="fw-bold text-dark">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                
                <button id="themeToggleBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Toggle Dark/Light Mode">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <a href="logout.php" class="btn btn-logout text-decoration-none" title="Logout"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </header>

    <ul class="nav nav-pills custom-navbar">
        <?php if ($is_admin): ?>
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="add_user.php"><i class="fa-solid fa-user-plus me-1"></i> Add User</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="user_dashboard.php"><i class="fa-solid fa-house-user me-1"></i> Dashboard</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="fa-solid fa-user-gear me-1"></i> My Profile</a></li>
    </ul>

    <div class="container pb-5" style="max-width: 800px;">
        
        <?php if($success_msg): ?>
            <div class='alert alert-success alert-dismissible fade show border-0 shadow-sm'><i class="fa-solid fa-circle-check me-2"></i><?php echo $success_msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class='alert alert-danger alert-dismissible fade show border-0 shadow-sm'><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo $error_msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card stat-card mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-regular fa-id-card me-2"></i> Update Profile Information</h5>
            </div>
            <div class="card-body border-top pt-4">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">FIRST NAME</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">LAST NAME</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">EMAIL (READ-ONLY)</label>
                        <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">ADDRESS</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user_data['address']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary w-100 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Profile Changes</button>
                </form>
            </div>
        </div>

        <div class="card stat-card mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-warning"><i class="fa-solid fa-lock me-2"></i> Change Password</h5>
            </div>
            <div class="card-body border-top pt-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CURRENT PASSWORD</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NEW PASSWORD</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">CONFIRM NEW PASSWORD</label>
                        <input type="password" name="confirm_new_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-warning w-100 fw-semibold"><i class="fa-solid fa-key me-1"></i> Update Password</button>
                </form>
            </div>
        </div>

        <div class="card stat-card border border-danger border-opacity-25 shadow-sm mb-5">
            <div class="card-body text-center py-4">
                <div class="icon-box bg-danger bg-opacity-10 text-danger mx-auto mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h5 class="text-danger fw-bold mb-2">Delete Account</h5>
                <p class="text-muted small px-3">Once you delete your account, there is no going back. Please be certain.</p>
                <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                    <button type="submit" name="delete_account" class="btn btn-outline-danger px-4 rounded-pill fw-semibold mt-2">Delete My Account</button>
                </form>
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
                
                if(newTheme === 'dark') {
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                }
            });
        });
    </script>
</body>
</html>