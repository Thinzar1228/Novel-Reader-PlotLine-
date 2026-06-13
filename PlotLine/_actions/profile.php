<?php
include("vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Libs\Database\NovelaTable;
use Helpers\Auth;

$user = Auth::check();
$db = new MySQL();

$userTable = new UsersTable($db);
$novelaTable = new NovelaTable($db);


$followerCount = $userTable->getFollowerCount($user->id);
$followingCount = $userTable->getFollowingCount($user->id);
$worksCount = $novelaTable->getWorksCount($user->id);

$books = $novelaTable->getByUserId($user->id);
?>