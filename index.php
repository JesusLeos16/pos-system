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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - K&Kream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#F4C2C2',
                        secondary: '#98FF98',
                        tertiary: '#6F4E37',
                        neutral: '#FDF5E6',
                        'tertiary-dark': '#5a3d2b',
                        'tertiary-light': '#8B6F5C',
                    },
                    fontFamily: {
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                        body: ['"Be Vietnam Pro"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="font-body">
    <div class="flex h-screen">

        <!-- Lado izquierdo: Imagen hero -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img src="/pos-system/src/ice_cream_hero.png" alt="Helado artesanal" class="w-full h-full object-cover">

            <!-- Overlay gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>


        </div>

        <!-- Lado derecho: Formulario -->
        <div class="flex-1 bg-neutral flex items-center justify-center px-8">
            <div class="w-full max-w-md">

                <!-- Título -->
                <div class="mb-8">
                    <h1 class="font-heading text-4xl font-extrabold text-tertiary-dark leading-tight">
                        Bienvenido al<br>punto dulce
                    </h1>
                    <p class="text-tertiary-light font-body text-sm mt-3">
                        Ingresa tus credenciales para acceder al panel de administración.
                    </p>
                </div>

                <!-- Error -->
                <?php if ($error): ?>
                    <div class="mb-5 p-3.5 bg-primary/30 border border-primary rounded-xl flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-tertiary flex-shrink-0"></i>
                        <p class="text-sm text-tertiary-dark font-medium"><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="index.php" method="POST" class="space-y-5">

                    <!-- Usuario -->
                    <div>
                        <label for="username" class="block text-xs font-semibold text-tertiary uppercase tracking-wider mb-2 font-body">Usuario</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-tertiary-light absolute left-4 top-1/2 -translate-y-1/2"></i>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                class="w-full pl-11 pr-4 py-3.5 bg-primary/15 border border-primary/30 rounded-xl text-sm text-tertiary-dark placeholder-tertiary-light/50 focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 font-body"
                                placeholder="nombre de usuario" required autofocus>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-tertiary uppercase tracking-wider mb-2 font-body">Contraseña</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-tertiary-light absolute left-4 top-1/2 -translate-y-1/2"></i>
                            <input type="password" id="password" name="password"
                                class="w-full pl-11 pr-12 py-3.5 bg-primary/15 border border-primary/30 rounded-xl text-sm text-tertiary-dark placeholder-tertiary-light/50 focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 font-body"
                                placeholder="••••••••" required>
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-tertiary-light hover:text-tertiary transition-all duration-300">
                                <i data-lucide="eye" class="w-4 h-4" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botón -->
                    <button type="submit" class="w-full py-3.5 bg-tertiary text-white font-heading font-semibold rounded-xl hover:bg-tertiary-dark transition-all duration-300 shadow-sm flex items-center justify-center gap-2 mt-2">
                        Iniciar Sesión
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-tertiary-light/50 text-xs font-body">&copy; <?= date('Y') ?> K&amp;Kream &bull; Hecho con amor</p>
                    <a href="https://eleosoft.dev" target="_blank" class="text-tertiary-light/40 text-[10px] font-body hover:text-tertiary-light transition-all duration-300 mt-1 inline-block">Desarrollado por eleosoft.dev</a>
                </div>
            </div>

        </div>
    </div>
    </div>

    <script>
        lucide.createIcons();

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>

</html>