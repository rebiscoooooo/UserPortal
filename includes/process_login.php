<?php
require 'db.php';
require 'User.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $userObj = new User($db);
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: ../login.php");
        exit();
    }

    $loginResult = $userObj->login($email, $password);

    if ($loginResult['success'] === true) {
        if ($loginResult['role'] === 'admin') {
            header("Location: ../admin_dashboard.php");
        } else {
            header("Location: ../user_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['login_error'] = $loginResult['error'];
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>