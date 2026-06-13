<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

Auth::isAdmin();
$db = new MySQL();
$pdo = $db->connect();

// 1. Suspend User
if (isset($_GET['suspend'])) {
    $id = $_GET['suspend'];
    $pdo->prepare("UPDATE users SET suspended = NOT suspended WHERE id = ?")->execute([$id]);
    header("Location: ../admin/index.php?msg=updated");

if (isset($_GET['suspend'])) {
    $id = $_GET['suspend'];
    $auth_user = Auth::check();

    //Prevent suspending self
    if ($id == $auth_user->id) {
        header("Location: ../admin/index.php?error=self_action");
        exit();
    }

    $pdo->prepare("UPDATE users SET suspended = NOT suspended WHERE id = ?")->execute([$id]);
    header("Location: ../admin/index.php?msg=updated");
}
}

// 2. Delete Novel (From Reports)
if (isset($_GET['delete_novel'])) {
    $id = $_GET['delete_novel'];
    // Delete the novel (database foreign keys should handle chapters/reports)
    $pdo->prepare("DELETE FROM novels WHERE id = ?")->execute([$id]);
    header("Location: ../admin/index.php?msg=novel_removed");
}

// 3. Dismiss/Resolve Report
if (isset($_GET['resolve_report'])) {
    $id = $_GET['resolve_report'];
    $pdo->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?")->execute([$id]);
    header("Location: ../admin/index.php?msg=resolved");
}

if (isset($_GET['change_role_user_id']) && isset($_GET['new_role'])) {
    $userId = $_GET['change_role_user_id'];
    $newRole = $_GET['new_role'];
    $auth_user = Auth::check();

    //cannot change own admin role
    if ($userId == $auth_user->id) {
        header("Location: ../admin/index.php?error=self_role_change");
        exit();
    }

    $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);
    header("Location: ../admin/index.php?msg=role_updated");
}