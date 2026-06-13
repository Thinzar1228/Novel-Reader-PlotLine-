<?php

include("../vendor/autoload.php");

use Libs\Database\MySQL;
use Libs\Database\UsersTable;
use Helpers\HTTP;

// $name     = $_POST['name'];
// $email    = $_POST['email'];
// $password = $_POST['password'];
// $confirm  = $_POST['confirm_password'];

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

$encodedName  = urlencode($name);
$encodedEmail = urlencode($email);

$table = new UsersTable(new MySQL);

$dup = $table->checkDuplicate($name, $email);

$nameTaken  = $dup->name_exists  > 0;
$emailTaken = $dup->email_exists > 0;

$encodedName  = urlencode($name);
$encodedEmail = urlencode($email);

if ($nameTaken && $emailTaken) {
    HTTP::redirect("/index.php", "register=both_taken");
}

if ($nameTaken) {
    HTTP::redirect("/index.php", "register=name_taken&email=$encodedEmail");
}

if ($emailTaken) {
    HTTP::redirect("/index.php", "register=email_taken&name=$encodedName");
}

if($password !== $confirm){
    HTTP::redirect("/index.php", "register=password_mismatch");
}

//Required fields
if(!$name || !$email || !$password || !$confirm){
   HTTP::redirect("/index.php","register=missing_fields&name=$encodedName&email=$encodedEmail");
}


//Username policy
if(strlen($name) < 3 || strlen($name) > 30){
    HTTP::redirect("/index.php", "register=name_length");
}

if(!preg_match('/^[a-zA-Z0-9_]+$/', $name)){
    HTTP::redirect("/index.php",
"register=name_invalid&email=$encodedEmail");
}


//Email validation
if(strlen($email) > 255){
  HTTP::redirect("/index.php",
"register=email_invalid&name=$encodedName");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    HTTP::redirect("/index.php", "register=email_invalid");
}


//Password policy
if(strlen($password) < 8){
    HTTP::redirect("/index.php","register=password_weak&name=$encodedName&email=$encodedEmail");
}


//Confirm match
if($password !== $confirm){
   HTTP::redirect("/index.php",
"register=password_mismatch&name=$encodedName&email=$encodedEmail");
}

$table->insert([
    "name" => $name,
    "email" => $email,
    "password" => $password
]);

HTTP::redirect("/index.php", "register=success");
