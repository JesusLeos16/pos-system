<?php
include '../db/auth.php';
include '../db/conexion.php';

// Convierte link de Google Drive a link directo de imagen
function driveDirectLink($url) {
    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://lh3.googleusercontent.com/d/' . $matches[1];
    }
    return $url;
}

// Consultar productos con su categoría
$stmt = $pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.id_categoria = c.id 
    ORDER BY p.id DESC
");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - K&Kream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png?v=3">
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
    <style>
        #sidebar-overlay {
            transition: opacity 0.3s ease;
        }
        #sidebar-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        #sidebar-mobile {
            transition: transform 0.3s ease;
        }
        #sidebar-mobile.sidebar-closed {
            transform: translateX(-100%);
        }
    </style>
</head>

<body class="font-body">
    <div class="flex">

        <!-- Sidebar overlay (mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar-mobile" class="flex flex-col bg-tertiary-dark w-64 h-screen px-5 py-6 justify-between fixed z-50 sidebar-closed lg:!transform-none">
            <div>
                <div class="flex items-center justify-between gap-3 mb-10">
                    <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-24">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-primary/70 hover:text-primary rounded-lg transition-all duration-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
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
                            <a href="inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
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
                            <a href="ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
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
        <main class="flex-1 bg-neutral min-h-screen lg:ml-64 p-4 sm:p-6 lg:p-8">

            <!-- Mobile top bar -->
            <div class="flex items-center justify-between mb-4 lg:hidden">
                <button onclick="toggleSidebar()" class="p-2 text-tertiary-dark hover:bg-primary/20 rounded-lg transition-all duration-300">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                <div class="w-10"></div>
            </div>

            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-heading font-bold text-tertiary-dark">Inventario</h2>
                    <p class="text-tertiary-light text-sm mt-1">Administra tus productos, categorías y stock.</p>
                </div>
                <a href="../productos/crear.php" class="flex items-center justify-center gap-2 px-5 py-3 bg-tertiary text-white text-sm font-heading font-semibold rounded-lg hover:bg-tertiary-dark transition-all duration-300 shadow-sm w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Crear Producto
                </a>
            </div>

            <!-- Tabla contenedor -->
            <div class="bg-white rounded-xl shadow-sm border border-primary/20">

                <!-- Barra de búsqueda -->
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-primary/20">
                    <div class="relative w-full sm:w-96">
                        <i data-lucide="search" class="w-4 h-4 text-tertiary-light absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="buscar" placeholder="Buscar por nombre, SKU o categoría..."
                            class="w-full pl-10 pr-4 py-2.5 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300">
                    </div>
                </div>

                <!-- Vista de tabla (desktop) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-primary/20">
                                <th class="text-left px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">Producto</th>
                                <th class="text-left px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">SKU</th>
                                <th class="text-left px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">Categoría</th>
                                <th class="text-right px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">Precio</th>
                                <th class="text-center px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">Stock</th>
                                <th class="text-center px-6 py-4 text-xs font-heading font-semibold text-tertiary-light uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($productos) > 0): ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr class="border-b border-primary/10 hover:bg-primary/5 transition-all duration-200 product-row">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center text-tertiary font-bold text-xs">
                                                    <?= strtoupper(substr($prod['nombre'], 0, 2)) ?>
                                                </div>
                                                <span class="text-sm font-medium text-tertiary-dark"><?= htmlspecialchars($prod['nombre']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-tertiary-light"><?= htmlspecialchars($prod['sku'] ?: '—') ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($prod['categoria_nombre']): ?>
                                                <span class="inline-block px-3 py-1 bg-primary/15 text-tertiary text-xs font-medium rounded-full">
                                                    <?= htmlspecialchars($prod['categoria_nombre']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-sm text-tertiary-light/50">Sin categoría</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-tertiary-dark">$<?= number_format($prod['precio'], 2) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <?php
                                            $stockClass = 'bg-green-100 text-green-700';
                                            if ($prod['stock'] <= 0) {
                                                $stockClass = 'bg-red-100 text-red-700';
                                            } elseif ($prod['stock'] <= $prod['stock_minimo']) {
                                                $stockClass = 'bg-yellow-100 text-yellow-700';
                                            }
                                            ?>
                                            <span class="inline-block px-3 py-1 <?= $stockClass ?> text-xs font-semibold rounded-full">
                                                <?= $prod['stock'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="../productos/editar.php?id=<?= $prod['id'] ?>" class="p-2 text-tertiary-light hover:text-tertiary hover:bg-primary/20 rounded-lg transition-all duration-200" title="Editar">
                                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                                </a>
                                                <a href="../productos/eliminar.php?id=<?= $prod['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')" class="p-2 text-tertiary-light hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200" title="Eliminar">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <i data-lucide="ice-cream-cone" class="w-12 h-12 text-primary"></i>
                                            <p class="text-tertiary-light text-sm">No hay productos registrados</p>
                                            <a href="../productos/crear.php" class="text-tertiary text-sm font-semibold hover:underline">Agregar tu primer producto</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Vista de cards (mobile) -->
                <div class="md:hidden divide-y divide-primary/10">
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $prod): ?>
                            <div class="p-4 product-row-mobile">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center text-tertiary font-bold text-xs flex-shrink-0">
                                            <?= strtoupper(substr($prod['nombre'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-tertiary-dark"><?= htmlspecialchars($prod['nombre']) ?></p>
                                            <p class="text-xs text-tertiary-light"><?= htmlspecialchars($prod['sku'] ?: '—') ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="../productos/editar.php?id=<?= $prod['id'] ?>" class="p-2 text-tertiary-light hover:text-tertiary hover:bg-primary/20 rounded-lg transition-all duration-200">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <a href="../productos/eliminar.php?id=<?= $prod['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')" class="p-2 text-tertiary-light hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <?php if ($prod['categoria_nombre']): ?>
                                            <span class="inline-block px-2.5 py-0.5 bg-primary/15 text-tertiary text-xs font-medium rounded-full">
                                                <?= htmlspecialchars($prod['categoria_nombre']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php
                                        $stockClass = 'bg-green-100 text-green-700';
                                        if ($prod['stock'] <= 0) {
                                            $stockClass = 'bg-red-100 text-red-700';
                                        } elseif ($prod['stock'] <= $prod['stock_minimo']) {
                                            $stockClass = 'bg-yellow-100 text-yellow-700';
                                        }
                                        ?>
                                        <span class="inline-block px-2.5 py-0.5 <?= $stockClass ?> text-xs font-semibold rounded-full">
                                            Stock: <?= $prod['stock'] ?>
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-tertiary-dark">$<?= number_format($prod['precio'], 2) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="ice-cream-cone" class="w-12 h-12 text-primary"></i>
                                <p class="text-tertiary-light text-sm">No hay productos registrados</p>
                                <a href="../productos/crear.php" class="text-tertiary text-sm font-semibold hover:underline">Agregar tu primer producto</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer de la tabla -->
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-primary/20">
                    <p class="text-sm text-tertiary-light">
                        Mostrando <span class="font-semibold"><?= count($productos) ?></span> producto(s)
                    </p>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }

        // Búsqueda en tiempo real
        document.getElementById('buscar').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            // Desktop table rows
            document.querySelectorAll('.product-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
            // Mobile card rows
            document.querySelectorAll('.product-row-mobile').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>

</html>