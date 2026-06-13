<?php
include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Libs\Database\NovelaTable;
use Helpers\Auth;

$auth_user = Auth::check();
$query = $_GET['q'] ?? '';
$db = new MySQL();

header('Content-Type: application/json');

if (strlen($query) < 2) {
    echo json_encode(['type' => 'none', 'results' => []]);
    exit();
}

// If starts with @, search Users. Else, search Novels.
if (strpos($query, '@') === 0) {
    $searchTerm = substr($query, 1);
    $table = new UsersTable($db);
    
    $results = $table->search($searchTerm, $auth_user->id); 
    
    echo json_encode(['type' => 'user', 'results' => $results]);
} else {
    $table = new NovelaTable($db);
    $results = $table->searchNovels($query);
    echo json_encode(['type' => 'novel', 'results' => $results]);
}