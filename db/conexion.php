<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "pos-system";

try {
    $conn = new PDO("mysql:host=$host;port=3310;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
