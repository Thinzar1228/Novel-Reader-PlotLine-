<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Helpers\Auth;
use Helpers\HTTP;

$auth_user = Auth::check();
$following_id = $_POST['following_id'];
$db = (new MySQL())->connect();

// Check if already following
$stmt = $db->prepare("SELECT id FROM follows WHERE follower_id = :f1 AND following_id = :f2");
$stmt->execute(['f1' => $auth_user->id, 'f2' => $following_id]);
$follow = $stmt->fetch();

if ($follow) {
    // Unfollow
    $stmt = $db->prepare("DELETE FROM follows WHERE id = :id");
    $stmt->execute(['id' => $follow->id]);
} else {
    // Follow
    $stmt = $db->prepare("INSERT INTO follows (follower_id, following_id, created_at) VALUES (:f1, :f2, NOW())");
    $stmt->execute(['f1' => $auth_user->id, 'f2' => $following_id]);
}

HTTP::redirect("/profile.php?id=$following_id");