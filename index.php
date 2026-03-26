<?php
require 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USER MANAGEMENT SYSTEM</title>
    <link rel="stylesheet" href="assets/index.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="main-container">
        <div class="welcome-text">
            <h1>Welcome Back!</h1>
            <p>Please select an option to proceed to the system.</p>
        </div>

        <div class="action-grid">
            <a href="login.php" class="action-card">
                <div class="icon-box"><i class="fa-solid fa-right-to-bracket"></i></div>
                <h3>Sign In</h3>
                <p>Access your account, dashboard and manage users.</p>
            </a>

            <a href="signup.php" class="action-card">
                <div class="icon-box"><i class="fa-solid fa-user-plus"></i></div>
                <h3>Create account</h3>
                <p>Register a new account.</p>
            </a>
        </div>
    </div>
    
</body>
</html>