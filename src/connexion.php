<?php
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

$host = "localhost";
$user = "root";
$password = "root";
$dbname = "fidelia";
/*
$host = "lemaniockvpersis.mysql.db";
$user = "lemaniockvpersis";
$password = "BossGroup51";
$dbname = "lemaniockvpersis";*/

$pdo = null;
try{
$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname,
        $user,
        $password,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e) {
  echo "Erreur!: " . $e->getMessage() . "<br/>";
  die();
}

?>
