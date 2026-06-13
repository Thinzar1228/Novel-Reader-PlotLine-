<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

header('Content-Type: application/json');

try {
    $auth_user = Auth::check();
    $db = new MySQL();
    $pdo = $db->connect();

    $novel_id = $_POST['novel_id'] ?? null;
    $user_id = $auth_user->id;

    if (!$novel_id) {
        echo json_encode(['status' => 'error', 'message' => 'No Novel ID']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE users_id = ? AND novel_id = ?");
    $stmt->execute([$user_id, $novel_id]);
    $bookmark = $stmt->fetch();

    if ($bookmark) {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE users_id = ? AND novel_id = ?");
        $stmt->execute([$user_id, $novel_id]);
        echo json_encode(['status' => 'removed']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO bookmarks (users_id, novel_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $novel_id]);
        echo json_encode(['status' => 'added']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}