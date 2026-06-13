<?php
include_once("vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth = Auth::check(); 
// Default to 'All' if no genre is picked
$genre_name = $_GET['genre'] ?? 'All';

$db = new MySQL();
$pdo = $db->connect();

try {
    $query = "SELECT n.*, u.name as author_name,
              (SELECT AVG(rating) FROM ratings WHERE novel_id = n.id) as avg_rating,
              (SELECT COUNT(id) FROM chapters WHERE novel_id = n.id AND status = 'published') as chapter_count 
              FROM novels n
              JOIN users u ON n.users_id = u.id";

    if ($genre_name !== 'All') {
        $query .= " JOIN novel_genres ng ON n.id = ng.novel_id
                    JOIN genres g ON ng.genre_id = g.id
                    WHERE g.name = :genre AND n.status != 'draft'";
        $stmt = $pdo->prepare($query . " ORDER BY n.created_at DESC");
        $stmt->execute(['genre' => $genre_name]);
    } else {
        $query .= " WHERE n.status != 'draft'";
        $stmt = $pdo->query($query . " ORDER BY n.created_at DESC");
    }
    
    $novels = $stmt->fetchAll();

    // Fetch all genres for the filter bar
    $genreStmt = $pdo->query("SELECT name FROM genres ORDER BY name ASC");
    $all_genres = $genreStmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

function getLetterCover($title) {
    $first = !empty($title) ? mb_strtoupper(mb_substr($title, 0, 1)) : '?';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300"><rect width="200" height="300" fill="#f8fafc"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#cbd5e1" font-family="serif" font-weight="900" font-size="100">'.$first.'</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse <?= htmlspecialchars($genre_name) ?> | PlotLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        
        /* NOVEL CARD STYLE */
        .novel-card { text-decoration: none !important; color: inherit; display: block; margin-bottom: 15px; }
        .card-img-wrapper { 
            position: relative; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); aspect-ratio: 2/3;
            margin-bottom: 10px; transition: transform 0.2s ease;
        }
        .card-img-wrapper:hover { transform: translateY(-5px); }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .badge-status { position: absolute; top: 8px; left: 8px; background: #0076fc; color: white; padding: 3px 8px; border-radius: 5px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; z-index: 2; }
        .badge-rating-top { position: absolute; top: 8px; right: 8px; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; gap: 3px; z-index: 2; }
        .novel-title { font-weight: 700; font-size: 0.95rem; color: #1e293b; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-box-orient: vertical; }
        .novel-meta-line { font-size: 0.8rem; color: #64748b; margin-top: 2px; }

        /* GENRE NAV BAR */
        .genre-scroll {
            display: flex; overflow-x: auto; white-space: nowrap; gap: 10px; padding: 15px 0;
            scrollbar-width: none; /* Firefox */
        }
        .genre-scroll::-webkit-scrollbar { display: none; /* Chrome/Safari */ }
        .genre-link {
            padding: 8px 20px; border-radius: 50px; background: #fff;
            border: 1px solid #e2e8f0; color: #64748b; text-decoration: none;
            font-weight: 600; font-size: 0.9rem; transition: 0.2s;
        }
        .genre-link:hover { border-color: #0076fc; color: #0076fc; }
        .genre-link.active { background: #1e293b; color: #fff; border-color: #1e293b; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <div class="bg-white border-bottom shadow-sm">
        <div class="container">
            <div class="genre-scroll">
                <a href="browse.php?genre=All" class="genre-link <?= $genre_name === 'All' ? 'active' : '' ?>">All Genres</a>
                <?php foreach($all_genres as $g): ?>
                    <a href="browse.php?genre=<?= urlencode($g->name) ?>" 
                       class="genre-link <?= $genre_name === $g->name ? 'active' : '' ?>">
                        <?= htmlspecialchars($g->name) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="container mt-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0"><?= $genre_name === 'All' ? 'All Stories' : htmlspecialchars($genre_name) . ' Novels' ?></h4>
            <span class="text-muted small"><?= count($novels) ?> results Found</span>
        </div>

        <div class="row g-4">
            <?php if($novels): ?>
                <?php foreach($novels as $book): ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
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
                            <div class="novel-meta-line">
                                By <?= htmlspecialchars($book->author_name) ?> <br>
                                <?= $book->chapter_count ?> Chapters
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-book text-muted opacity-25" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No stories found in this section yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>