<?php
include("vendor/autoload.php");
use Libs\Database\MySQL;
$auth_user = Helpers\Auth::check();

$db = new MySQL();
$pdo = $db->connect();

// Check if we are editing an existing story
$novel_id = $_GET['id'] ?? null;
$existing_novel = null;
$selected_genres = [];

if ($novel_id) {
    $stmt = $pdo->prepare("SELECT * FROM novels WHERE id = ? AND users_id = ?");
    $stmt->execute([$novel_id, $auth_user->id]);
    $existing_novel = $stmt->fetch();

    // Fetch already selected genres for this novel
    $stmt = $pdo->prepare("SELECT genre_id FROM novel_genres WHERE novel_id = ?");
    $stmt->execute([$novel_id]);
    $selected_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Helper to generate a default SVG cover based on the first letter
function getDefaultCover($title) {
    $firstLetter = !empty($title) ? mb_strtoupper(mb_substr($title, 0, 1)) : '?';
    $bg = "1a2a40"; 
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900">
                <rect width="600" height="900" fill="#' . $bg . '"/>
                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" 
                      fill="#ffffff" font-family="serif" font-weight="900" font-size="300">
                    ' . $firstLetter . '
                </text>
            </svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $existing_novel ? 'Edit' : 'Create' ?> Story - Novela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .invalid-text { display: none; color: #dc3545; font-size: 0.8rem; margin-top: 5px; font-weight: 500; }
        .was-validated .form-control:invalid ~ .invalid-text { display: block; }
        
        /* Custom Scrollbar for Genre Box */
        .genre-selection-box {
            max-height: 220px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #ffffff;
        }
        .genre-selection-box::-webkit-scrollbar { width: 6px; }
        .genre-selection-box::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }

        .cover-upload-box { transition: all 0.3s ease; background: #1a2a40; border: 2px dashed #ccc; }
        .btn-next { background-color: #4389ec; color: white; font-weight: 600; border-radius: 50px; }
    </style>
</head>
<body>

<div class="container-fluid bg-white border-bottom py-2 sticky-top shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="javascript:history.back()" class="text-muted text-decoration-none me-3">
                <i class="bi bi-chevron-left fs-5"></i>
            </a>
            <div>
                <span class="text-muted small">Step 1: Basic Info</span>
                <h5 class="fw-bold mb-0" id="headerTitle">
                    <?= $existing_novel ? htmlspecialchars($existing_novel->title) : 'Untitled Story' ?>
                </h5>
            </div>
        </div>
        <div>
            <button form="novelForm" type="submit" class="btn btn-next px-4">Save & Next</button>
        </div>
    </div>
</div>

<div class="container mt-5 mb-5">
    <form id="novelForm" class="needs-validation" action="_actions/create-novel-action.php" method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="novel_id" value="<?= $novel_id ?>">

        <div class="row g-5">
            <div class="col-md-4">
                <label for="coverInput" class="cover-upload-box text-center flex-column d-flex align-items-center justify-content-center shadow-sm" style="width: 100%; height: 480px; border-radius: 12px; cursor: pointer; overflow: hidden; position: relative;">
                    <?php 
                        $titleForCover = $existing_novel ? $existing_novel->title : '';
                        $defaultCover = getDefaultCover($titleForCover);
                        $currentImg = ($existing_novel && $existing_novel->cover_image) ? $existing_novel->cover_image : $defaultCover;
                    ?>
                    <img src="<?= $currentImg ?>" id="coverPreview" style="width: 100%; height: 100%; object-fit: cover;">
                    <div id="uploadPlaceholder" class="position-absolute text-white px-3 py-2 rounded-pill" style="background: rgba(0, 0, 0, 0.6); bottom: 20px;">
                        <i class="bi bi-camera-fill me-1"></i> Change Cover
                    </div>
                    <input type="file" name="cover_image" id="coverInput" class="d-none" accept="image/*">
                </label>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h5 class="fw-bold mb-4">Story Details</h5>
                    
                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase text-muted">Title</label>
                        <input type="text" name="title" id="titleInput" class="form-control form-control-lg border-light-subtle" 
                               value="<?= $existing_novel ? htmlspecialchars($existing_novel->title) : '' ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold mb-2 small text-uppercase text-muted">Synopsis</label>
                        <textarea name="description" class="form-control border-light-subtle" rows="5" required><?= $existing_novel ? htmlspecialchars($existing_novel->description) : '' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase text-muted">Select Genres (Max 3 recommended)</label>
                        <div class="genre-selection-box">
                            <div class="row">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM genres ORDER BY name ASC");
                                $genres = $stmt->fetchAll();
                                foreach($genres as $genre):
                                    $checked = in_array($genre->id, $selected_genres) ? 'checked' : '';
                                ?>
                                <div class="col-6 col-lg-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="genres[]" 
                                               value="<?= $genre->id ?>" id="genre_<?= $genre->id ?>" <?= $checked ?>>
                                        <label class="form-check-label small" for="genre_<?= $genre->id ?>">
                                            <?= htmlspecialchars($genre->name) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="form-text text-muted">Pick genres that best describe your story.</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // 1. Validation Logic
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false)
        })
    })()

    // 2. Dynamic Title and Cover Preview
    const titleInput = document.getElementById('titleInput');
    const headerTitle = document.getElementById('headerTitle');
    const coverPreview = document.getElementById('coverPreview');

    titleInput.addEventListener('input', function() {
        const titleValue = this.value.trim();
        headerTitle.textContent = titleValue || "Untitled Story";

        if (coverPreview.src.startsWith('data:image/svg')) {
            const firstLetter = titleValue ? titleValue.charAt(0).toUpperCase() : '?';
            const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="900" viewBox="0 0 600 900">
                <rect width="600" height="900" fill="#1a2a40"/>
                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="serif" font-weight="900" font-size="300">${firstLetter}</text>
            </svg>`;
            coverPreview.src = 'data:image/svg+xml;base64,' + btoa(svg);
        }
    });

    // 3. Image Upload Preview
    document.getElementById('coverInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => coverPreview.src = e.target.result;
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>