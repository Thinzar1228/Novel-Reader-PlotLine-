<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

// Security: Only role_id 3 (Admin) can enter
$admin = Auth::isAdmin(); 

$db = new MySQL();
$pdo = $db->connect();

// 1. Fetch Stats
$userCount = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();
$novelCount = $pdo->query("SELECT COUNT(id) FROM novels")->fetchColumn();
$reportCount = $pdo->query("SELECT COUNT(id) FROM reports WHERE status='pending'")->fetchColumn();

// 2. Fetch Users and their Roles
$users = $pdo->query("
    SELECT u.*, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    ORDER BY u.role_id DESC, u.id DESC
")->fetchAll(PDO::FETCH_OBJ);

// 3. Fetch Reports
$reports = $pdo->query("
    SELECT r.*, u.name as reporter_name, n.title as novel_title 
    FROM reports r 
    JOIN users u ON r.reporter_id = u.id 
    JOIN novels n ON r.novel_id = n.id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at DESC
")->fetchAll(PDO::FETCH_OBJ);

// 4. Fetch All Available Roles for the Switcher
$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | StoryHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --admin-bg: #1a2a40; }
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: var(--admin-bg); color: white; position: sticky; top: 0; }
        .nav-link { color: #adb5bd; transition: 0.3s; margin-bottom: 5px; border-radius: 8px; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .stat-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .role-select { width: auto; display: inline-block; font-size: 0.85rem; padding: 2px 10px; border-radius: 20px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar p-3 shadow">
            <div class="text-center py-4">
                <h4 class="fw-bold text-white mb-0">Novela</h4>
                <small class="text-muted text-uppercase">Admin Portal</small>
            </div>
            <hr class="text-secondary">
            <ul class="nav flex-column mt-3">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#users"><i class="bi bi-people me-2"></i> Manage Users</a></li>
                <li class="nav-item"><a class="nav-link" href="#reports"><i class="bi bi-flag me-2"></i> Reports <span class="badge bg-danger ms-auto"><?= $reportCount ?></span></a></li>
                <li class="nav-item mt-5"><a class="nav-link text-white bg-danger bg-opacity-25" href="../home.php"><i class="bi bi-house-door me-2"></i> Exit Dashboard</a></li>
            </ul>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="row g-3 mb-5">
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-4">
                        <div class="d-flex justify-content-between">
                            <div><p class="text-muted mb-1 fw-bold">TOTAL USERS</p><h3><?= $userCount ?></h3></div>
                            <div class="fs-1 text-primary"><i class="bi bi-people-fill"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-4">
                        <div class="d-flex justify-content-between">
                            <div><p class="text-muted mb-1 fw-bold">TOTAL NOVELS</p><h3><?= $novelCount ?></h3></div>
                            <div class="fs-1 text-success"><i class="bi bi-book-fill"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-4">
                        <div class="d-flex justify-content-between">
                            <div><p class="text-muted mb-1 fw-bold">PENDING REPORTS</p><h3><?= $reportCount ?></h3></div>
                            <div class="fs-1 text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <h4 id="users" class="fw-bold mb-3"><i class="bi bi-people me-2"></i>User Management</h4>
            <div class="card table-card mb-5">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle ">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Role Switcher</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="fw-bold"><?= htmlspecialchars($u->name) ?></div>
                                        <div class="small text-muted ms-2">(<?= htmlspecialchars($u->email) ?>)</div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u->id !== $admin->id): ?>
                                        <form action="../_actions/admin-actions.php" method="GET" class="d-inline">
                                            <input type="hidden" name="change_role_user_id" value="<?= $u->id ?>">
                                            <select name="new_role" class="form-select form-select-sm role-select shadow-sm " onchange="this.form.submit()">
                                                <?php foreach($roles as $r): ?>
                                                    <option value="<?= $r->id ?>" <?= $u->role_id == $r->id ? 'selected' : '' ?>>
                                                        <?= $r->name ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-primary rounded-pill px-3">Master Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u->suspended): ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($u->id !== $admin->id && $u->role_id != 3): ?>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../_actions/admin-actions.php?suspend=<?= $u->id ?>" class="btn btn-outline-warning">
                                                <?= $u->suspended ? '<i class="bi bi-unlock"></i> Unsuspend' : '<i class="bi bi-lock"></i> Suspend' ?>
                                            </a>
                                            <button onclick="confirmDelete(<?= $u->id ?>)" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <h4 id="reports" class="fw-bold mb-3"><i class="bi bi-flag me-2"></i>Reported Content</h4>
            <div class="card table-card">
                <div class="table-responsive p-3">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reporter</th>
                                <th>Novel Title</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reports as $rep): ?>
                            <tr>
                                <td><?= htmlspecialchars($rep->reporter_name) ?></td>
                                <td><a href="../view-story.php?id=<?= $rep->novel_id ?>" target="_blank" class="fw-bold text-decoration-none"><?= htmlspecialchars($rep->novel_title) ?></a></td>
                                <td><span class="text-muted small"><?= htmlspecialchars($rep->reason) ?></span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button onclick="confirmDeleteNovel(<?= $rep->novel_id ?>)" class="btn btn-danger">Delete Novel</button>
                                        <a href="../_actions/admin-actions.php?resolve_report=<?= $rep->id ?>" class="btn btn-secondary">Dismiss</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($reports)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Great job! No pending reports.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete User?',
        text: "This action is permanent and deletes all their stories!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `../_actions/admin-actions.php?delete_user=${id}`;
    })
}

function confirmDeleteNovel(id) {
    Swal.fire({
        title: 'Delete this novel?',
        text: "It will be removed from the library forever.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Delete Now'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `../_actions/admin-actions.php?delete_novel=${id}`;
    })
}
</script>
</body>
</html>