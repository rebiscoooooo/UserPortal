<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (isset($_GET['impersonate'])) {
    $target_id = $_GET['impersonate'];

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

    if ($target_id == $_SESSION['user_id']) {
        header("Location: admin_dashboard.php");
        exit();
    }

    $target_user = $userObj->getUserById($target_id);
    if ($target_user) {
        $_SESSION['original_admin_id'] = $_SESSION['user_id'];
        $_SESSION['original_admin_name'] = $_SESSION['user_name'];
        $_SESSION['original_admin_role'] = $_SESSION['role'];

        $_SESSION['user_id'] = $target_user['id'];
        $_SESSION['user_name'] = $target_user['first_name'];
        $_SESSION['role'] = $target_user['role'];

        header("Location: user_dashboard.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "User not found.";
        header("Location: admin_dashboard.php");
        exit();
    }
}

if (isset($_GET['revert'])) {
    if (isset($_SESSION['original_admin_id'])) {
        $_SESSION['user_id'] = $_SESSION['original_admin_id'];
        $_SESSION['user_name'] = $_SESSION['original_admin_name'];
        $_SESSION['role'] = $_SESSION['original_admin_role'];

        unset($_SESSION['original_admin_id']);
        unset($_SESSION['original_admin_name']);
        unset($_SESSION['original_admin_role']);

        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

header("Location: index.php");
exit();
?>