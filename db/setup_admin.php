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
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$nombre   = 'Administrador';
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

echo "✅ Usuario admin creado exitosamente.<br>";
echo "Usuario: <strong>admin</strong><br>";
echo "Contraseña: <strong>admin123</strong><br>";
echo "<br><strong>⚠️ Cambia la contraseña después de iniciar sesión.</strong>";
echo '<br><br><a href="../index.php">Ir al Login</a>';
