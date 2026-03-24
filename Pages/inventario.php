<?php
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
    <title>Inventario - POSYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
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
        <!-- Sidebar -->
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
                            <a href="tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                Tienda
                            </a>
                        </li>
                        <li>
                            <a href="inventario.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-active text-blue-400 font-semibold transition-all duration-300">
                                <i data-lucide="package" class="w-5 h-5"></i>
                                Inventario
                            </a>
                        </li>
                        <li>
                            <a href="reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-all duration-300">
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

        <!-- Contenido principal -->
        <main class="flex-1 bg-slate-100 min-h-screen ml-64 p-8">

            <!-- Encabezado -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Inventario</h2>
                    <p class="text-slate-500 text-sm mt-1">Administra tus productos, categorías y stock.</p>
                </div>
                <a href="../productos/crear.php" class="flex items-center gap-2 px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-all duration-300 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Crear Producto
                </a>
            </div>

            <!-- Tabla contenedor -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">

                <!-- Barra de búsqueda -->
                <div class="flex items-center justify-between p-5 border-b border-slate-200">
                    <div class="relative w-96">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" id="buscar" placeholder="Buscar por nombre, SKU o categoría..."
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</th>
                                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio</th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock</th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($productos) > 0): ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-all duration-200 product-row">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                                    <?= strtoupper(substr($prod['nombre'], 0, 2)) ?>
                                                </div>
                                                <span class="text-sm font-medium text-slate-800"><?= htmlspecialchars($prod['nombre']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($prod['sku'] ?: '—') ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($prod['categoria_nombre']): ?>
                                                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded-full">
                                                    <?= htmlspecialchars($prod['categoria_nombre']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-sm text-slate-400">Sin categoría</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-slate-800">$<?= number_format($prod['precio'], 2) ?></td>
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
                                                <a href="../productos/editar.php?id=<?= $prod['id'] ?>" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200" title="Editar">
                                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                                </a>
                                                <a href="../productos/eliminar.php?id=<?= $prod['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este producto?')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200" title="Eliminar">
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
                                            <i data-lucide="package-open" class="w-12 h-12 text-slate-300"></i>
                                            <p class="text-slate-500 text-sm">No hay productos registrados</p>
                                            <a href="../productos/crear.php" class="text-blue-600 text-sm font-semibold hover:underline">Agregar tu primer producto</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer de la tabla -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200">
                    <p class="text-sm text-slate-500">
                        Mostrando <span class="font-semibold"><?= count($productos) ?></span> producto(s)
                    </p>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Búsqueda en tiempo real
        document.getElementById('buscar').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.product-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    </script>
</body>

</html>