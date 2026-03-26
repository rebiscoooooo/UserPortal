<?php
require 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="assets/login.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <form action="includes/process_login.php" method="POST">
        <main class="main-container">
            <div class="login-card">
                <form action="includes/process_login.php" method="POST">
                    <h2 class="text-center mb-4">Log In</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">EMAIL</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">PASSWORD</label>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            Login
                        </button>

                        <div class="footer-links text-center mt-3">
                            <a href="index.php">Back to menu</a>
                            <span class="mx-2 text-muted">|</span>
                            <a href="signup.php">Create an Account</a>
                        </div>
                    </div>
                </form>
            </div>
        </main>
        
    </body>
</html>