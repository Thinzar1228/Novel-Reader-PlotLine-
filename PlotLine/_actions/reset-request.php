<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\HTTP;

$email = $_POST['email'];
$db = new MySQL();
$pdo = $db->connect();

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Generate 8-char random password
    $temp_pass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8);
    $hashed = password_hash($temp_pass, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update->execute([$hashed, $email]);

    HTTP::redirect("/forgot-password.php", "msg=sent");
} else {
    HTTP::redirect("/forgot-password.php", "error=not_found");
}