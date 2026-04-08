<?php
include '../db/auth.php';
include '../db/conexion.php';
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Crear Producto - K&Kream</title>
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
    <div class="flex">
        <aside class="flex flex-col bg-tertiary-dark w-64 h-screen px-5 py-6 justify-between fixed">
            <div>
                <div class="flex items-center gap-3 mb-10">
                    <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                </div>

                <nav>
                    <ul class="flex flex-col gap-2">
                        <li>
                            <a href="../Pages/tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Tienda
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
                                <i data-lucide="package" class="w-5 h-5"></i>
                                Inventario
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                Reportes
                            </a>
                        </li>
                        <li>
                            <a href="../Pages/ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
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

        <!-- Contenido principal -->
        <main class="flex-1 bg-neutral min-h-screen ml-64 p-8">

            <!-- Encabezado -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-heading font-bold text-tertiary-dark">Agregar Nuevo Producto</h2>
                    <p class="text-tertiary-light text-sm mt-1">Completa la información del producto</p>
                </div>
                <a href="../Pages/inventario.php" class="flex items-center gap-2 text-tertiary-light hover:text-tertiary transition-all duration-300">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Volver al Inventario
                </a>
            </div>

            <!-- Formulario -->
            <form action="guardar.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-primary/20 p-8">



                <!-- Columna derecha: Campos -->
                <div class="flex-1 flex flex-col gap-5">

                    <!-- Nombre del producto -->
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-tertiary-dark mb-2">
                            Nombre del Producto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre" placeholder="ej. Helado de Vainilla"
                            class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300"
                            required>
                    </div>

                    <!-- Categoria y SKU -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="categoria" class="block text-sm font-semibold text-tertiary-dark mb-2">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select id="categoria" name="categoria"
                                class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-light focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-white"
                                required>
                                <option value="" disabled selected>Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label for="sku" class="block text-sm font-semibold text-tertiary-dark mb-2">SKU</label>
                            <input type="text" id="sku" name="sku" placeholder="ej. HEL-001"
                                class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300">
                        </div>
                    </div>

                    <!-- Precio y Stock -->
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="precio" class="block text-sm font-semibold text-tertiary-dark mb-2">
                                Precio <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-tertiary-light text-sm font-semibold">$</span>
                                <input type="number" id="precio" name="precio" placeholder="0.00" step="0.01" min="0"
                                    class="w-full pl-8 pr-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300"
                                    required>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label for="stock" class="block text-sm font-semibold text-tertiary-dark mb-2">Stock Inicial</label>
                            <input type="number" id="stock" name="stock" placeholder="0" min="0"
                                class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300">
                        </div>

                    </div>
                    <div class="flex gap-8">

                        <!-- Columna izquierda: Imagen -->
                        <div>
                            <label for="imagen_url" class="block text-sm font-semibold text-tertiary-dark mb-2">
                                URL de Imagen
                            </label>
                            <input type="url" id="imagen_url" name="imagen_url"
                                placeholder="https://drive.google.com/..."
                                class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300">
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-primary/20">
                    <a href="../Pages/inventario.php" class="px-6 py-3 text-sm font-semibold text-tertiary-light hover:text-tertiary transition-all duration-300 rounded-lg hover:bg-primary/10">
                        Descartar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-tertiary text-white text-sm font-heading font-semibold rounded-lg hover:bg-tertiary-dark transition-all duration-300 shadow-sm">
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