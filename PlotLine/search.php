<?php
include_once("vendor/autoload.php");
use Libs\Database\MySQL;
use Libs\Database\NovelaTable;
use Libs\Database\UsersTable;
use Helpers\Auth;

$auth_user = Auth::check();
$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? (strpos($query, '@') === 0 ? 'users' : 'novels');

$db = new MySQL();
$novelaTable = new NovelaTable($db);
$userTable = new UsersTable($db);

$results = [];
if (!empty($query)) {
    if ($type === 'users') {
        $searchTerm = str_replace('@', '', $query);
        $results = $userTable->search($searchTerm, $auth_user->id);
    } else {
        $results = $novelaTable->searchNovels($query);
    }
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
    <title>Search: <?= htmlspecialchars($query) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        
        /*CARD STYLE */
        .novel-card { text-decoration: none !important; color: inherit; display: block; margin-bottom: 15px; }
        .card-img-wrapper { 
            position: relative; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); aspect-ratio: 2/3;
            margin-bottom: 10px; transition: transform 0.2s ease;
        }
        .card-img-wrapper:hover { transform: translateY(-5px); }
        .card-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .novel-title { font-weight: 700; font-size: 0.95rem; color: #1e293b; line-height: 1.2; }
        .novel-meta-line { font-size: 0.8rem; color: #64748b; margin-top: 2px; }

        /* USER RESULT STYLE */
        .user-result-card {
            background: #fff; border-radius: 12px; padding: 15px;
            display: flex; align-items: center; border: 1px solid #eee;
            text-decoration: none; color: inherit; transition: 0.2s;
        }
        .user-result-card:hover { border-color: #0076fc; background: #f0f7ff; }

        /* TABS UI */
        .search-tabs .nav-link { color: #64748b; border: none; font-weight: 600; padding: 10px 25px; border-radius: 50px; }
        .search-tabs .nav-link.active { background: #1e293b; color: white; }

        /* --- MODERN TAB UI --- */
    .search-tab-container {
        display: flex;
        gap: 10px;
        margin-bottom: 40px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 2px;
    }

    .tab-item {
        position: relative;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #64748b;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 8px 8px 0 0;
    }

    .tab-item:hover {
        color: #1e293b;
        background: rgba(30, 41, 59, 0.04);
    }

    /* The Active State */
    .tab-item.active {
        color: #0076fc;
    }

    /* The Animated Underline */
    .tab-item.active::after {
        content: "";
        position: absolute;
        bottom: -2px; /* aligns with the container border */
        left: 0;
        width: 100%;
        height: 3px;
        background: #0076fc;
        border-radius: 10px;
    }

    /* Result Counter Badge */
    .count-badge {
        font-size: 0.75rem;
        background: #f1f5f9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 20px;
        margin-left: 8px;
        transition: 0.3s;
    }

    .tab-item.active .count-badge {
        background: #0076fc;
        color: #ffffff;
    }

    /* Empty State Styling */
    .empty-state {
        padding: 80px 0;
        background: #ffffff;
        border-radius: 20px;
        border: 2px dashed #e2e8f0;
    }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <header class="mb-5">
                <span class="text-uppercase text-primary fw-bold small" style="letter-spacing: 1px;">Search Results</span>
                <h2 class="fw-black display-6 mt-1">Showing results for "<span class="text-primary"><?= htmlspecialchars($query) ?></span>"</h2>
            </header>

            <div class="search-tab-container">
                <a href="search.php?q=<?= urlencode($query) ?>&type=novels" 
                   class="tab-item <?= $type === 'novels' ? 'active' : '' ?>">
                    Novels 
                    <?php if($type === 'novels'): ?>
                        <span class="count-badge"><?= count($results) ?></span>
                    <?php endif; ?>
                </a>
                <a href="search.php?q=<?= urlencode($query) ?>&type=users" 
                   class="tab-item <?= $type === 'users' ? 'active' : '' ?>">
                    Users
                    <?php if($type === 'users'): ?>
                        <span class="count-badge"><?= count($results) ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="row g-4">
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $item): ?>
                        <?php if ($type === 'novels'): ?>
                            <div class="col-6 col-md-3 col-lg-2">
                                <a href="view-story.php?id=<?= $item['id'] ?>" class="novel-card">
                                    <div class="card-img-wrapper">
                                        <img src="<?= $item['cover_image'] ?: getLetterCover($item['title']) ?>" alt="Cover">
                                    </div>
                                    <div class="novel-title"><?= htmlspecialchars($item['title']) ?></div>
                                    <div class="novel-meta-line">By <?= htmlspecialchars($item['author_name']) ?></div>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <a href="profile.php?id=<?= $item->id ?>" class="user-result-card">
                                    <img src="<?= $item->profile_image ?: 'https://ui-avatars.com/api/?name='.urlencode($item->name) ?>" class="rounded-circle me-3" width="55" height="55">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($item->name) ?></h6>
                                        <p class="small text-muted mb-0">@<?= strtolower(str_replace(' ', '', $item->name)) ?></p>
                                    </div>
                                    <div class="ms-auto text-primary fw-bold small">
                                        View Profile <i class="bi bi-arrow-right"></i>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state text-center">
                            <div class="mb-4">
                                <i class="bi bi-search-heart text-light" style="font-size: 4rem;"></i>
                            </div>
                            <h4 class="fw-bold text-dark">We couldn't find any matches</h4>
                            <p class="text-muted">Try checking your spelling or using different keywords.</p>
                            <a href="browse.php" class="btn btn-primary rounded-pill px-4 mt-2">Explore Library</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>