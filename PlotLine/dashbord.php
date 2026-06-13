<?php
include("vendor/autoload.php");
use Helpers\Auth;

$auth_user = Auth::check();
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
</body>
</html>