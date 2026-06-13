<?php
include("vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

// Fetch bookmarked novels with necessary metadata
$stmt = $pdo->prepare("
    SELECT n.*, u.name AS author_name,
    (SELECT AVG(rating) FROM ratings WHERE novel_id = n.id) as avg_rating,
    (SELECT COUNT(id) FROM chapters WHERE novel_id = n.id AND status = 'published') as chapter_count
    FROM novels n
    JOIN bookmarks b ON n.id = b.novel_id
    JOIN users u ON n.users_id = u.id
    WHERE b.users_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$auth_user->id]);
$bookmarks = $stmt->fetchAll();

// Helper for covers
function getCover($path, $title) {
    if ($path) return $path;
    $first = mb_strtoupper(mb_substr($title, 0, 1));
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450" viewBox="0 0 300 450"><rect width="300" height="450" fill="#f8fafc"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#cbd5e1" font-family="serif" font-weight="900" font-size="120">'.$first.'</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Library | Novela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8fafc; }
        
        /* Card Container */
        .novel-card { 
            text-decoration: none !important; 
            color: inherit; 
            display: block;
            margin-bottom: 15px;
        }

        /* Image Wrapper with Badges */
        .card-img-wrapper { 
            position: relative;
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            aspect-ratio: 2/3;
            margin-bottom: 10px;
            transition: transform 0.2s ease;
        }
        .card-img-wrapper:hover { transform: translateY(-5px); }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

        /* Badges */
        .badge-status {
            position: absolute; top: 8px; left: 8px;
            background: #0076fc; color: white;
            padding: 3px 8px; border-radius: 5px;
            font-weight: 800; font-size: 0.65rem; text-transform: uppercase;
            z-index: 2;
        }
        .badge-rating-top {
            position: absolute; top: 8px; right: 8px;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);
            color: white; padding: 3px 6px; border-radius: 6px;
            font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 3px;
            z-index: 2;
        }
        .badge-rating-top i { color: #ffc107; font-size: 0.7rem; }

        /* Title + Dropdown Alignment */
        .title-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 5px;
        }
        .novel-title { 
            font-weight: 700; 
            font-size: 0.95rem; 
            color: #1e293b; 
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            /* -webkit-line-clamp: 2; */
            -webkit-box-orient: vertical;
            flex: 1;
        }

        /* Dropdown Button Styling */
        .options-dropdown .btn-link {
            color: #94a3b8;
            padding: 0;
            margin-top: -2px;
            font-size: 1.1rem;
            text-decoration: none;
        }
        .options-dropdown .btn-link:hover { color: #1e293b; }
        
        .novel-meta-line { 
            font-size: 0.8rem; 
            color: #64748b; 
            margin-top: 2px;
        }

        .empty-state { padding: 100px 20px; text-align: center; }
        .empty-icon { font-size: 4rem; color: #cbd5e1; }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="fw-bold mb-4">My Library</h2>

        <?php if (empty($bookmarks)): ?>
            <div class="empty-state">
                <i class="bi bi-bookmark-x empty-icon mb-3 d-block"></i>
                <h4 class="fw-bold text-dark">Your library is empty</h4>
                <p class="text-secondary">You haven't bookmarked any stories yet. Start exploring!</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-2">Discover Stories</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-4" id="bookmark-grid">
                <?php foreach ($bookmarks as $novel): ?>
                    <div class="col novel-card-container" id="novel-card-<?= $novel->id ?>">
                        <div class="novel-card">
                            <a href="view-story.php?id=<?= $novel->id ?>">
                                <div class="card-img-wrapper">
                                    <img src="<?= getCover($novel->cover_image, $novel->title) ?>" alt="Cover">
                                    <div class="badge-status"><?= htmlspecialchars($novel->status) ?></div>
                                    <div class="badge-rating-top">
                                        <i class="bi bi-star-fill"></i>
                                        <?= number_format($novel->avg_rating, 1) ?: '0.0' ?>
                                    </div>
                                </div>
                            </a>

                            <div class="title-wrapper">
                                <a href="view-story.php?id=<?= $novel->id ?>" class="novel-title text-decoration-none">
                                    <?= htmlspecialchars($novel->title) ?>
                                </a>
                                
                                <div class="dropdown options-dropdown">
                                    <button class="btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item text-danger d-flex align-items-center" href="javascript:void(0)" onclick="removeBookmark(<?= $novel->id ?>)">
                                                <i class="bi bi-trash3 me-2"></i> Unbookmark
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="novel-meta-line">
                                By <?= htmlspecialchars($novel->author_name) ?> | <?= $novel->chapter_count ?> Chapters
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<script>
function removeBookmark(novelId) {
    const container = document.getElementById(`novel-card-${novelId}`);
    const formData = new FormData();
    formData.append('novel_id', novelId);

    fetch('_actions/toggle-bookmark.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'removed' || data.status === 'added') {
            // Animate card removal
            container.style.transition = "0.4s cubic-bezier(0.4, 0, 0.2, 1)";
            container.style.opacity = "0";
            container.style.transform = "translateY(15px)";
            
            setTimeout(() => {
                container.remove();
                
                // If library becomes empty, reload to show empty state
                const remaining = document.querySelectorAll('.novel-card-container');
                if (remaining.length === 0) {
                    location.reload();
                }
            }, 400);
        }
    })
    .catch(err => console.error("Error unbookmarking:", err));
}
</script>
</body>
</html>