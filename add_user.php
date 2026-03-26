<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'gender' => $_POST['gender'] ?? '',
        'role' => $_POST['role'] ?? '',
        'address' => trim($_POST['address']),
        'status' => 'active'
    ];

    if (in_array('', $data)) {
        $errors[] = "All fields are required.";
    } else {
        $result = $userObj->register($data);
        if ($result === true) {
            $_SESSION['success_msg'] = "New user successfully added!";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $errors[] = $result;
        }
    }
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
    <title>Add User | Admin</title>
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
                
                <button id="themeToggleBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Toggle Dark/Light Mode">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <a href="logout.php" class="btn btn-logout text-decoration-none" title="Logout"><i class="fa-solid fa-power-off"></i></a>
            </div>
        </div>
    </header>

    <ul class="nav nav-pills custom-navbar">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="add_user.php"><i class="fa-solid fa-user-plus me-1"></i> Add User</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fa-solid fa-user-gear me-1"></i> My Profile</a></li>
    </ul>

    <main class="container pb-5" style="max-width: 700px;">
        <div class="card stat-card mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-success"><i class="fa-solid fa-user-plus me-2"></i> Register New User</h5>
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
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">LAST NAME</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">TEMPORARY PASSWORD</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">GENDER</label>
                            <select name="gender" class="form-select" required>
                                <option value="" selected disabled>Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">ROLE ASSIGNMENT</label>
                            <select name="role" class="form-select" required>
                                <option value="" selected disabled>Select...</option>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">ADDRESS</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold py-2">Create Account</button>
                </form>
            </div>
        </div>
    </main>
    
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