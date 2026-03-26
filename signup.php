<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect variables
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
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (in_array('', $data) || empty($confirm_password)) $errors[] = "All fields are required.";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($data['password']) < 8) $errors[] = "Password must be at least 8 characters long.";
    if ($data['password'] !== $confirm_password) $errors[] = "Password and Confirm Password must match.";

    if (empty($errors)) {
        $registerResult = $userObj->register($data);
        if ($registerResult === true) {
            $success = "Registration successful! You can now <a href='login.php'>Login</a>.";
        } else {
            $errors[] = $registerResult; // Outputs email exist error or DB error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | User Management</title>
    <link rel="stylesheet" href="assets/signup.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="main-container py-5">
    <div class="login-card" style="max-width: 600px;">
        <h2 class="text-center mb-4">Create Account</h2>

        <?php 
        if (!empty($errors)) {
            echo "<div class='alert alert-danger py-2'><ul>";
            foreach ($errors as $error) {
                echo "<li>{$error}</li>";
            }
            echo "</ul></div>";
        }
        if ($success) {
            echo "<div class='alert alert-success py-2'>{$success}</div>";
        }
        ?>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">FIRST NAME</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="Enter your first name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">LAST NAME</label>
                    <input type="text" name="last_name" class="form-control" required placeholder="Enter your last name">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">EMAIL</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email address">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">PASSWORD</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter a password">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">CONFIRM PASSWORD</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm your password">
                </div>
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
                    <label class="form-label small fw-bold text-muted">ROLE</label>
                    <select name="role" class="form-select" required>
                        <option value="" selected disabled>Select...</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">ADDRESS</label>
                <input type="text" name="address" class="form-control"  placeholder="Enter your address">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
            <div class="footer-links text-center mt-3">
                <a href="login.php" style="text-decoration:none;">Already have an account? Login</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>