<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Helpers\HTTP;

$email = $_POST['email'];
$password = $_POST['password'];
$remember = isset($_POST['remember_me']);

$table = new UsersTable(new MySQL);
$user = $table->find($email, $password);

if($user === "email_not_found") {
    HTTP::redirect("/index.php", "error=email_not_found");
} elseif($user === "wrong_password") {
    HTTP::redirect("/index.php", "error=wrong_password&keep_email=" . urlencode($email));
} elseif($user) {
    if($user->suspended) {
        HTTP::redirect("/index.php", "suspended=account");
    }

    // Remember Me
    if ($remember) {
        setcookie('remembered_email', $email, time() + (86400 * 30), "/");
    } else {
        setcookie('remembered_email', '', time() - 3600, "/");
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user'] = $user;

    // Role-based Admin
    if ((int)$user->role_id === 3 || (int)$user->role_id === 2) {
        HTTP::redirect("/admin/index.php"); 
    } else {
        HTTP::redirect("/home.php"); 
    }
}