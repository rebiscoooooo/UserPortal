<?php
require 'includes/db.php';
require 'includes/User.php';

$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    if ($delete_id <= 0) {
        $_SESSION['error_msg'] = "Invalid user ID.";
    } elseif ($delete_id == $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "You cannot delete your own account.";
    } else {
        if ($userObj->deleteUser($delete_id)) {
            $_SESSION['success_msg'] = "User deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to delete user.";
        }
    }
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['toggle_status_id'])) {
    $toggle_id = intval($_GET['toggle_status_id']);
    $status = isset($_GET['status']) ? sanitizeStatus($_GET['status']) : null;
    
    if ($toggle_id <= 0 || !$status) {
        $_SESSION['error_msg'] = "Invalid user ID or status.";
    } elseif ($toggle_id == $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "You cannot change your own account status.";
    } else {
        if ($userObj->toggleStatus($toggle_id, $status)) {
            $_SESSION['success_msg'] = "User status updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to update user status.";
        }
    }
    header("Location: admin_dashboard.php");
    exit();
}

function sanitizeStatus($status) {
    return in_array($status, ['active', 'inactive']) ? $status : null;
}

$counts = $userObj->getCounts();
$total_users = (int)$counts['total'];
$active_users = (int)$counts['active'];
$inactive_users = (int)$counts['inactive'];
$admin_users = (int)$counts['admin'];

$search = "";
$search_param = ""; 
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $search_param = "&search=" . urlencode($search);
}

$total_records = $userObj->getUsersCount($search);

// Setup actual Pagination parameters
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$total_pages = ceil($total_records / $records_per_page);

if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

$offset = ($page - 1) * $records_per_page;

$users = $userObj->getUsers($search, $records_per_page, $offset);
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
    <title>Admin Dashboard</title>
    
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
                <span class="badge rounded-pill badge-role-admin px-3 py-2">
                    ADMINISTRATOR
                </span>
                
                <span class="fw-bold text-dark">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                
                <button id="themeToggleBtn" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Toggle Dark/Light Mode">
                    <i id="themeIcon" class="fa-solid fa-moon"></i>
                </button>

                <a href="logout.php" class="btn btn-logout text-decoration-none">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </header>

    <ul class="nav nav-pills custom-navbar">
        <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="add_user.php"><i class="fa-solid fa-user-plus me-1"></i> Add User</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fa-solid fa-user-gear me-1"></i> My Profile</a></li>
    </ul>

    <div class="container pb-5">
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class='alert alert-success alert-dismissible fade show shadow-sm border-0' role='alert'>
                <i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($_SESSION['success_msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class='alert alert-danger alert-dismissible fade show shadow-sm border-0' role='alert'>
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($_SESSION['error_msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-semibold">Total Users</p>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$total_users); ?></h3>
                            <small class="text-muted">Registered accounts</small>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-semibold">Active Users</p>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$active_users); ?></h3>
                            <small class="text-success"><i class="fa-solid fa-circle fa-2xs"></i> Currently active</small>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success"><i class="fa-solid fa-user-check"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-semibold">Inactive Users</p>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$inactive_users); ?></h3>
                            <small class="text-danger"><i class="fa-solid fa-circle fa-2xs"></i> Locked accounts</small>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-user-lock"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 fw-semibold">Administrators</p>
                            <h3 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$admin_users); ?></h3>
                            <small class="text-muted">System management</small>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-shield-halved"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stat-card">
            <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-users-gear me-2"></i> All Users Data <span class="badge bg-secondary ms-2"><?php echo $total_records; ?> Total</span></h5>
                
                <form method="GET" class="d-flex" style="max-width: 400px; flex: 1;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search name, email, or address..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary px-3">Search</button>
                        <?php if(isset($_GET['search'])): ?>
                            <a href="admin_dashboard.php" class="btn btn-secondary px-3">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle border-top">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4 fw-semibold">ID</th>
                                <th class="fw-semibold">FULL NAME</th>
                                <th class="fw-semibold">EMAIL</th>
                                <th class="fw-semibold">GENDER</th>
                                <th class="fw-semibold">ADDRESS</th>
                                <th class="fw-semibold">ROLE</th>
                                <th class="fw-semibold">STATUS</th>
                                <th class="fw-semibold text-center">LOGIN<br>ATTEMPTS</th>
                                <th class="fw-semibold">CREATED</th>
                                <th class="text-end pe-4 fw-semibold">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users) && is_array($users) && count($users) > 0): ?>
                                <?php foreach ($users as $row): ?>
                                    <?php 
                                    // Validate row data
                                    $user_id = isset($row['id']) ? (int)$row['id'] : 0;
                                    $first_name = isset($row['first_name']) ? htmlspecialchars($row['first_name']) : '';
                                    $last_name = isset($row['last_name']) ? htmlspecialchars($row['last_name']) : '';
                                    $email = isset($row['email']) ? htmlspecialchars($row['email']) : '';
                                    $gender = isset($row['gender']) ? htmlspecialchars($row['gender']) : '-';
                                    $address = isset($row['address']) ? htmlspecialchars($row['address']) : '-';
                                    $role = isset($row['role']) ? htmlspecialchars($row['role']) : 'user';
                                    $status = isset($row['status']) ? htmlspecialchars($row['status']) : 'active';
                                    $login_attempts = isset($row['login_attempts']) ? (int)$row['login_attempts'] : 0;
                                    $created_at = isset($row['created_at']) ? $row['created_at'] : 'N/A';
                                    
                                    if ($user_id <= 0) continue; // Skip invalid rows
                                    
                                    // Format created_at date if it exists
                                    if ($created_at !== 'N/A') {
                                        try {
                                            $date = new DateTime($created_at);
                                            $created_at = $date->format('M d, Y');
                                        } catch (Exception $e) {
                                            $created_at = 'N/A';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted small fw-bold">26-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></td>
                                        <td class="fw-semibold text-dark"><?php echo $first_name . ' ' . $last_name; ?></td>
                                        <td class="text-muted" title="<?php echo $email; ?>"><?php echo strlen($email) > 25 ? substr($email, 0, 22) . '...' : $email; ?></td>
                                        <td><span class="badge bg-info-soft text-info rounded-pill px-2 py-1"><?php echo ucfirst($gender); ?></span></td>
                                        <td title="<?php echo $address; ?>"><?php echo (strlen($address) > 20 && $address !== '-') ? substr($address, 0, 17) . '...' : $address; ?></td>
                                        <td>
                                            <span class="badge <?php echo $role === 'admin' ? 'badge-role-admin' : 'badge-role-user'; ?> rounded-pill px-2 py-1">
                                                <?php echo htmlspecialchars(ucfirst($role)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($status === 'active'): ?>
                                                <span class="badge bg-success-soft rounded-pill px-2 py-1 border border-success border-opacity-25"><i class="fa-solid fa-circle fa-2xs me-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-soft rounded-pill px-2 py-1 border border-danger border-opacity-25"><i class="fa-regular fa-circle fa-2xs me-1"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo ($login_attempts >= 3) ? 'bg-danger' : (($login_attempts >= 2) ? 'bg-warning' : 'bg-success'); ?> rounded-pill px-2 py-1">
                                                <?php echo $login_attempts; ?>/3
                                            </span>
                                        </td>
                                        <td class="text-muted small"><?php echo htmlspecialchars($created_at); ?></td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group gap-1">
                                                <?php if ($user_id != $_SESSION['user_id']): ?>
                                                    <a href="switch_account.php?impersonate=<?php echo $user_id; ?>" class="btn btn-outline-info btn-sm" title="Switch to this user's account">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-outline-primary btn-sm" title="Edit User">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <a href="admin_dashboard.php?toggle_status_id=<?php echo $user_id; ?>&status=<?php echo htmlspecialchars($status); ?>" class="btn btn-outline-<?php echo ($status === 'active') ? 'warning' : 'success'; ?> btn-sm" title="<?php echo ($status === 'active') ? 'Deactivate Account' : 'Activate Account'; ?>">
                                                    <i class="fa-solid fa-power-off"></i>
                                                </a>
                                                <a href="admin_dashboard.php?delete_id=<?php echo $user_id; ?>" class="btn btn-outline-danger btn-sm" title="Delete User" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>? This action cannot be undone.');">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="text-center py-5 text-muted"><i class="fa-regular fa-folder-open fs-2 mb-3 d-block"></i> No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center bg-light">
                    <div class="text-muted small mb-2 mb-md-0">
                        <i class="fa-solid fa-check-circle me-1"></i> Showing <strong><?php echo count($users); ?></strong> of <strong><?php echo $total_records; ?></strong> records
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search_param; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search_param; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search_param; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>

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
            
            // Database diagnostics (press Ctrl+Shift+D to toggle)
            let diagsVisible = false;
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    e.preventDefault();
                    const diagsSection = document.getElementById('dbDiagnostics');
                    if (diagsSection) {
                        diagsVisible = !diagsVisible;
                        diagsSection.style.display = diagsVisible ? 'block' : 'none';
                    }
                }
            });
        });
    </script>
    
    <!-- Hidden Database Diagnostics Section (Ctrl+Shift+D to toggle) -->
    <div id="dbDiagnostics" style="display: none; position: fixed; bottom: 20px; right: 20px; background: #f8f9fa; border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; width: 450px; max-height: 500px; overflow-y: auto; z-index: 9999; font-family: monospace; font-size: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">
            DATABASE DIAGNOSTICS
        </div>
        <div>
            <strong>Users Retrieved:</strong> <?php echo count($users); ?><br>
            <strong>Total Records in DB:</strong> <?php echo $total_records; ?><br>
            <strong>Current Page:</strong> <?php echo $page; ?> / <?php echo $total_pages; ?><br>
            <strong>Records Per Page:</strong> <?php echo $records_per_page; ?><br>
            <strong>Search Query:</strong> <?php echo !empty($search) ? htmlspecialchars($search) : 'None (showing all)'; ?><br>
            <strong>Data Source:</strong> PDO Connection (MySQL)<br>
            <strong>Database Status:</strong> <span style="color: green;">✓ Connected</span><br>
            <strong>Columns Displayed:</strong> 10 columns<br>
            <strong>Pagination:</strong> <?php echo ($total_pages > 1) ? 'Enabled' : 'Disabled (all records fit on one page)'; ?>
        </div>
    </div>
</body>
</html>