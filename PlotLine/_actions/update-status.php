<?php
include("../vendor/autoload.php");
use Libs\Database\MySQL;
use Helpers\Auth;

$auth_user = Auth::check();
$id = $_GET['id'];
$status = $_GET['status'];

$db = new MySQL();
$pdo = $db->connect();

$stmt = $pdo->prepare("UPDATE novels SET status = ? WHERE id = ? AND users_id = ?");
$stmt->execute([$status, $id, $auth_user->id]);

header("Location: ../my-stories.php");