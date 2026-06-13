<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

$auth = Auth::check();
$novel_id = $_GET['novel_id'] ?? null;

if ($novel_id) {
    $db = new MySQL();
    $pdo = $db->connect();

    $stmt = $pdo->prepare("DELETE FROM ratings WHERE users_id = ? AND novel_id = ?");
    $stmt->execute([$auth->id, $novel_id]);

    header("Location: ../view-story.php?id=$novel_id&success=removed");
    exit();
}

header("Location: ../index.php");