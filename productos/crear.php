<?php
include '../db/conexion.php';
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
    <title>Crear Producto - POSYSTEM</title>
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
    <div class="flex">
        <aside class="flex flex-col bg-sidebar w-64 h-screen px-5 py-6 justify-between fixed">
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
                            <a href="../Pages/tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Tienda
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-active text-blue-400 font-semibold transition-all duration-300">
                                <i data-lucide="package" class="w-5 h-5"></i>
                                Inventario
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                Reportes
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
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

        <!-- Contenido principal -->
        <main class="flex-1 bg-slate-100 min-h-screen ml-64 p-8">

            <!-- Encabezado -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Agregar Nuevo Producto</h2>
                    <p class="text-slate-500 text-sm mt-1">Completa la información del producto</p>
                </div>
                <a href="../Pages/inventario.php" class="flex items-center gap-2 text-slate-500 hover:text-slate-700 transition-all duration-300">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Volver al Inventario
                </a>
            </div>

            <!-- Formulario -->
            <form action="guardar.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">



                <!-- Columna derecha: Campos -->
                <div class="flex-1 flex flex-col gap-5">

                    <!-- Nombre del producto -->
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-2">
                            Nombre del Producto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre" placeholder="ej. Café Arábica en Grano"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                            required>
                    </div>

                    <!-- Categoria y SKU -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="categoria" class="block text-sm font-semibold text-slate-700 mb-2">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select id="categoria" name="categoria"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white"
                                required>
                                <option value="" disabled selected>Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label for="sku" class="block text-sm font-semibold text-slate-700 mb-2">SKU</label>
                            <input type="text" id="sku" name="sku" placeholder="ej. BEB-001"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        </div>
                    </div>

                    <!-- Precio y Stock -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="precio" class="block text-sm font-semibold text-slate-700 mb-2">
                                Precio <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-semibold">$</span>
                                <input type="number" id="precio" name="precio" placeholder="0.00" step="0.01" min="0"
                                    class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                    required>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label for="stock" class="block text-sm font-semibold text-slate-700 mb-2">Stock Inicial</label>
                            <input type="number" id="stock" name="stock" placeholder="0" min="0"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        </div>

                    </div>
                    <div class="flex gap-8">

                        <!-- Columna izquierda: Imagen -->
                        <div>
                            <label for="imagen_url" class="block text-sm font-semibold text-slate-700 mb-2">
                                URL de Imagen
                            </label>
                            <input type="url" id="imagen_url" name="imagen_url"
                                placeholder="https://drive.google.com/..."
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-slate-200">
                    <a href="../Pages/inventario.php" class="px-6 py-3 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-all duration-300 rounded-lg hover:bg-slate-100">
                        Descartar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-sm">
                        Guardar Producto
                    </button>
                </div>

            </form>

        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>