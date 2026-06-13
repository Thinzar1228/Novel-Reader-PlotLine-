<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

$auth = Auth::check();
$novel_id = $_POST['novel_id'] ?? null;
$rating = $_POST['rating'] ?? null;

if ($novel_id && $rating) {
    $db = new MySQL();
    $pdo = $db->connect();

    $stmt = $pdo->prepare("SELECT users_id FROM novels WHERE id = ?");
    $stmt->execute([$novel_id]);
    $novel_author_id = $stmt->fetchColumn();


    if ((int)$auth->id === (int)$novel_author_id) {
        header("Location: ../view-story.php?id=$novel_id&error=self_rating");
        exit();
    }

    // Proceed with rating logic if not the author
    $sql = "INSERT INTO ratings (users_id, novel_id, rating, created_at) 
            VALUES (:uid, :nid, :r, NOW())
            ON DUPLICATE KEY UPDATE rating = :r, updated_at = NOW()";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid' => $auth->id,
        ':nid' => $novel_id,
        ':r' => $rating
    ]);

    header("Location: ../view-story.php?id=$novel_id&success=rated");
    exit();
}