<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/x-icon" href="assets/img/pos-system.png">
    <title>POSYSTEM</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidebar: '#0a0e1a',
                        active: '#2d2b7c',
                    }
                }
            }
        }
    </script>
</head>

<body>
    <header>
        <div class="flex">
            <aside class="flex flex-col bg-sidebar w-64 h-screen px-5 py-6 justify-between">

                <div>
                    <div class="flex items-center gap-3 mb-10">
                        <div class="bg-blue-600 p-2 rounded-lg">
                            <i data-lucide="store" class="w-6 h-6 text-white"></i>
                        </div>
                        <h1 class="text-blue-500 text-xl font-bold">POSYSTEM</h1>
                    </div>

                    <nav>
                        <ul class="flex flex-col gap-2">
                            <li>
                                <a href="tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    Tienda
                                </a>
                            </li>
                            <li>
                                <a href="inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                    <i data-lucide="package" class="w-5 h-5"></i>
                                    Inventario
                                </a>
                            </li>
                            <li>
                                <a href="reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-active text-blue-400 font-semibold transition-all duration-300">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                    Reportes
                                </a>
                            </li>
                            <li>
                                <a href="ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                    <i data-lucide="settings" class="w-5 h-5"></i>
                                    Ajustes
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-700 flex items-center justify-center text-white font-bold text-sm">
                            YO
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">Usuario</p>
                            <p class="text-slate-400 text-xs">Administrador</p>
                        </div>
                    </div>
                    <a href="../index.php" class="flex items-center gap-3 text-slate-400 hover:text-slate-200 text-sm transition-all duration-300">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Cerrar Sesión
                    </a>
                </div>

            </aside>


        </div>

        <script>
            lucide.createIcons();
        </script>
    </header>
</body>

</html>