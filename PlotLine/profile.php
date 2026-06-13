<?php
include("vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Libs\Database\NovelaTable;
use Helpers\Auth;

$auth_user = Auth::check();
$db = new MySQL();
$pdo = $db->connect(); 
$userTable = new UsersTable($db);
$novelaTable = new NovelaTable($db);

$profile_id = $_GET['id'] ?? $auth_user->id;
$profile = $userTable->findById($profile_id); 

if (!$profile) die("User not found.");

$followerCount = $userTable->getFollowerCount($profile->id); 
$followingCount = $userTable->getFollowingCount($profile->id);
$worksCount = $novelaTable->getWorksCount($profile->id);
$recentFollowers = $userTable->getRecentFollowers($profile->id);

$isFollowing = $userTable->isFollowing($auth_user->id, $profile->id);
$isOwner = ($auth_user->id == $profile->id);

$stmt = $pdo->prepare("
    SELECT n.*, 
    (SELECT AVG(rating) FROM ratings WHERE novel_id = n.id) as avg_rating,
    (SELECT COUNT(id) FROM chapters WHERE novel_id = n.id AND status = 'published') as chapter_count 
    FROM novels n 
    WHERE n.users_id = ? AND n.status != 'draft' 
    ORDER BY n.updated_at DESC
");
$stmt->execute([$profile->id]);
$published_works = $stmt->fetchAll();

$display_works = array_slice($published_works, 0, 3);
$has_more = count($published_works) > 3;

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
    <title><?= htmlspecialchars($profile->name) ?> - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .cover-photo { height: 250px; background-color: #a3d4ff; background-position: center; background-size: cover; }
        .profile-img-wrapper { margin-top: -75px; }

        /* --- REPLICATED CARD STYLE FROM BOOKMARK PAGE --- */
        .novel-card { 
            text-decoration: none !important; 
            color: inherit; 
            display: block;
            margin-bottom: 15px;
        }

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
        }

        .novel-meta-line { 
            font-size: 0.8rem; 
            color: #64748b; 
            margin-top: 2px;
        }
        /* --- END REPLICATED CARD STYLE --- */

        .btn-outline-primary { border-color: #1a2a40; color: #1a2a40; }
        .btn-outline-primary:hover { background-color: #1a2a40; border-color: #1a2a40; color: white; }

        .hover-bg-light:hover {
            background-color: #f8f9fa;
            transition: 0.2s;
        }
        /* Custom Scrollbar */
        .user-list-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .user-list-scroll::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
</style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="cover-photo" style="background-image: url('<?= $profile->cover_image ?? 'default-cover.jpg' ?>');">
                <div class="d-flex justify-content-end p-3">
                    <?php if ($isOwner): ?>
                        <a href="edit-profile.php" class="btn btn-light btn-sm opacity-75"><i class="bi bi-pencil"></i> Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row">
                    <div class="col-md-auto text-center text-md-start">
                        <div class="profile-img-wrapper">
                            <?php if($profile->profile_image): ?>
                                <img src="<?= $profile->profile_image ?>" class="rounded-circle border border-4 border-white shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle border border-4 border-white shadow-sm bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px; font-size: 4rem;">
                                    <?= strtoupper(substr($profile->name, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md mt-3 mt-md-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="fw-bold mb-0"><?= htmlspecialchars($profile->name) ?></h2>
                                <p class="text-muted mb-2">@<?= strtolower(str_replace(' ', '', $profile->name)) ?></p>
                            </div>
                            <?php if (!$isOwner): ?>
                                <form action="_actions/toggle-follow.php" method="POST">
                                    <input type="hidden" name="following_id" value="<?= $profile->id ?>">
                                    <button type="submit" class="btn <?= $isFollowing ? 'btn-secondary' : 'btn-primary' ?> btn-sm rounded-pill px-4">
                                        <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p class="mb-3"><?= htmlspecialchars($profile->bio ?? 'No bio yet.') ?></p>
                        <div class="d-flex gap-4">
                            <a href="#" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#followersModal">
                                <strong><?= number_format($followerCount) ?></strong> <small class="text-muted">FOLLOWERS</small>
                            </a>
                            <a href="#" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#followingModal">
                                <strong><?= number_format($followingCount) ?></strong> <small class="text-muted">FOLLOWING</small>
                            </a>
                            <span><strong><?= number_format($worksCount) ?></strong> <small class="text-muted">WORKS</small></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Published Works</h4>
                    <?php if ($has_more): ?>
                        <?php if ($isOwner): ?>
                            <a href="my-stories.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Manage All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#allWorksModal">
                                View All (<?= count($published_works) ?>)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <?php if (count($display_works) > 0): ?>
                        <?php foreach ($display_works as $book): ?>
                            <div class="col-6 col-md-4">
                                <a href="view-story.php?id=<?= $book->id ?>" class="novel-card">
                                    <div class="card-img-wrapper">
                                        <img src="<?= $book->cover_image ?: getLetterCover($book->title) ?>" alt="Cover">
                                        
                                        <div class="badge-status"><?= htmlspecialchars($book->status) ?></div>
                                        <div class="badge-rating-top">
                                            <i class="bi bi-star-fill"></i>
                                            <?= number_format($book->avg_rating ?? 0.0, 1) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="novel-title"><?= htmlspecialchars($book->title) ?></div>
                                    <div class="novel-meta-line">
                                        By <?= htmlspecialchars($profile->name) ?> | <?= $book->chapter_count ?> Chapters
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 bg-white rounded shadow-sm border">
                            <i class="bi bi-journal-x fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No public stories found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="fw-bold mb-3">Recent Followers</h5>
                    <?php foreach ($recentFollowers as $follower): ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= $follower->profile_image ?: 'https://ui-avatars.com/api/?name='.urlencode($follower->name) ?>" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="small fw-bold"><?= htmlspecialchars($follower->name) ?></div>
                                <div class="text-muted small">@<?= strtolower(str_replace(' ', '', $follower->name)) ?></div>
                            </div>
                            <a href="profile.php?id=<?= $follower->id ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($has_more): ?>
    <div class="modal fade" id="allWorksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">All Stories by <?= htmlspecialchars($profile->name) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-4">
                        <?php foreach ($published_works as $book): ?>
                            <div class="col">
                                <a href="view-story.php?id=<?= $book->id ?>" class="novel-card">
                                    <div class="card-img-wrapper">
                                        <img src="<?= $book->cover_image ?: getLetterCover($book->title) ?>" alt="Cover">
                                        <div class="badge-status"><?= htmlspecialchars($book->status) ?></div>
                                        <div class="badge-rating-top">
                                            <i class="bi bi-star-fill"></i>
                                            <?= number_format($book->avg_rating ?? 0.0, 1) ?>
                                        </div>
                                    </div>
                                    <div class="novel-title"><?= htmlspecialchars($book->title) ?></div>
                                    <div class="novel-meta-line">
                                        By <?= htmlspecialchars($profile->name) ?> | <?= $book->chapter_count ?> Chapters
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="followersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Followers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="user-list-scroll" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $followersList = $userTable->getFollowers($profile->id); // You'll need this method in UsersTable
                    if (count($followersList) > 0): 
                        foreach ($followersList as $u): ?>
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded-3 hover-bg-light">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $u->profile_image ?: 'https://ui-avatars.com/api/?name='.urlencode($u->name) ?>" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($u->name) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">@<?= strtolower(str_replace(' ', '', $u->name)) ?></div>
                                    </div>
                                </div>
                                <a href="profile.php?id=<?= $u->id ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                            </div>
                        <?php endforeach; 
                    else: ?>
                        <p class="text-center text-muted py-4">No followers yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="followingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Following</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="user-list-scroll" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $followingList = $userTable->getFollowing($profile->id); // You'll need this method in UsersTable
                    if (count($followingList) > 0): 
                        foreach ($followingList as $u): ?>
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded-3 hover-bg-light">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $u->profile_image ?: 'https://ui-avatars.com/api/?name='.urlencode($u->name) ?>" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($u->name) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">@<?= strtolower(str_replace(' ', '', $u->name)) ?></div>
                                    </div>
                                </div>
                                <a href="profile.php?id=<?= $u->id ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View</a>
                            </div>
                        <?php endforeach; 
                    else: ?>
                        <p class="text-center text-muted py-4">Not following anyone yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>