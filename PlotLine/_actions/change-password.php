<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Helpers\Auth;
use Helpers\HTTP;

$auth_user = Auth::check();
$db = new MySQL();
$userTable = new UsersTable($db);

$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

//Validate Input
if (empty($old_password) || empty($new_password)) {
    HTTP::redirect("/settings.php", "error=missing_fields");
}

$user = $userTable->findById($auth_user->id);

//Verify Old Password
if (!password_verify($old_password, $user->password)) {
    HTTP::redirect("/settings.php", "error=incorrect_old_password");
}

//Check if new password is same as old
if ($old_password === $new_password) {
    HTTP::redirect("/settings.php", "error=same_password");
}

//Hash and Update
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
$result = $userTable->updatePassword($user->id, $hashedPassword);

if ($result) {
    HTTP::redirect("/settings.php", "success=password_updated");
} else {
    HTTP::redirect("/settings.php", "error=unknown_error");
}