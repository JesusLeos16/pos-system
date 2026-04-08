<?php include '../db/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes - K&Kream</title>
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
    <header>
        <div class="flex">
            <aside class="flex flex-col bg-tertiary-dark w-64 h-screen px-5 py-6 justify-between">

                <div>
                    <div class="flex items-center gap-3 mb-10">
                        <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                    </div>

                    <nav>
                        <ul class="flex flex-col gap-2">
                            <li>
                                <a href="tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    Tienda
                                </a>
                            </li>
                            <li>
                                <a href="inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                    <i data-lucide="package" class="w-5 h-5"></i>
                                    Inventario
                                </a>
                            </li>
                            <li>
                                <a href="reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                    Reportes
                                </a>
                            </li>
                            <li>
                                <a href="ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
                                    <i data-lucide="settings" class="w-5 h-5"></i>
                                    Ajustes
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-tertiary flex items-center justify-center text-primary font-bold text-sm">
                            <?= strtoupper(substr($_SESSION['nombre'], 0, 2)) ?>
                        </div>
                        <div>
                            <p class="text-primary text-sm font-semibold"><?= htmlspecialchars($_SESSION['nombre']) ?></p>
                            <p class="text-primary/50 text-xs"><?= htmlspecialchars($_SESSION['rol']) ?></p>
                        </div>
                    </div>
                    <a href="../logout.php" class="flex items-center gap-3 text-primary/50 hover:text-primary text-sm transition-all duration-300">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Cerrar Sesión
                    </a>
                </div>

            </aside>

            <main class="flex-1 bg-neutral h-screen flex items-center justify-center">
                <div class="text-center text-tertiary-light">
                    <i data-lucide="hard-hat" class="w-16 h-16 mx-auto mb-4"></i>
                    <h2 class="text-xl font-heading font-semibold text-tertiary">Página en construcción</h2>
                    <p class="text-sm mt-2">Esta sección estará disponible próximamente.</p>
                </div>
            </main>

        </div>

        <script>
            lucide.createIcons();
        </script>
</body>

</html>