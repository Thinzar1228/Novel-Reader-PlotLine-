<?php
include("vendor/autoload.php");

use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect();

$novel_id = $_GET['id'] ?? null;

if (!$novel_id) {
    header("Location: index.php");
    exit();
}

// 1. Fetch Novel and Author
$stmt = $pdo->prepare("
    SELECT n.*, u.name AS author_name, u.profile_image AS author_image,
    (SELECT AVG(rating) FROM ratings WHERE novel_id = n.id) as avg_rating,
    (SELECT COUNT(id) FROM ratings WHERE novel_id = n.id) as total_ratings
    FROM novels n
    JOIN users u ON n.users_id = u.id
    WHERE n.id = ?
");
$stmt->execute([$novel_id]);
$novel = $stmt->fetch();

if (!$novel) die("Story not found.");

// --- GENRE FETCH ---
$stmt = $pdo->prepare("
    SELECT g.name 
    FROM genres g
    JOIN novel_genres ng ON g.id = ng.genre_id
    WHERE ng.novel_id = ?
");
$stmt->execute([$novel_id]);
$genres = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Define sorting order
$sort = $_GET['sort'] ?? 'desc'; 
$order = ($sort === 'asc') ? 'ASC' : 'DESC';

// 2. Fetch Published Chapters
$stmt = $pdo->prepare("
    SELECT * FROM chapters 
    WHERE novel_id = ? AND status = 'published' 
    ORDER BY chapter_number $order
");
$stmt->execute([$novel_id]);
$chapters = $stmt->fetchAll();

// Ensure "Read Story" always starts at Chapter 1 regardless of sort
$stmt = $pdo->prepare("SELECT id FROM chapters WHERE novel_id = ? AND status = 'published' ORDER BY chapter_number ASC LIMIT 1");
$stmt->execute([$novel_id]);
$first_chapter_id = $stmt->fetchColumn();

// 3. Status logic
$stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE users_id = ? AND novel_id = ?");
$stmt->execute([$auth_user->id, $novel_id]);
$isBookmarked = $stmt->fetch();

$stmt = $pdo->prepare("SELECT rating FROM ratings WHERE users_id = ? AND novel_id = ?");
$stmt->execute([$auth_user->id, $novel_id]);
$userRating = $stmt->fetchColumn() ?: 0;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($novel->title) ?> | Novela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #0076fc; --bg-main: #ffffff; }
        body { background-color: var(--bg-main); color: #0f172a; font-family: 'Inter', -apple-system, sans-serif; }
        
        /* Fixed Back Button */
        .btn-back-prev { 
            display: inline-flex; align-items: center; gap: 8px;
            color: #64748b; text-decoration: none; font-weight: 500;
            padding: 10px 0; transition: 0.2s; border: none; background: none;
        }
        .btn-back-prev:hover { color: var(--primary); transform: translateX(-4px); }

        /* Cover & UI Styling */
        .novel-cover { 
            width: 100%; max-width: 210px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08); aspect-ratio: 2/3; object-fit: cover;
        }

        .btn-read-main { 
            background: var(--primary); color: white; border: none; padding: 12px 35px;
            border-radius: 12px; font-weight: 700; transition: 0.3s;
        }
        .btn-read-main:hover { background: #0061d5; box-shadow: 0 8px 20px rgba(0,118,252,0.25); transform: translateY(-2px); }

        /* Dynamic UI Buttons */
        .btn-action-soft { 
            background: #f1f5f9; color: #475569; border: 1px solid transparent; 
            padding: 12px 20px; border-radius: 12px; transition: 0.2s;
        }
        .btn-action-soft:hover { background: #e2e8f0; color: #1e293b; }
        
        /* Bookmarked State */
        .is-bookmarked { background: #eff6ff; color: var(--primary); border-color: #dbeafe; }
        .is-bookmarked i { color: var(--primary); }

        /* Rated State */
        .is-rated { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
        .is-rated i { color: #f59e0b; }

        .genre-link { 
            background: #f1f5f9; color: #64748b; padding: 6px 14px; border-radius: 8px;
            text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s;
        }
        .genre-link:hover { background: var(--primary); color: white; }

        /*Chapter Rows */
    .chapter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 15px;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        color: inherit;
        transition: 0.2s ease;
    }
    .chapter-row:hover { background-color: #f8fafc; color: inherit; }
    
    .chapter-info { display: flex; align-items: center; gap: 20px; }
    
    .chapter-num { 
        font-size: 1.2rem; 
        font-weight: 800; 
        color: #cbd5e1; 
        min-width: 40px; 
    }
    
    .chapter-title { 
        font-weight: 600; 
        font-size: 1.05rem; 
        color: #1e293b; 
        margin-bottom: 2px;
    }
    
    .chapter-meta { 
        font-size: 0.85rem; 
        color: #94a3b8; 
    }

        /* Synopsis Expand/Collapse */
    .synopsis-container {
        position: relative;
        max-height: 120px;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .synopsis-container.expanded {
        max-height: 2000px;
    }

    /*fade effect */
    .synopsis-fade {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 50px;
        background: linear-gradient(transparent, var(--bg-main));
        pointer-events: none;
    }

    .expanded .synopsis-fade {
        display: none;
    }

    .btn-read-more {
        color: var(--primary);
        background: none;
        border: none;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 5px 0;
        cursor: pointer;
        text-transform: uppercase;
    }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row g-4 g-lg-5">
            <div class="col-md-4 col-lg-3 text-center">
                <img src="<?= getCover($novel->cover_image, $novel->title) ?>" class="novel-cover" alt="Cover">
            </div>

            <div class="col-md-8 col-lg-9">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach($genres as $g): ?>
                        <a href="browse.php?genre=<?= urlencode($g) ?>" class="genre-link"><?= htmlspecialchars($g) ?></a>
                    <?php endforeach; ?>
                    <span class="badge bg-white border text-primary rounded-pill px-3 py-2 text-uppercase fw-bold" style="font-size: 0.65rem;"><?= $novel->status ?></span>
                </div>

                <h1 class="display-6 fw-bold mb-2"><?= htmlspecialchars($novel->title) ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center gap-1 text-warning fw-bold">
                        <i class="bi bi-star-fill"></i> <?= number_format($novel->avg_rating, 1) ?: '0.0' ?>
                        <span class="text-muted fw-normal small ms-1">(<?= $novel->total_ratings ?> rated)</span>
                    </div>
                    <div class="text-muted">|</div>
                    <div class="small fw-medium">Author: <a href="profile.php?id=<?= $novel->users_id ?>" class="text-primary text-decoration-none"><?= htmlspecialchars($novel->author_name) ?></a></div>
                </div>

                <div class="d-flex flex-wrap gap-3 mb-5">
                    <?php if ($first_chapter_id): ?>
                        <a href="read.php?id=<?= $first_chapter_id ?>" class="btn-read-main text-decoration-none">READ STORY</a>
                    <?php endif; ?>

                    <div class="m-0">
                        <button id="bookmarkBtn" 
                                class="btn-action-soft <?= $isBookmarked ? 'is-bookmarked' : '' ?>" 
                                onclick="toggleBookmark(<?= $novel->id ?>)">
                            <i id="bookmarkIcon" class="bi <?= $isBookmarked ? 'bi-bookmark-check-fill' : 'bi-bookmark' ?> me-2"></i>
                            <span id="bookmarkText"><?= $isBookmarked ? 'Saved' : 'Bookmark' ?></span>
                        </button>
                    </div>

                    <?php if ($auth_user->id !== $novel->users_id): ?>
                        <button class="btn-action-soft <?= $userRating > 0 ? 'is-rated' : '' ?>" data-bs-toggle="modal" data-bs-target="#ratingModal">
                            <i class="bi <?= $userRating > 0 ? 'bi-star-fill' : 'bi-star' ?> me-2"></i>
                            <?= $userRating > 0 ? 'Rated ' . $userRating : 'Rate' ?>
                        </button>
                    <?php endif; ?>

                    <button class="btn-action-soft" onclick="shareStory()"><i class="bi bi-share"></i></button>

                    <?php if ($auth_user->id !== $novel->users_id): ?>
                        <button class="btn-action-soft text-danger" data-bs-toggle="modal" data-bs-target="#reportModal">
                            <i class="bi bi-flag"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="fw-bold"><i class="bi bi-flag-fill text-danger me-2"></i>Report Story</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="_actions/report-novel.php" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="novel_id" value="<?= $novel->id ?>">
                                <label class="form-label small fw-bold text-muted">REASON FOR REPORT</label>
                                <select name="reason" class="form-select mb-3" required>
                                    <option value="">Select a reason...</option>
                                    <option value="Plagiarism">Plagiarism (Stolen Work)</option>
                                    <option value="Inappropriate Content">Inappropriate/Explicit Content</option>
                                    <option value="Harassment">Harassment/Hate Speech</option>
                                    <option value="Spam">Spam/Advertising</option>
                                    <option value="Other">Other</option>
                                </select>
                                <textarea name="details" class="form-control" rows="3" placeholder="Additional details (optional)"></textarea>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-3 px-4">Submit Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['reported'])): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    if (window.history.replaceState) {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('reported');
                        window.history.replaceState({}, '', url.href);
                    }

                    //Show the alert
                    Swal.fire({
                        title: 'Reported',
                        text: 'Thank you. Our team will review this story.',
                        icon: 'success',
                        confirmButtonColor: '#0076fc'
                    });
                </script>
            <?php endif; ?>

                <h5 class="fw-bold mb-3">Synopsis</h5>
                <div id="synopsisWrapper" class="synopsis-container">
                    <p class="text-secondary lh-lg mb-0" style="white-space: pre-line;">
                        <?= htmlspecialchars($novel->description) ?>
                    </p>
                    <div class="synopsis-fade"></div>
                </div>
                <button id="readMoreBtn" class="btn-read-more" onclick="toggleSynopsis()">Read More</button>
            </div>
        </div>

        <div class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                <h4 class="fw-bold m-0">Chapters <span class="text-muted fw-normal fs-6 ms-2">(<?= count($chapters) ?>)</span></h4>
                
                <button onclick="toggleSort('<?= ($sort === 'desc' ? 'asc' : 'desc') ?>')" class="btn btn-link text-decoration-none fw-bold small text-primary p-0">
                    <i class="bi bi-arrow-down-up me-1"></i>
                    <?= $sort === 'desc' ? 'Newest First' : 'Oldest First' ?>
                </button>
            </div>

            <div class="chapter-list">
                <?php foreach ($chapters as $chapter): ?>
                    <a href="read.php?id=<?= $chapter->id ?>" class="chapter-row">
                        <div class="chapter-info">
                            <div class="chapter-num"><?= $chapter->chapter_number ?></div>
                            <div>
                                <div class="chapter-title"><?= htmlspecialchars($chapter->title ?: 'Untitled Episode') ?></div>
                                <div class="chapter-meta">Published: <?= date('M d, Y', strtotime($chapter->created_at)) ?></div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted opacity-50"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="ratingForm">
                    <div class="modal-body text-center p-4">
                        <h5 class="fw-bold mb-3">Your Rating</h5>
                        <input type="hidden" name="novel_id" value="<?= $novel->id ?>">
                        
                        <div class="d-flex justify-content-center flex-row-reverse mb-4">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="d-none star-input" <?= ($userRating == $i) ? 'checked' : '' ?> required>
                                <label for="star<?= $i ?>" class="px-1" style="cursor:pointer">
                                    <i class="bi bi-star-fill fs-2 text-light rating-star-icon" id="icon-<?= $i ?>"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold border-0" style="background: var(--primary);">Save Rating</button>
                        
                        <?php if ($userRating > 0): ?>
                            <div class="mt-3">
                                <a href="_actions/remove-rating.php?novel_id=<?= $novel->id ?>" onclick="handleRatingRemove(event)" class="text-danger small text-decoration-none fw-bold">Undo Rating</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Modal Star Interaction */
        .star-input:checked ~ label i,
        label:hover i,
        label:hover ~ label i { color: #f59e0b !important; }
    </style>


    <script>
    function toggleSort(newSort) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', newSort);
        window.location.replace(url.href);
    }

    function handleRatingRemove(e) {
        e.preventDefault();
        const url = e.currentTarget.href;
        window.location.replace(url);
    }

    function shareStory() {
        if (navigator.share) {
            navigator.share({ title: '<?= addslashes($novel->title) ?>', url: window.location.href });
        } else {
            navigator.clipboard.writeText(window.location.href);
            alert("Link copied to clipboard!");
        }
    }

        function toggleSynopsis() {
        const wrapper = document.getElementById('synopsisWrapper');
        const btn = document.getElementById('readMoreBtn');
        
        wrapper.classList.toggle('expanded');
        
        if (wrapper.classList.contains('expanded')) {
            btn.innerText = "Show Less";
        } else {
            btn.innerText = "Read More";
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('synopsisWrapper');
        const btn = document.getElementById('readMoreBtn');
        if (wrapper.scrollHeight <= 125) {
            btn.style.display = 'none';
            if(document.querySelector('.synopsis-fade')) {
                document.querySelector('.synopsis-fade').style.display = 'none';
            }
        }
    });

    function toggleBookmark(novelId) {
    const btn = document.getElementById('bookmarkBtn');
    const icon = document.getElementById('bookmarkIcon');
    const text = document.getElementById('bookmarkText');

    const formData = new FormData();
    formData.append('novel_id', novelId);

    fetch('_actions/toggle-bookmark.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.status === 'added') {
            btn.classList.add('is-bookmarked');
            icon.classList.remove('bi-bookmark');
            icon.classList.add('bi-bookmark-check-fill');
            text.innerText = 'Saved';
        } else if (data.status === 'removed') {
            btn.classList.remove('is-bookmarked');
            icon.classList.remove('bi-bookmark-check-fill');
            icon.classList.add('bi-bookmark');
            text.innerText = 'Bookmark';
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
    });
}

// Handle Rating Form Submission
const ratingForm = document.getElementById('ratingForm');
if (ratingForm) {
    ratingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('_actions/rate-novel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            window.location.replace(window.location.href);
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Failed to save rating.");
        });
    });
}

// Handle Rating Removal (Undo)
function handleRatingRemove(e) {
    e.preventDefault();
    const url = e.currentTarget.href;
    window.location.replace(url);
}

// Handle Sort Toggle
function toggleSort(newSort) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', newSort);
    window.location.replace(url.href);
}
</script>
</body>
</html>