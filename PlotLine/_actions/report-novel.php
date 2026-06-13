<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

if ($_POST) {
    $novel_id = $_POST['novel_id'];
    $reason = $_POST['reason'] . ": " . $_POST['details'];
    $user_id = $auth_user->id;

    $stmt = $pdo->prepare("INSERT INTO reports (reporter_id, novel_id, reason) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $novel_id, $reason]);

    header("Location: ../view-story.php?id=$novel_id&reported=1");
    exit();
}