<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;

$novel_id = $_GET['novel_id'];
$db = new MySQL();
$pdo = $db->connect();

//Get the last chapter number
$stmt = $pdo->prepare("SELECT MAX(chapter_number) as last_num FROM chapters WHERE novel_id = ?");
$stmt->execute([$novel_id]);
$res = $stmt->fetch();
$new_num = ($res->last_num ?? 0) + 1;

//Insert new chapter
$stmt = $pdo->prepare("INSERT INTO chapters (novel_id, chapter_number, title, content, order_index) VALUES (?, ?, 'Untitled Chapter', '', ?)");
$stmt->execute([$novel_id, $new_num, $new_num]);

$new_chapter_id = $pdo->lastInsertId();

header("Location: ../editor.php?novel_id=$novel_id&chapter_id=$new_chapter_id");