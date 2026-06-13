<?php
include("vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

// Fetch all stories for this author
$stmt = $pdo->prepare("SELECT * FROM novels WHERE users_id = ? ORDER BY created_at DESC");
$stmt->execute([$auth_user->id]);
$all_stories = $stmt->fetchAll();

// Filter for Tabs
$published = array_filter($all_stories, fn($s) => $s->status !== 'draft');
$drafts = array_filter($all_stories, fn($s) => $s->status === 'draft');

function getLetterCover($title) {
    $first = !empty($title) ? mb_strtoupper(mb_substr($title, 0, 1)) : '?';
    return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300"><rect width="200" height="300" fill="#1a2a40"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="serif" font-weight="900" font-size="100">'.$first.'</text></svg>');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Stories - StoryHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* General Styles */
        .story-card-img { width: 110px; height: 160px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-action { padding: 5px 15px; font-size: 0.85rem; border-radius: 20px; }
        .nav-tabs .nav-link { border: none; color: #666; font-weight: 600; padding: 10px 25px; }
        .nav-tabs .nav-link.active { border-bottom: 3px solid #1a2a40; color: #1a2a40; background: none; }

        /* FIX: Dropdown Clipping & Z-Index */
        .story-card { 
            transition: transform 0.2s; 
            border: none !important; 
            overflow: visible !important; /* Allow dropdown to pop out */
            z-index: 1;
        }
        .story-card:hover { transform: translateY(-5px); z-index: 10; } /* Bring hovered card to front */
        
        .card-body, .d-flex {
            overflow: visible !important;
        }

        .dropdown-menu {
            z-index: 9999 !important;
            min-width: 160px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'components/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">My Stories</h2>
            </div>
            <a href="create-story.php" class="btn btn-primary rounded-pill px-4 shadow-sm">+ Create New Story</a>
        </div>

        <ul class="nav nav-tabs border-0 mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pubTab">Published (<?= count($published) ?>)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#draftTab">Drafts (<?= count($drafts) ?>)</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pubTab">
                <div class="row g-4">
                    <?php if (count($published) > 0): ?>
                        <?php foreach($published as $s): ?>
                            <?= renderStoryCard($s) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">You have no published works yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="draftTab">
                <div class="row g-4">
                    <?php if (count($drafts) > 0): ?>
                        <?php foreach($drafts as $s): ?>
                            <?= renderStoryCard($s) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No drafts found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php 
    function renderStoryCard($s) {
        $cover = $s->cover_image ?: getLetterCover($s->title);
        $statusClass = ($s->status == 'completed') ? 'bg-success' : (($s->status == 'ongoing') ? 'bg-primary' : 'bg-secondary');
        $isDraft = ($s->status === 'draft');
        
        ob_start(); ?>
        <div class="col-lg-6">
            <div class="card story-card shadow-sm p-3 rounded-4 h-100">
                <div class="d-flex">
                    <img src="<?= $cover ?>" class="story-card-img">
                    <div class="ms-4 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($s->title) ?></h5>
                                <span class="badge rounded-pill mb-2 <?= $statusClass ?>">
                                    <?= strtoupper($s->status) ?>
                                </span>
                            </div>
                            <button class="btn btn-outline-danger border-0 btn-sm delete-novel-btn" 
                                    data-id="<?= $s->id ?>" 
                                    data-title="<?= htmlspecialchars($s->title) ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <p class="text-muted small mb-3 text-truncate-2"><?= htmlspecialchars($s->description) ?></p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <a href="editor.php?novel_id=<?= $s->id ?>&chapter_id=1" class="btn btn-dark btn-action">Write</a>
                            <a href="create-story.php?id=<?= $s->id ?>&edit=true" class="btn btn-light border btn-action">Settings</a>
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-action dropdown-toggle" 
                                        type="button" 
                                        data-bs-toggle="dropdown" 
                                        data-bs-boundary="viewport">
                                    Status
                                </button>
                                <ul class="dropdown-menu shadow border-0 dropdown-menu-end">
                                    <?php if ($isDraft): ?>
                                        <li>
                                            <a class="dropdown-item small publish-novel-confirm" 
                                               href="javascript:void(0)" 
                                               data-id="<?= $s->id ?>">
                                                <i class="bi bi-rocket-takeoff me-2 text-primary"></i>Publish Novel
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item small" href="_actions/update-status.php?id=<?= $s->id ?>&status=ongoing">Ongoing</a></li>
                                        <li><a class="dropdown-item small" href="_actions/update-status.php?id=<?= $s->id ?>&status=completed">Completed</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item small" href="_actions/update-status.php?id=<?= $s->id ?>&status=draft">Move to Draft</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }
    ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Delete Confirmation
    document.querySelectorAll('.delete-novel-btn').forEach(button => {
        button.addEventListener('click', function() {
            const novelId = this.getAttribute('data-id');
            const novelTitle = this.getAttribute('data-title');

            Swal.fire({
                title: 'Are you sure?',
                text: `Delete "${novelTitle}"? This cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `_actions/delete-novel.php?id=${novelId}`;
                }
            });
        });
    });

    // Publish Confirmation
    document.querySelectorAll('.publish-novel-confirm').forEach(button => {
        button.addEventListener('click', function() {
            const novelId = this.getAttribute('data-id');
            Swal.fire({
                title: 'Publish this novel?',
                text: "It will be visible in the public library!",
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#1a2a40',
                confirmButtonText: 'Yes, Publish Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `_actions/update-status.php?id=${novelId}&status=ongoing&published=true`;
                }
            });
        });
    });
</script>

<?php if (isset($_GET['published'])): ?>
<script>
    Swal.fire({
        title: 'Congratulations!',
        text: 'Your story has been published to the library.',
        icon: 'success',
        confirmButtonColor: '#1a2a40'
    });
</script>
<?php endif; ?>
</body>
</html>