<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$novel_id = $_GET['novel_id'] ?? null;

if ($novel_id) {
    $db = new MySQL();
    $pdo = $db->connect();
    
    // Only delete if the novel is still in 'draft' status 
    $stmt = $pdo->prepare("DELETE FROM novels WHERE id = ? AND users_id = ? AND status = 'draft'");
    $stmt->execute([$novel_id, $auth_user->id]);
}

header("Location: ../my-stories.php");
exit();