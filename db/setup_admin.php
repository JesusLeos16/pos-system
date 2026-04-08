<?php
include 'conexion.php';

// Verificar si ya existe un admin
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "Ya existe un usuario administrador.";
    echo '<br><a href="../index.php">Ir al Login</a>';
    exit;
}

// Crear usuario admin
$username = 'Satevoyork';
$password = password_hash('PueblitoMagico', PASSWORD_DEFAULT);
$nombre   = 'Satevoyork';
$rol      = 'admin';

$stmt = $pdo->prepare("
    INSERT INTO usuarios (username, password, nombre, rol) 
    VALUES (:username, :password, :nombre, :rol)
");

$stmt->execute([
    ':username' => $username,
    ':password' => $password,
    ':nombre'   => $nombre,
    ':rol'      => $rol,
]);
