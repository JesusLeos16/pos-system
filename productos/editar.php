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

// Verificar que se recibió un ID
if (!isset($_GET['id'])) {
    header('Location: ../Pages/inventario.php');
    exit;
}

$id = intval($_GET['id']);

// Obtener producto
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header('Location: ../Pages/inventario.php');
    exit;
}

// Obtener categorías
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Editar Producto - K&Kream</title>
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
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="flex flex-col bg-tertiary-dark w-64 min-w-[16rem] h-screen px-5 py-6 justify-between">
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
        <main class="flex-1 bg-neutral h-screen overflow-y-auto">

            <!-- Barra superior -->
            <div class="sticky top-0 z-10 bg-white border-b border-primary/20 px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="../Pages/inventario.php" class="flex items-center gap-2 text-tertiary hover:text-tertiary-dark text-sm font-medium transition-all duration-300">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Volver al Inventario
                    </a>
                    <span class="text-primary">|</span>
                    <h2 class="text-sm font-heading font-bold text-tertiary-dark">Editar Producto</h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../Pages/inventario.php" class="px-4 py-2 text-sm font-medium text-tertiary-light hover:text-tertiary transition-all duration-300">
                        Descartar
                    </a>
                    <button type="submit" form="form-editar" class="px-5 py-2 bg-tertiary text-white text-sm font-heading font-semibold rounded-lg hover:bg-tertiary-dark transition-all duration-300 shadow-sm">
                        Guardar Cambios
                    </button>
                </div>
            </div>

            <div class="p-8">

                <!-- Encabezado del producto -->
                <div class="mb-8">
                    <h1 class="text-2xl font-heading font-bold text-tertiary-dark"><?= htmlspecialchars($producto['nombre']) ?></h1>
                    <p class="text-tertiary-light text-sm mt-1">
                        ID: <?= $producto['id'] ?>
                        <?php if ($producto['sku']): ?>
                            • SKU: <?= htmlspecialchars($producto['sku']) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <form id="form-editar" action="actualizar.php" method="POST" class="flex gap-6">

                    <!-- Columna izquierda -->
                    <div class="flex-1 flex flex-col gap-6">

                        <!-- Sección 01: Información General -->
                        <div class="bg-white rounded-xl border border-primary/20 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <span class="text-xs font-heading font-bold text-tertiary uppercase tracking-wider">Sección 01</span>
                                <h3 class="text-lg font-heading font-bold text-tertiary-dark">Información General</h3>
                            </div>

                            <input type="hidden" name="id" value="<?= $producto['id'] ?>">

                            <div class="mb-4">
                                <label for="nombre" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Nombre del Producto</label>
                                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>"
                                    class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5"
                                    required>
                            </div>
                        </div>

                        <!-- Secciones 02 y 03 en fila -->
                        <div class="flex gap-6">

                            <!-- Sección 02: Precio y Categoría -->
                            <div class="flex-1 bg-white rounded-xl border border-primary/20 p-6">
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="text-xs font-heading font-bold text-tertiary uppercase tracking-wider">Sección 02</span>
                                    <h3 class="text-lg font-heading font-bold text-tertiary-dark">Precio y Categoría</h3>
                                </div>

                                <div class="mb-4">
                                    <label for="precio" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Precio</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-tertiary-light text-sm font-semibold">$</span>
                                        <input type="number" id="precio" name="precio" value="<?= $producto['precio'] ?>" step="0.01" min="0"
                                            class="w-full pl-8 pr-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="id_categoria" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Categoría</label>
                                    <select id="id_categoria" name="id_categoria"
                                        class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5">
                                        <option value="">Sin categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($producto['id_categoria'] == $cat['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label for="sku" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">SKU</label>
                                    <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($producto['sku'] ?? '') ?>"
                                        class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5">
                                </div>
                            </div>

                            <!-- Sección 03: Stock -->
                            <div class="flex-1 bg-white rounded-xl border border-primary/20 p-6">
                                <div class="flex items-center gap-3 mb-5">
                                    <span class="text-xs font-heading font-bold text-tertiary uppercase tracking-wider">Sección 03</span>
                                    <h3 class="text-lg font-heading font-bold text-tertiary-dark">Stock</h3>
                                </div>

                                <div class="mb-4">
                                    <label for="stock" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Stock Actual</label>
                                    <input type="number" id="stock" name="stock" value="<?= $producto['stock'] ?>" min="0"
                                        class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5">
                                </div>

                                <div class="mb-4">
                                    <label for="stock_minimo" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Stock Mínimo</label>
                                    <input type="number" id="stock_minimo" name="stock_minimo" value="<?= $producto['stock_minimo'] ?>" min="0"
                                        class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5">
                                </div>

                                <!-- Estado del inventario -->
                                <?php
                                $stockStatus = 'Saludable';
                                $stockColor = 'green';
                                $stockIcon = 'check-circle';
                                if ($producto['stock'] <= 0) {
                                    $stockStatus = 'Sin stock';
                                    $stockColor = 'red';
                                    $stockIcon = 'alert-circle';
                                } elseif ($producto['stock'] <= $producto['stock_minimo']) {
                                    $stockStatus = 'Stock bajo';
                                    $stockColor = 'yellow';
                                    $stockIcon = 'alert-triangle';
                                }
                                ?>
                                <div class="p-4 bg-<?= $stockColor ?>-50 border border-<?= $stockColor ?>-200 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="<?= $stockIcon ?>" class="w-4 h-4 text-<?= $stockColor ?>-600"></i>
                                        <p class="text-sm font-semibold text-<?= $stockColor ?>-700">Estado del Inventario: <?= $stockStatus ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Imagen -->
                    <div class="w-80">
                        <div class="bg-white rounded-xl border border-primary/20 p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <span class="text-xs font-heading font-bold text-tertiary uppercase tracking-wider">Sección 04</span>
                                <h3 class="text-lg font-heading font-bold text-tertiary-dark">Imagen</h3>
                            </div>

                            <!-- Vista previa de imagen -->
                            <div class="w-full h-52 bg-primary/10 rounded-xl border border-primary/20 overflow-hidden flex items-center justify-center mb-4">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?= htmlspecialchars(driveDirectLink($producto['imagen'])) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-full h-full object-cover" id="preview-img">
                                <?php else: ?>
                                    <div class="flex flex-col items-center gap-2 text-primary" id="img-placeholder">
                                        <i data-lucide="ice-cream-cone" class="w-12 h-12"></i>
                                        <p class="text-xs text-tertiary-light">Sin imagen</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="imagen_url" class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">URL de Imagen</label>
                                <input type="url" id="imagen_url" name="imagen_url" value="<?= htmlspecialchars($producto['imagen'] ?? '') ?>"
                                    placeholder="https://drive.google.com/..."
                                    class="w-full px-4 py-3 border border-primary/30 rounded-lg text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300 bg-primary/5">
                            </div>
                        </div>

                        <!-- Zona de peligro -->
                        <div class="bg-white rounded-xl border border-red-200 p-6 mt-6">
                            <h3 class="text-sm font-heading font-bold text-red-600 mb-3">Zona de Peligro</h3>
                            <p class="text-xs text-tertiary-light mb-4">Esta acción no se puede deshacer. El producto será eliminado permanentemente.</p>
                            <a href="eliminar.php?id=<?= $producto['id'] ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')"
                                class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-50 text-red-600 text-sm font-semibold rounded-lg hover:bg-red-100 border border-red-200 transition-all duration-300">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Eliminar Producto
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
