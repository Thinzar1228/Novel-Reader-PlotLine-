<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

Auth::check();
$db = new MySQL();
$pdo = $db->connect();

$id = $_GET['id'];
$novel_id = $_GET['novel_id'];

$pdo->prepare("DELETE FROM chapters WHERE id = ?")->execute([$id]);

$stmt = $pdo->prepare("SELECT id FROM chapters WHERE novel_id = ? LIMIT 1");
$stmt->execute([$novel_id]);
$chapter = $stmt->fetch();

if ($chapter) {
    header("Location: ../editor.php?novel_id=$novel_id&chapter_id=$chapter->id");
} else {
    header("Location: ../profile.php");
}