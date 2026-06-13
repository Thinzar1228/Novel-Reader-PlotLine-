<?php
include("vendor/autoload.php");
use Libs\Database\MySQL;
$auth_user = Helpers\Auth::check();

$db = new MySQL();
$pdo = $db->connect();

$novel_id = $_GET['novel_id'] ?? null;
$chapter_id = $_GET['chapter_id'] ?? null;

// Fetch Novel
$stmt = $pdo->prepare("SELECT * FROM novels WHERE id = ?");
$stmt->execute([$novel_id]);
$novel = $stmt->fetch(PDO::FETCH_OBJ);

// Fetch current
$stmt = $pdo->prepare("SELECT * FROM chapters WHERE id = ?");
$stmt->execute([$chapter_id]);
$chapter = $stmt->fetch(PDO::FETCH_OBJ);

// If novel or chapter is not found, stop the errors
if (!$novel || !$chapter) {
    die("Error: Story or Chapter not found. <a href='my-stories.php'>Go back</a>");
}

// Fetch all chapters for the sidebar
$stmt = $pdo->prepare("SELECT id, title, status FROM chapters WHERE novel_id = ? ORDER BY order_index ASC");
$stmt->execute([$novel_id]);
$all_chapters = $stmt->fetchAll(PDO::FETCH_OBJ);

function getDefaultCover($title) {
    $firstLetter = !empty($title) ? mb_strtoupper(mb_substr($title, 0, 1)) : '?';
    $bg = "1a2a40"; 
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300"><rect width="200" height="300" fill="#' . $bg . '"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="serif" font-weight="900" font-size="120">' . $firstLetter . '</text></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drafting: <?= htmlspecialchars($novel->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
    
    <style>
        :root { --sidebar-width: 300px; --brand-blue: #a3d4ff; }
        body { background-color: #fdfdfd; font-family: 'Merriweather', serif; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: #fff; border-right: 1px solid #eee; padding: 20px; overflow-y: auto; z-index: 1000; }
        .workspace { margin-left: var(--sidebar-width); min-height: 100vh; }
        .editor-nav { background: #fff; border-bottom: 1px solid #eee; padding: 12px 40px; }
        .paper { max-width: 800px; margin: 40px auto; background: #fff; padding: 60px 80px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 4px; min-height: 900px; }
        .chapter-title-input { border: none; font-size: 2rem; font-weight: 800; width: 100%; outline: none; margin-bottom: 30px; color: #1a2a40; }
        .content-area { border: none; width: 100%; min-height: 600px; font-size: 1.15rem; line-height: 1.8; outline: none; resize: none; color: #333; }
        .chapter-item { display: flex; align-items: center; border-radius: 8px; margin-bottom: 6px; padding-right: 5px;}
        .chapter-item:hover { background: #f8f9fa; }
        .chapter-item.active-row { background: #eef7ff; border-left: 4px solid #1a2a40; }
        .chapter-link { flex-grow: 1; padding: 10px 12px; color: #666; text-decoration: none; overflow: hidden; }
        .active-row .chapter-link { color: #1a2a40; font-weight: 600; }
        .publish-ch-btn { border: 2px solid #1a2a40; color: #1a2a40; font-weight: 600; }
        .publish-novel-btn { background: #1a2a40; color: #fff; border: none; font-weight: 600; }
        .spin { animation: rotation 2s infinite linear; display: inline-block; }
        @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="mb-3">
        <a href="my-stories.php" id="backBtn" class="text-decoration-none text-muted small fw-bold d-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i> Back
        </a>
    </div>

    <div class="mb-4 text-center">
        <?php $sidebar_cover = ($novel->cover_image) ? $novel->cover_image : getDefaultCover($novel->title); ?>
        <img src="<?= $sidebar_cover ?>" class="img-fluid rounded shadow-sm mb-3" style="max-height: 150px; width: 100px; object-fit: cover; background: #1a2a40;">
        <h6 class="fw-bold"><?= htmlspecialchars($novel->title) ?></h6>
    </div>
    
    <div class="d-flex justify-content-between align-items-center mb-3 mt-5 px-2">
        <span class="text-uppercase small fw-bold text-muted">Chapters</span>
        <a href="_actions/add-chapter.php?novel_id=<?= $novel_id ?>" class="btn btn-sm btn-primary rounded-circle">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>

    <div id="chapterList">
        <?php foreach($all_chapters as $ch): ?>
            <div class="chapter-item <?= $ch->id == $chapter_id ? 'active-row' : '' ?>" id="sidebar-ch-<?= $ch->id ?>">
                <a href="editor.php?novel_id=<?= $novel_id ?>&chapter_id=<?= $ch->id ?>" class="chapter-link d-flex flex-column">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text me-2"></i>
                        <span class="ch-sidebar-title"><?= htmlspecialchars($ch->title) ?></span>
                    </div>
                    <div class="ms-4 mt-1 status-badge-container">
                        <?php if($ch->status === 'published'): ?>
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size: 0.6rem;">
                                 <i class="bi bi-eye-fill"></i> Published
                            </span>
                        <?php else: ?>
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.6rem;">
                                <i class="bi bi-pencil-fill"></i> Draft
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm border-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item text-danger small delete-chapter-btn" href="javascript:void(0)" data-id="<?= $ch->id ?>" data-novel="<?= $novel_id ?>"><i class="bi bi-trash me-2"></i> Delete</a></li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="workspace">
    <nav class="editor-nav d-flex justify-content-between align-items-center sticky-top">
        <div class="small"><span id="saveStatus" class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Saved</span></div>
        <div class="d-flex align-items-center">
            <button id="saveBtn" class="btn btn-link text-dark text-decoration-none me-3 fw-bold">Save Draft</button>
            
            <button class="btn btn-outline-primary rounded-pill px-3 me-2 shadow-sm <?= $chapter->status !== 'published' ? 'd-none' : '' ?>" id="editChapterBtn">
                <i class="bi bi-pencil-square me-1"></i> Edit Chapter
            </button>

            <button class="btn publish-ch-btn rounded-pill px-3 me-2 shadow-sm <?= $chapter->status === 'published' ? 'd-none' : '' ?>" id="publishChapterBtn">
                Publish Chapter
            </button>

            <?php if ($novel->status === 'draft'): ?>
                <button class="btn publish-novel-btn rounded-pill px-4 shadow-sm" id="publishNovelBtn">Publish Novel</button>
            <?php endif; ?>
        </div>
    </nav>

    <div class="paper">
        <input type="text" id="chapterTitle" class="chapter-title-input" value="<?= htmlspecialchars($chapter->title) ?>" placeholder="Untitled Chapter" <?= $chapter->status === 'published' ? 'readonly' : '' ?>>
        <textarea id="chapterContent" class="content-area" placeholder="Tell your story..." <?= $chapter->status === 'published' ? 'readonly' : '' ?>><?= htmlspecialchars($chapter->content) ?></textarea>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const titleInput = document.getElementById('chapterTitle');
    const contentInput = document.getElementById('chapterContent');
    const saveStatus = document.getElementById('saveStatus');
    const activeSidebarTitle = document.querySelector('.active-row .ch-sidebar-title');
    const publishBtn = document.getElementById('publishChapterBtn');
    const editBtn = document.getElementById('editChapterBtn');
    const backBtn = document.getElementById('backBtn');

    // 1. BACK BUTTON ALERT LOGIC
backBtn.addEventListener('click', function(e) {
    e.preventDefault();
    const targetUrl = this.href;

    const isPublished = titleInput.hasAttribute('readonly');

    if (isPublished) {
        window.location.href = targetUrl;
        return;
    }

    Swal.fire({
        title: 'Unsaved Changes',
        text: "Your work won't be saved. Save as a draft?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1a2a40',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Save & Exit',
        cancelButtonText: 'No, Don\'t Save'
    }).then((result) => {
        if (result.isConfirmed) {
            // Case: YES -> Save the progress and keep the novel
            saveContent('draft').then(() => {
                window.location.href = targetUrl;
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Case: NO -> Delete the draft novel entirely so it's not in My Stories
            window.location.href = `_actions/cancel-novel.php?novel_id=<?= $novel_id ?>`;
        }
    });
});

    // 2. EDIT CHAPTER
    if(editBtn) {
        editBtn.addEventListener('click', () => {
            editBtn.classList.add('d-none');
            publishBtn.classList.remove('d-none');
            titleInput.removeAttribute('readonly');
            contentInput.removeAttribute('readonly');
            titleInput.focus();

            const activeBadge = document.querySelector('.active-row .status-badge-container');
            if(activeBadge) {
                activeBadge.innerHTML = `<span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.6rem;"><i class="bi bi-pencil-fill"></i> Draft</span>`;
            }
        });
    }

    // 3. SAVE LOGIC
    function saveContent(newStatus = 'draft') {
        if (titleInput.hasAttribute('readonly')) return Promise.resolve(false);

        saveStatus.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> Saving...';
        saveStatus.className = "text-muted";
        
        const data = new FormData();
        data.append('chapter_id', '<?= $chapter_id ?>');
        data.append('title', titleInput.value);
        data.append('content', contentInput.value);
        data.append('status', newStatus);

        return fetch('_actions/save-chapter.php', { method: 'POST', body: data })
        .then(res => res.json())
        .then(result => {
            if(result.success) {
                saveStatus.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Saved';
                saveStatus.className = "text-success";
                if(activeSidebarTitle) activeSidebarTitle.textContent = titleInput.value || "Untitled Chapter";
                return true;
            }
            return false;
        });
    }

    // 4. PUBLISH CHAPTER
    if(publishBtn) {
        publishBtn.addEventListener('click', () => {
            Swal.fire({
                title: 'Publish this chapter?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1a2a40',
                confirmButtonText: 'Yes, Publish'
            }).then((result) => {
                if (result.isConfirmed) {
                    saveContent('published').then((success) => {
                        if(success) {
                            publishBtn.classList.add('d-none');
                            editBtn.classList.remove('d-none');
                            titleInput.setAttribute('readonly', true);
                            contentInput.setAttribute('readonly', true);
                            const activeBadge = document.querySelector('.active-row .status-badge-container');
                            if(activeBadge) {
                                activeBadge.innerHTML = `<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size: 0.6rem;"><i class="bi bi-eye-fill"></i> Published</span>`;
                            }
                        }
                    });
                }
            });
        });
    }

    // 5. AUTO-SAVE & NOVEL PUBLISH
    let typingTimer;
    [titleInput, contentInput].forEach(el => {
        el.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => saveContent('draft'), 1500);
        });
    });

    document.getElementById('saveBtn').addEventListener('click', (e) => { e.preventDefault(); saveContent('draft'); });

    const pubNovelBtn = document.getElementById('publishNovelBtn');
    if(pubNovelBtn) {
        pubNovelBtn.addEventListener('click', () => {
            Swal.fire({ title: 'Publish Novel?', icon: 'success', showCancelButton: true, confirmButtonColor: '#1a2a40', confirmButtonText: 'Publish Now' })
            .then((result) => { if (result.isConfirmed) window.location.href = `_actions/publish-novel.php?novel_id=<?= $novel_id ?>`; });
        });
    }

      // 5. STYLISH DELETE
    document.querySelectorAll('.delete-chapter-btn').forEach(button => {
        button.addEventListener('click', function() {
            const chapterId = this.getAttribute('data-id');
            const novelId = this.getAttribute('data-novel');
            Swal.fire({
                title: 'Are you sure?',
                text: "This chapter will be gone forever!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1a2a40',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `_actions/delete-chapter.php?id=${chapterId}&novel_id=${novelId}`;
                }
            });
        });
    });
</script>
</body>
</html>