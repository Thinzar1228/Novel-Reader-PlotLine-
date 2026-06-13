<?php
include("vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

$chapter_id = $_GET['id'] ?? null;
if (!$chapter_id) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT c.*, n.title AS novel_title, n.id AS novel_id
    FROM chapters c
    JOIN novels n ON c.novel_id = n.id
    WHERE c.id = ? AND c.status = 'published'
");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch();

if (!$chapter) die("Chapter not found or not published.");

$stmt = $pdo->prepare("SELECT id, title, chapter_number FROM chapters WHERE novel_id = ? AND status = 'published' ORDER BY chapter_number ASC");
$stmt->execute([$chapter->novel_id]);
$all_chapters = $stmt->fetchAll();

$prev_id = null; $next_id = null;
foreach ($all_chapters as $index => $c) {
    if ($c->id == $chapter_id) {
        $prev_id = $all_chapters[$index - 1]->id ?? null;
        $next_id = $all_chapters[$index + 1]->id ?? null;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($chapter->title) ?> - <?= htmlspecialchars($chapter->novel_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --reader-bg: #ffffff; 
            --reader-text: #1a1a1a; 
            --reader-font-size: 1.25rem; 
            --reader-family: 'Inter', sans-serif;
            --primary: #0076fc;
        }
        
        body { 
            background-color: var(--reader-bg); 
            color: var(--reader-text); 
            font-family: var(--reader-family); 
            transition: background 0.3s, color 0.3s;
            min-height: 100vh;
        }
        
        /* Alignment Classes */
        .text-align-left { text-align: left !important; }
        .text-align-center { text-align: center !important; }
        .text-align-justify { text-align: justify !important; text-justify: inter-word; }

        /* Control Button Styling */
        .align-btn {
            flex: 1;
            border: 1px solid #ddd;
            background: transparent;
            color: inherit;
            padding: 5px;
            transition: 0.2s;
        }
        .align-btn.active {
            background: var(--primary);
            color: white !important;
            border-color: var(--primary);
        }
        body.theme-dark .align-btn { border-color: #444; }

        /* THEME OVERRIDES */
        body.theme-sepia { --reader-bg: #f4ecd8; --reader-text: #5b4636; }
        
        /* Forces pure white text and light gray for secondary info in Dark Mode */
        body.theme-dark { --reader-bg: #121212; --reader-text: #ffffff; }
        body.theme-dark .text-muted { color: #b0b0b0 !important; }
        body.theme-dark #sidebar { background: #1e1e1e; border-color: #333; color: white; }
        body.theme-dark .sidebar-link { border-color: #2d2d2d; color: #b0b0b0; }
        body.theme-dark .sidebar-link.active { background: rgba(255,255,255,0.05); color: #fff; }
        body.theme-dark .btn-exit-reader { background: #2d2d2d; color: #fff; border-color: #444; }
    
        .bi { color: inherit; }

        /* Sidebar TOC */
        #sidebar { 
            position: fixed; left: -320px; top: 0; width: 320px; height: 100%; 
            background: #fff; z-index: 1050; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 10px 0 30px rgba(0,0,0,0.1); overflow-y: auto;
        }
        #sidebar.active { left: 0; }
        .sidebar-link { display: block; padding: 18px 25px; color: inherit; text-decoration: none; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .sidebar-link.active { background: rgba(0, 118, 252, 0.08); color: var(--primary); font-weight: 700; border-left: 4px solid var(--primary); }

        .reader-header { 
            position: sticky; top: 0; background: var(--reader-bg); 
            border-bottom: 1px solid rgba(0,0,0,0.1); z-index: 1000; padding: 10px 0;
            backdrop-filter: blur(10px);
        }

        .content-container { max-width: 750px; margin: 40px auto; padding: 0 20px; }
        .chapter-text { font-size: var(--reader-font-size); line-height: 1.9; white-space: pre-line; letter-spacing: 0.01em; }

        .reader-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 1040; display: none; }
        .reader-overlay.active { display: block; }

        .settings-card { width: 280px; padding: 20px; border-radius: 15px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .theme-dot { width: 35px; height: 35px; border-radius: 50%; border: 2px solid #ddd; cursor: pointer; transition: 0.2s; }
        
        .btn-nav-custom { background: var(--primary); color: white; padding: 12px 35px; border-radius: 50px; text-decoration: none; font-weight: 700; transition: 0.3s; }
        .btn-nav-custom:hover { background: #0061d5; color: white; transform: translateY(-2px); }
    </style>
</head>
<body id="reader-body">

    <div class="reader-overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div id="sidebar">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold">Chapters</h5>
            <button class="btn-close" onclick="toggleSidebar()"></button>
        </div>
        <div class="toc-list">
            <?php foreach($all_chapters as $c): ?>
                <a href="read.php?id=<?= $c->id ?>" class="sidebar-link <?= $c->id == $chapter_id ? 'active' : '' ?>">
                    <small class="d-block text-uppercase fw-bold opacity-50" style="font-size: 0.65rem;">Chapter <?= $c->chapter_number ?></small>
                    <?= htmlspecialchars($c->title ?: 'Untitled Episode') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <nav class="reader-header">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="btn border-0 p-1" onclick="toggleSidebar()"><i class="bi bi-list fs-3"></i></button>
            </div>

            <div class="text-center px-2">
                <a href="view-story.php?id=<?= $chapter->novel_id ?>" class="text-decoration-none text-dark">
                    <div class="fw-bold text-truncate" style="max-width: 200px;"><?= htmlspecialchars($chapter->novel_title) ?></div>
                    <div class="small text-muted d-none d-md-block" style="font-size: 0.75rem;">
                        <?= htmlspecialchars($chapter->title) ?>
                    </div>
                </a>
            </div>

            <div class="dropdown">
                <button class="btn border-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-sliders fs-4"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end settings-card">
                    <h6 class="fw-bold mb-3">Reader Settings</h6>
                    <label class="small text-muted mb-2 d-block">Theme</label>
                    <div class="d-flex gap-3 mb-4">
                        <div class="theme-dot" style="background: #fff;" onclick="setTheme('white')"></div>
                        <div class="theme-dot" style="background: #f4ecd8;" onclick="setTheme('sepia')"></div>
                        <div class="theme-dot" style="background: #1a1a1a;" onclick="setTheme('dark')"></div>
                    </div>
                    <label class="small text-muted mb-2 d-block">Font Size</label>
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <button class="btn btn-outline-secondary btn-sm w-25" onclick="adjustFont(-1)">A-</button>
                        <span id="font-percent" class="fw-bold">100%</span>
                        <button class="btn btn-outline-secondary btn-sm w-25" onclick="adjustFont(1)">A+</button>
                    </div>
                    <label class="small text-muted mb-2 d-block">Font Style</label>
                    <select class="form-select form-select-sm" onchange="setFontFamily(this.value)">
                        <option value="'Inter', sans-serif">Modern Sans</option>
                        <option value="'Georgia', serif">Classic Serif</option>
                        <option value="'Courier New', monospace">Monospace</option>
                    </select>
                    <label class="small text-muted mb-2 d-block">Text Alignment</label>
                    <div class="d-flex btn-group w-100" role="group">
                        <button type="button" class="btn align-btn" id="align-left" onclick="setTextAlign('left')">
                            <i class="bi bi-text-left"></i>
                        </button>
                        <button type="button" class="btn align-btn" id="align-center" onclick="setTextAlign('center')">
                            <i class="bi bi-text-center"></i>
                        </button>
                        <button type="button" class="btn align-btn active" id="align-justify" onclick="setTextAlign('justify')">
                            <i class="bi bi-justify"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="content-container">
        <div class="chapter-text" id="reading-area">
            <?= htmlspecialchars($chapter->content) ?>
        </div>

        <div class="mt-5 pt-5 pb-5 d-flex justify-content-between align-items-center border-top">
            <?php if($prev_id): ?>
                <a href="read.php?id=<?= $prev_id ?>" class="text-decoration-none text-muted fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Prev
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <?php if($next_id): ?>
                <a href="read.php?id=<?= $next_id ?>" class="btn-nav-custom">Next Chapter</a>
            <?php else: ?>
                <a href="view-story.php?id=<?= $chapter->novel_id ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Finished Story</a>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }

        function setTheme(theme) {
            const body = document.getElementById('reader-body');
            body.classList.remove('theme-sepia', 'theme-dark');
            if(theme !== 'white') body.classList.add('theme-' + theme);
            localStorage.setItem('novel-theme', theme);
        }

        let currentScale = 1.0;
        function adjustFont(delta) {
            currentScale += delta * 0.1;
            if(currentScale < 0.7) currentScale = 0.7;
            if(currentScale > 1.8) currentScale = 1.8;
            document.documentElement.style.setProperty('--reader-font-size', (1.25 * currentScale) + 'rem');
            document.getElementById('font-percent').innerText = Math.round(currentScale * 100) + '%';
            localStorage.setItem('reader-scale', currentScale);
        }

        function setFontFamily(family) {
            document.documentElement.style.setProperty('--reader-family', family);
            localStorage.setItem('reader-family', family);
        }

        window.onload = () => {
            const savedTheme = localStorage.getItem('novel-theme');
            if(savedTheme) setTheme(savedTheme);
            const savedScale = localStorage.getItem('reader-scale');
            if(savedScale) { currentScale = parseFloat(savedScale); adjustFont(0); }
            const savedFamily = localStorage.getItem('reader-family');
            if(savedFamily) setFontFamily(savedFamily);
        }

        function setTextAlign(align) {
        const readerArea = document.getElementById('reading-area');
        
        readerArea.classList.remove('text-align-left', 'text-align-center', 'text-align-justify');
        
        readerArea.classList.add('text-align-' + align);

        document.querySelectorAll('.align-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('align-' + align).classList.add('active');
        
        localStorage.setItem('reader-align', align);
    }

    window.onload = () => {
        const savedAlign = localStorage.getItem('reader-align') || 'justify';
        setTextAlign(savedAlign);
    }

</script>
</body>
</html>