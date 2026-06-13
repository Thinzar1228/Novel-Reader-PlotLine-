<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Helpers\HTTP;
use Helpers\Auth;

$user = Auth::check();
$table = new UsersTable(new MySQL());

$bio = $_POST['bio'] ?? $user->bio;

$data = [
    'bio' => $bio,
    'profile_image' => $user->profile_image, 
    'cover_image' => $user->cover_image     
];

function handleUpdateUpload($fileKey, $folder, $oldPath) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $type = $_FILES[$fileKey]['type'];
        $tmp = $_FILES[$fileKey]['tmp_name'];

        if ($type === "image/jpeg" || $type === "image/png") {
            if ($oldPath && file_exists("../" . $oldPath)) {
                unlink("../" . $oldPath);
            }

            $name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES[$fileKey]['name']);
            move_uploaded_file($tmp, "../uploads/$folder/$name");
            return "uploads/$folder/$name";
        }
    }
    return $oldPath;
}

// Process Profile Image
$data['profile_image'] = handleUpdateUpload('profile_image', 'profiles', $user->profile_image);

// Process Cover Image
$data['cover_image'] = handleUpdateUpload('cover_image', 'covers', $user->cover_image);

// Update Database
if ($table->updateProfile($user->id, $data)) {
    session_start();
    $user->bio = $data['bio'];
    $user->profile_image = $data['profile_image'];
    $user->cover_image = $data['cover_image'];
    $_SESSION['user'] = $user;

    HTTP::redirect("/profile.php", "update=success");
} else {
    HTTP::redirect("/edit-profile.php", "error=db_failed");
}