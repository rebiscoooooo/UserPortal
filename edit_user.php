<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: admin_dashboard.php"); exit(); }

$edit_id = $_GET['id'];
$errors = [];

$user_data = $userObj->getUserById($edit_id);
if (!$user_data) { header("Location: admin_dashboard.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'gender' => $_POST['gender'],
        'role' => $_POST['role'],
        'status' => $_POST['status'],
        'address' => trim($_POST['address']),
        'new_password' => $_POST['new_password']
    ];

    $result = $userObj->updateUser($edit_id, $data);
    if ($result === true) {
        $_SESSION['success_msg'] = "User profile updated successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $errors[] = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/dashboard.css">
</head>
<body>
    <header class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="brand-text">
                <h4>USER MANAGEMENT</h4>
                <p>System Administration</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill badge-role-admin px-3 py-2">ADMINISTRATOR</span>
                <span class="fw-bold text-dark">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="btn btn-logout text-decoration-none" title="Logout"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </header>

    <ul class="nav nav-pills custom-navbar">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="add_user.php"><i class="fa-solid fa-user-plus me-1"></i> Add User</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fa-solid fa-user-gear me-1"></i> My Profile</a></li>
    </ul>

    <main class="container pb-5" style="max-width: 700px;">
        <div class="card stat-card">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-user-pen me-2"></i> Edit User Records</h5>
                <span class="badge bg-light text-dark border">ID: 26-<?php echo str_pad($user_data['id'], 4, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="card-body border-top pt-4">
                <?php 
                if (!empty($errors)) {
                    echo "<div class='alert alert-danger py-2 border-0 shadow-sm'><ul class='mb-0'>";
                    foreach ($errors as $error) echo "<li>{$error}</li>";
                    echo "</ul></div>";
                }
                ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">FIRST NAME</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">LAST NAME</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">OVERRIDE PASSWORD <span class="text-secondary fw-normal">(Optional)</span></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted">GENDER</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?php echo $user_data['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $user_data['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $user_data['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted">ROLE</label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?php echo $user_data['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user_data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted">STATUS</label>
                            <select name="status" class="form-select" required>
                                <option value="active" <?php echo $user_data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $user_data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">ADDRESS</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user_data['address']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>