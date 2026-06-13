<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;

// Set header so JavaScript knows this is JSON
header('Content-Type: application/json');

// Check if data was actually posted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$db = new MySQL();
$pdo = $db->connect();

$id = $_POST['chapter_id'] ?? null;
$title = $_POST['title'] ?? 'Untitled Chapter';
$content = $_POST['content'] ?? '';
$status = $_POST['status'] ?? 'draft';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing Chapter ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE chapters SET title = ?, content = ?, status = ? WHERE id = ?");
    $result = $stmt->execute([$title, $content, $status, $id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}