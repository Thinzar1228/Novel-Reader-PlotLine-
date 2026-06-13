<?php
include_once("vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$currentUser = Auth::check(); 

$currentPage = basename($_SERVER['PHP_SELF']); 

$db = new MySQL();
$pdo = $db->connect();
$stmt = $pdo->query("SELECT name FROM genres ORDER BY name ASC");
$all_genres = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstap/bootstrap.min.css">
    <link rel="stylesheet" href="css/navbar.css?v=1.1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Novela</title>
    <style>
        #searchResults {
            top: 100%; left: 0; right: 0;
            background: white; z-index: 1000;
            max-height: 450px; overflow-y: auto;
        }
        .genre-link { transition: 0.2s; border-radius: 4px; }
        .genre-link:hover {
            background-color: #f8f9fa;
            color: #4389ec !important;
            padding-left: 10px;
        }
        .avatar-circle {
            width: 35px; height: 35px;
            background: #4389ec; color: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-weight: bold;
        }

        /* ACTIVE LINK STYLING */
        .nav-link.active-page {
            color: #494747 !important;
            font-weight: 800 !important;
        }
        .navbar {
            position: relative;
            z-index: 1050;
        }
        .dropdown-menu {
            z-index: 1060;
        }
        /* Custom Admin Style for Dropdown */
        .admin-link {
            background-color: #fff5f5;
            color: #dc3545 !important;
            font-weight: bold;
        }
        .admin-link:hover {
            background-color: #dc3545 !important;
            color: white !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
    <div class="container-fluid px-4">

        <a class="navbar-brand d-flex align-items-center" href="home.php">
            <div class="logo-icon me-2">
                <i class="bi bi-feather text-primary"></i>
            </div>
            <span class="brand-text fw-bold">Novela</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'home.php') ? 'active-page' : 'text-secondary' ?>" href="home.php">Home</a>
                </li>

                <li class="nav-item dropdown position-static">
                    <a class="nav-link dropdown-toggle <?= ($currentPage == 'browse.php') ? 'active-page' : 'text-secondary' ?> ms-lg-3" href="#" id="browseDropdown" data-bs-toggle="dropdown">
                        Browse
                    </a>
                    <div class="dropdown-menu w-100 border-0 shadow-sm py-4">
                        <div class="container">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0"><i class="bi bi-grid-fill me-2"></i>Discover by Genre</h6>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-3 col-6">
                                    <a class="dropdown-item genre-link small fw-bold text-primary" href="browse.php?genre=All">
                                        <i class="bi bi-stars me-1"></i> All Genres
                                    </a>
                                </div>

                                <?php foreach($all_genres as $g): ?>
                                    <div class="col-md-3 col-6">
                                        <a class="dropdown-item genre-link small text-muted" href="browse.php?genre=<?= urlencode($g->name) ?>">
                                            <?= htmlspecialchars($g->name) ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'bookmarks.php') ? 'active-page' : 'text-secondary' ?> ms-lg-3" href="bookmarks.php">Bookmarks</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'about.php') ? 'active-page' : 'text-secondary' ?> ms-lg-3" href="about.php">About</a>
                </li>
            </ul>

            <div class="d-flex align-items-center right-nav-section">
                
                <div class="search-container d-flex align-items-center position-relative" id="searchWrapper">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search novel or @author..." autocomplete="off">
                    <button class="btn search-trigger-btn" id="searchBtn">
                        <i class="bi bi-search"></i>
                    </button>
                    <div id="searchResults" class="position-absolute d-none border shadow-sm rounded-3"></div>
                </div>
                
                <div class="vertical-divider mx-3 d-none d-lg-block"></div>

                <div class="dropdown ms-2">
                    <button class="btn btn-primary rounded-pill px-3 me-3 dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-pencil-square me-1"></i> Write
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="my-stories.php"><i class="bi bi-journal-text me-2"></i> My Stories</a></li>
                        <li><a class="dropdown-item fw-bold text-primary" href="create-story.php"><i class="bi bi-plus-circle me-2"></i> Create New Story</a></li>
                    </ul>
                </div>
                
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <div class="avatar me-2" id="userAvatar">
                            <?php if(!empty($currentUser->profile_image)): ?>
                                <img src="<?= $currentUser->profile_image ?>" class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($currentUser->name, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="user-name-text d-none d-sm-inline fw-medium">
                            <?= htmlspecialchars($currentUser->name) ?>
                        </span>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <?php if ((int)$currentUser->role_id === 3 || (int)$currentUser->role_id === 2): ?>
                            <li>
                                <a class="dropdown-item admin-link" href="admin/index.php">
                                    <i class="bi bi-speedometer2 me-2"></i> Admin Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>

                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="_actions/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchBtn = document.getElementById('searchBtn');

    searchInput.addEventListener('input', function() {
        let query = this.value.trim();
        if (query.length < 2) {
            searchResults.classList.add('d-none');
            return;
        }

        fetch(`_actions/search.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.results.length > 0) {
                    data.results.forEach(item => {
                        let html = '';
                        if (data.type === 'novel') {
                            html = `
                                <a href="view-story.php?id=${item.id}" class="d-flex align-items-center p-2 text-decoration-none text-dark search-item">
                                    <img src="${item.cover_image || 'assets/default-cover.png'}" width="30" height="40" class="me-2 rounded">
                                    <div class="small"><strong>${item.title}</strong><br><span class="text-muted">By ${item.author_name}</span></div>
                                </a>`;
                        } else {
                            html = `
                                <a href="profile.php?id=${item.id}" class="d-flex align-items-center p-2 text-decoration-none text-dark search-item">
                                    <img src="${item.profile_image || 'assets/default-avatar.png'}" width="35" height="35" class="me-2 rounded-circle">
                                    <div class="small"><strong>${item.name}</strong><br><span class="text-muted">User</span></div>
                                </a>`;
                        }
                        searchResults.innerHTML += html;
                    });
                    searchResults.classList.remove('d-none');
                } else {
                    searchResults.innerHTML = '<div class="p-3 small text-muted">No results found</div>';
                    searchResults.classList.remove('d-none');
                }
            });
    });

    const triggerSearch = () => {
        let query = searchInput.value.trim();
        if (query) {
            window.location.href = `search.php?q=${encodeURIComponent(query)}`;
        }
    };

    searchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') triggerSearch(); });
    searchBtn.addEventListener('click', triggerSearch);

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#searchWrapper')) searchResults.classList.add('d-none');
    });
});
</script>

<script src="js/bootstrap_js/bootstrap.bundle.min.js"></script>
<script src="js/navbar.js?v=1.1"></script>
</body>
</html>



