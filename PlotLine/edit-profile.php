<?php
include("vendor/autoload.php");
$user = Helpers\Auth::check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Document</title>
</head>
<body>
    <?php include 'components\navbar.php'; ?>
    
    <div class="container py-5">
    <div class="row justify-content-center">
        
        <div class="col-md-8">
            <a href="profile.php" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm mb-3">
    <i class="bi bi-chevron-left"></i> Back
</a>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4">
                    <h4 class="fw-bold"><i class="bi bi-person-gear me-2"></i>Edit Profile</h4>
                </div>
                <div class="card-body">
                    <form action="_actions/update-profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">About You (Bio)</label>
                            <textarea name="bio" class="form-control" rows="3" placeholder="Tell your readers about yourself..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Profile Picture</label>
                            <input type="file" name="profile_image" class="form-control mb-2">
                            <small class="text-muted">Recommended: Square image (500x500px)</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control mb-2">
                            <small class="text-muted">Recommended: Wide image (1200x300px)</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="profile.php" class="text-secondary text-decoration-none">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
