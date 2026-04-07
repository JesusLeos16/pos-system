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

// Obtener productos
$stmt = $pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.id_categoria = c.id 
    WHERE p.stock > 0
    ORDER BY p.nombre ASC
");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para los filtros
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png">
    <title>Tienda - POSYSTEM</title>
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
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="flex flex-col bg-sidebar w-64 min-w-[16rem] h-screen px-5 py-6 justify-between">
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
                            <a href="tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-active text-blue-400 font-semibold transition-all duration-300">
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
                        <?= strtoupper(substr($_SESSION['nombre'], 0, 2)) ?>
                    </div>
                    <div>
                        <p class="text-white text-sm font-semibold"><?= htmlspecialchars($_SESSION['nombre']) ?></p>
                        <p class="text-slate-400 text-xs"><?= htmlspecialchars($_SESSION['rol']) ?></p>
                    </div>
                </div>
                <a href="../logout.php" class="flex items-center gap-3 text-slate-400 hover:text-slate-200 text-sm transition-all duration-300">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Cerrar Sesión
                </a>
            </div>
        </aside>

        <!-- Catálogo de productos -->
        <main class="flex-1 bg-slate-100 h-screen overflow-y-auto p-6">

            <!-- Barra de búsqueda -->
            <div class="relative mb-5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="buscar" placeholder="Buscar productos por nombre, categoría o SKU..."
                    class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-300">
            </div>

            <!-- Filtros de categoría -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
                <button onclick="filtrarCategoria('todos')" class="cat-btn active-cat px-4 py-2 rounded-full text-sm font-semibold bg-blue-600 text-white transition-all duration-300 whitespace-nowrap" data-cat="todos">
                    Todos
                </button>
                <?php foreach ($categorias as $cat): ?>
                    <button onclick="filtrarCategoria('<?= htmlspecialchars($cat['nombre']) ?>')" class="cat-btn px-4 py-2 rounded-full text-sm font-medium bg-white text-slate-600 border border-slate-200 hover:border-blue-400 hover:text-blue-600 transition-all duration-300 whitespace-nowrap" data-cat="<?= htmlspecialchars($cat['nombre']) ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Grid de productos -->
            <div id="productos-grid" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="product-card bg-white rounded-xl border border-slate-200 overflow-hidden cursor-pointer hover:shadow-md hover:border-blue-200 transition-all duration-300"
                        data-id="<?= $prod['id'] ?>"
                        data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                        data-precio="<?= $prod['precio'] ?>"
                        data-categoria="<?= htmlspecialchars($prod['categoria_nombre'] ?? '') ?>"
                        data-sku="<?= htmlspecialchars($prod['sku'] ?? '') ?>"
                        data-stock="<?= $prod['stock'] ?>"
                        onclick="agregarAlCarrito(this)">

                        <!-- Imagen -->
                        <div class="h-36 bg-slate-50 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= htmlspecialchars(driveDirectLink($prod['imagen'])) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex flex-col items-center gap-1 text-slate-300">
                                    <i data-lucide="image" class="w-10 h-10"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="p-3">
                            <h3 class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($prod['nombre']) ?></h3>
                            <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría') ?></p>
                            <p class="text-sm font-bold text-blue-600 mt-2">$<?= number_format($prod['precio'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($productos) === 0): ?>
                <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                    <i data-lucide="package-open" class="w-16 h-16 mb-4"></i>
                    <p class="text-lg font-medium">No hay productos disponibles</p>
                    <p class="text-sm mt-1">Agrega productos desde el inventario</p>
                </div>
            <?php endif; ?>
        </main>

        <!-- Panel de orden actual -->
        <aside class="w-80 min-w-[20rem] bg-white border-l border-slate-200 h-screen flex flex-col">

            <!-- Header de la orden -->
            <div class="flex items-center justify-between p-5 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Orden Actual</h2>
                    <p class="text-xs text-slate-400 mt-0.5" id="orden-info">
                        Orden #<?= rand(1000, 9999) ?> - <?= date('d/m/Y, H:i') ?>
                    </p>
                </div>
                <button onclick="limpiarCarrito()" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200" title="Limpiar orden">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Items del carrito -->
            <div id="carrito-items" class="flex-1 overflow-y-auto p-5">
                <!-- Estado vacío -->
                <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-slate-300">
                    <i data-lucide="shopping-bag" class="w-12 h-12 mb-3"></i>
                    <p class="text-sm font-medium">Carrito vacío</p>
                    <p class="text-xs mt-1">Selecciona productos para agregar</p>
                </div>
            </div>

            <!-- Footer: Totales y cobrar -->
            <div class="border-t border-slate-200">

                <!-- Totales -->
                <div class="px-5 pt-4 pb-2 space-y-2">
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Subtotal</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>IVA (16%)</span>
                        <span id="impuesto">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-slate-800 pt-2 border-t border-slate-100">
                        <span>Total</span>
                        <span id="total" class="text-blue-600">$0.00</span>
                    </div>
                </div>

                <!-- Botón cobrar -->
                <div class="p-5 pt-3">
                    <button onclick="cobrar()" id="btn-cobrar" class="w-full py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span>Cobrar</span>
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

        </aside>
    </div>

    <script>
        lucide.createIcons();

        // === CARRITO ===
        let carrito = [];

        function agregarAlCarrito(el) {
            const id = el.dataset.id;
            const nombre = el.dataset.nombre;
            const precio = parseFloat(el.dataset.precio);
            const stock = parseInt(el.dataset.stock);

            const existente = carrito.find(item => item.id === id);

            if (existente) {
                if (existente.cantidad < stock) {
                    existente.cantidad++;
                }
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1, stock });
            }

            renderCarrito();
        }

        function cambiarCantidad(id, delta) {
            const item = carrito.find(i => i.id === id);
            if (!item) return;

            item.cantidad += delta;

            if (item.cantidad <= 0) {
                carrito = carrito.filter(i => i.id !== id);
            } else if (item.cantidad > item.stock) {
                item.cantidad = item.stock;
            }

            renderCarrito();
        }

        function limpiarCarrito() {
            carrito = [];
            renderCarrito();
        }

        function renderCarrito() {
            const container = document.getElementById('carrito-items');
            const vacio = document.getElementById('carrito-vacio');
            const btnCobrar = document.getElementById('btn-cobrar');

            if (carrito.length === 0) {
                container.innerHTML = `
                    <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <p class="text-sm font-medium mt-3">Carrito vacío</p>
                        <p class="text-xs mt-1">Selecciona productos para agregar</p>
                    </div>`;
                btnCobrar.disabled = true;
            } else {
                let html = '<div class="space-y-3">';
                carrito.forEach(item => {
                    const subtotal = (item.precio * item.cantidad).toFixed(2);
                    const iniciales = item.nombre.substring(0, 2).toUpperCase();
                    html += `
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs flex-shrink-0">
                                ${iniciales}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">${item.nombre}</p>
                                <p class="text-xs text-slate-400">$${item.precio.toFixed(2)}</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button onclick="cambiarCantidad('${item.id}', -1)" class="w-7 h-7 rounded-md bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center text-sm font-bold transition-all">−</button>
                                <span class="w-7 text-center text-sm font-semibold text-slate-700">${item.cantidad}</span>
                                <button onclick="cambiarCantidad('${item.id}', 1)" class="w-7 h-7 rounded-md bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center text-sm font-bold transition-all">+</button>
                            </div>
                            <p class="text-sm font-bold text-slate-800 w-16 text-right">$${subtotal}</p>
                        </div>`;
                });
                html += '</div>';
                container.innerHTML = html;
                btnCobrar.disabled = false;
            }

            actualizarTotales();
        }

        function actualizarTotales() {
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const impuesto = subtotal * 0.16;
            const total = subtotal + impuesto;

            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('impuesto').textContent = '$' + impuesto.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
        }

        function cobrar() {
            if (carrito.length === 0) return;
            const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0) * 1.16;
            alert('Cobro realizado: $' + total.toFixed(2));
            limpiarCarrito();
        }

        // === BÚSQUEDA ===
        document.getElementById('buscar').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const nombre = card.dataset.nombre.toLowerCase();
                const categoria = card.dataset.categoria.toLowerCase();
                const sku = card.dataset.sku.toLowerCase();
                const match = nombre.includes(query) || categoria.includes(query) || sku.includes(query);
                card.style.display = match ? '' : 'none';
            });
        });

        // === FILTRO POR CATEGORÍA ===
        function filtrarCategoria(cat) {
            // Actualizar botón activo
            document.querySelectorAll('.cat-btn').forEach(btn => {
                if (btn.dataset.cat === cat) {
                    btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                    btn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
                }
            });

            // Filtrar cards
            document.querySelectorAll('.product-card').forEach(card => {
                if (cat === 'todos') {
                    card.style.display = '';
                } else {
                    card.style.display = card.dataset.categoria === cat ? '' : 'none';
                }
            });
        }
    </script>
</body>

</html>