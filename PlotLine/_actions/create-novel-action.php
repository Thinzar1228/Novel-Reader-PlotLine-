<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

$novel_id = $_POST['novel_id'] ?? null;
$title = !empty($_POST['title']) ? $_POST['title'] : 'Untitled Story';
$description = $_POST['description'] ?? '';
$users_id = $auth_user->id;

$selected_genres = $_POST['genres'] ?? [];

$coverPath = null;
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir("../covers")) mkdir("../covers", 0777, true);
    
    $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $coverPath = "covers/" . bin2hex(random_bytes(8)) . "." . $extension;
    move_uploaded_file($_FILES['cover_image']['tmp_name'], "../" . $coverPath);
}

try {
    $pdo->beginTransaction();

    if ($novel_id) {
        $sql = "UPDATE novels SET title = :title, description = :description";
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':id' => $novel_id,
            ':uid' => $users_id
        ];

        if ($coverPath) {
            $sql .= ", cover_image = :cover";
            $params[':cover'] = $coverPath;
        }

        $sql .= " WHERE id = :id AND users_id = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare("DELETE FROM novel_genres WHERE novel_id = ?");
        $stmt->execute([$novel_id]);

        $stmt = $pdo->prepare("SELECT id FROM chapters WHERE novel_id = ? ORDER BY order_index ASC LIMIT 1");
        $stmt->execute([$novel_id]);
        $chapter = $stmt->fetch();
        $chapter_id = $chapter->id ?? null;

    } else {
        $sqlNovel = "INSERT INTO novels (users_id, title, description, cover_image, status) 
                     VALUES (:users_id, :title, :description, :cover_image, 'draft')";
        
        $stmt = $pdo->prepare($sqlNovel);
        $stmt->execute([
            ':users_id' => $users_id,
            ':title' => $title,
            ':description' => $description,
            ':cover_image' => $coverPath
        ]);
        
        $novel_id = $pdo->lastInsertId();

        $sqlChapter = "INSERT INTO chapters (novel_id, chapter_number, title, content, order_index, status) 
                       VALUES (:novel_id, 1, 'Untitled Chapter', '', 0, 'draft')";
        $stmt = $pdo->prepare($sqlChapter);
        $stmt->execute([':novel_id' => $novel_id]);
        $chapter_id = $pdo->lastInsertId();
    }

    if (!empty($selected_genres)) {
        $sqlGenre = "INSERT INTO novel_genres (novel_id, genre_id) VALUES (?, ?)";
        $stmtGenre = $pdo->prepare($sqlGenre);
        foreach ($selected_genres as $genre_id) {
            $stmtGenre->execute([$novel_id, $genre_id]);
        }
    }

    $pdo->commit();

    header("Location: ../editor.php?novel_id=$novel_id&chapter_id=$chapter_id");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}