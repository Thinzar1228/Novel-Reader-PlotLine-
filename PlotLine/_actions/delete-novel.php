<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$novel_id = $_GET['id'] ?? null;

if (!$novel_id) {
    header("Location: ../my-stories.php");
    exit();
}

$db = new MySQL();
$pdo = $db->connect();

try {
    $checkStmt = $pdo->prepare("SELECT id FROM novels WHERE id = ? AND users_id = ?");
    $checkStmt->execute([$novel_id, $auth_user->id]);
    $novel = $checkStmt->fetch();

    if ($novel) {
        $stmt = $pdo->prepare("DELETE FROM novels WHERE id = ?");
        $stmt->execute([$novel_id]);

        header("Location: ../my-stories.php?deleted=success");
    } else {
        header("Location: ../my-stories.php?error=unauthorized");
    }
} catch (Exception $e) {
    header("Location: ../my-stories.php?error=server_error");
}