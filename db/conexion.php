<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "pos-system";

try {
    $pdo = new PDO("mysql:host=$host;port=3310;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
