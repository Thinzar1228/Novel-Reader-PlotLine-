<?php
include("vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\NovelaTable;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

//Looking for 'ongoing' or 'completed' status
$stmt = $pdo->prepare("
    SELECT n.*, u.name as author_name,
    (SELECT AVG(rating) FROM ratings WHERE novel_id = n.id) as avg_rating,
    (SELECT COUNT(id) FROM chapters WHERE novel_id = n.id AND status = 'published') as chapter_count
    FROM novels n
    JOIN users u ON n.users_id = u.id
    WHERE n.status IN ('ongoing', 'completed')
    ORDER BY n.created_at DESC
");
$stmt->execute();
$all_novels = $stmt->fetchAll();

function getLetterCover($title) {
    $first = !empty($title) ? mb_strtoupper(mb_substr($title, 0, 1)) : '?';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300"><rect width="200" height="300" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#cbd5e1" font-family="serif" font-weight="900" font-size="100">'.$first.'</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Novela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #0076fc; --text-main: #1e293b; --text-muted: #64748b; }
        body { background-color: #ffffff; font-family: 'Inter', sans-serif; color: var(--text-main); }

        .novel-card { text-decoration: none !important; color: inherit; display: block; }
        .card-img-wrapper { 
            position: relative; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); aspect-ratio: 2/3;
            margin-bottom: 12px; transition: transform 0.2s ease;
        }
        .card-img-wrapper:hover { transform: translateY(-5px); }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

        .badge-status {
            position: absolute; top: 8px; left: 8px;
            background: var(--primary); color: white;
            padding: 3px 8px; border-radius: 5px;
            font-weight: 800; font-size: 0.6rem; text-transform: uppercase; z-index: 2;
        }
        
        .badge-rating-top {
            position: absolute; top: 8px; right: 8px;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);
            color: white; padding: 3px 6px; border-radius: 6px;
            font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 3px; z-index: 2;
        }

        .novel-title { 
            font-weight: 700; font-size: 0.95rem; color: var(--text-main); line-height: 1.3;
            margin-bottom: 2px; display: -webkit-box;
            -webkit-box-orient: vertical; overflow: hidden; height: 2.6em;
        }
        .novel-meta-line { font-size: 0.8rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="fw-bold mb-4">All Published Stories</h2>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4">
            <?php if ($all_novels): foreach ($all_novels as $book): ?>
                <div class="col">
                    <a href="view-story.php?id=<?= $book->id ?>" class="novel-card">
                        <div class="card-img-wrapper">
                            <img src="<?= $book->cover_image ?: getLetterCover($book->title) ?>" alt="Cover">
                            <div class="badge-status"><?= htmlspecialchars($book->status) ?></div>
                            <div class="badge-rating-top">
                                <i class="bi bi-star-fill text-warning"></i>
                                <?= number_format($book->avg_rating ?? 0.0, 1) ?>
                            </div>
                        </div>
                        <div class="novel-title"><?= htmlspecialchars($book->title) ?></div>
                        <div class="novel-meta-line text-primary fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">
                            <?= htmlspecialchars($book->genre) ?>
                        </div>
                        <div class="novel-meta-line">By <?= htmlspecialchars($book->author_name) ?></div>
                        <div class="novel-meta-line small opacity-75"><?= $book->chapter_count ?> Chapters</div>
                    </a>
                </div>
            <?php endforeach; else: ?>
                <p class="text-muted">No published novels found. (Make sure status is 'ongoing' or 'completed')</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>