<?php
session_start();
include 'db/conexion.php';

// Si ya tiene sesión, redirigir a la tienda
if (isset($_SESSION['usuario_id'])) {
    header('Location: Pages/tienda.php');
    exit;
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Completa todos los campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['rol']        = $usuario['rol'];

            header('Location: Pages/tienda.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POSYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: '#0a0e1a',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-sidebar min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">

        <!-- Logo -->
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="bg-blue-600 p-2.5 rounded-xl">
                <i data-lucide="store" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-blue-500 text-2xl font-bold">POSYSTEM</h1>
        </div>

        <!-- Card de login -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-1">Iniciar Sesión</h2>
            <p class="text-sm text-slate-400 mb-6">Ingresa tus credenciales para continuar</p>

            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                    <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Usuario</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-slate-50"
                        placeholder="Ingresa tu usuario" required autofocus>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Contraseña</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-slate-50"
                        placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-sm">
                    Iniciar Sesión
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">POSYSTEM &copy; <?= date('Y') ?></p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>