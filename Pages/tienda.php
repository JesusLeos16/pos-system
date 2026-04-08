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
    <link rel="icon" type="image/png" href="/pos-system/src/favicon.png?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Tienda - K&Kream</title>
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
        /* Sidebar overlay */
        #sidebar-overlay {
            transition: opacity 0.3s ease;
        }
        #sidebar-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        /* Sidebar mobile slide */
        #sidebar-mobile {
            transition: transform 0.3s ease;
        }
        #sidebar-mobile.sidebar-closed {
            transform: translateX(-100%);
        }
        /* Cart panel mobile slide */
        #cart-panel {
            transition: transform 0.3s ease;
        }
        #cart-panel.cart-closed {
            transform: translateY(100%);
        }
        /* Cart overlay */
        #cart-overlay {
            transition: opacity 0.3s ease;
        }
        #cart-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>

<body class="font-body">
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar overlay (mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar-mobile" class="flex flex-col bg-tertiary-dark w-64 min-w-[16rem] h-screen px-5 py-6 justify-between fixed lg:relative z-50 sidebar-closed lg:!transform-none">
            <div>
                <div class="flex items-center justify-between gap-3 mb-10">
                    <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-24">
                    <!-- Close button (mobile) -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-primary/70 hover:text-primary rounded-lg transition-all duration-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <nav>
                    <ul class="flex flex-col gap-2">
                        <li>
                            <a href="tienda.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
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

        <!-- Catálogo de productos -->
        <main class="flex-1 bg-neutral h-screen overflow-y-auto p-4 sm:p-6 pb-20 lg:pb-6">

            <!-- Mobile top bar -->
            <div class="flex items-center justify-between mb-4 lg:hidden">
                <button onclick="toggleSidebar()" class="p-2 text-tertiary-dark hover:bg-primary/20 rounded-lg transition-all duration-300">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                <button onclick="toggleCart()" class="p-2 text-tertiary-dark hover:bg-primary/20 rounded-lg transition-all duration-300 relative">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                    <span id="cart-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-tertiary text-white text-xs font-bold rounded-full flex items-center justify-center hidden">0</span>
                </button>
            </div>

            <!-- Barra de búsqueda -->
            <div class="relative mb-5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="buscar" placeholder="Buscar productos por nombre, categoría o SKU..."
                    class="w-full pl-11 pr-4 py-3 bg-white border border-primary/30 rounded-xl text-sm text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 shadow-sm transition-all duration-300">
            </div>

            <!-- Filtros de categoría -->
            <div class="flex gap-2 mb-6 overflow-x-auto pb-1 scrollbar-hide">
                <button onclick="filtrarCategoria('todos')" class="cat-btn active-cat px-4 py-2 rounded-full text-sm font-heading font-semibold bg-tertiary text-white transition-all duration-300 whitespace-nowrap" data-cat="todos">
                    Todos
                </button>
                <?php foreach ($categorias as $cat): ?>
                    <button onclick="filtrarCategoria('<?= htmlspecialchars($cat['nombre']) ?>')" class="cat-btn px-4 py-2 rounded-full text-sm font-heading font-medium bg-white text-tertiary-light border border-primary/30 hover:border-tertiary/40 hover:text-tertiary transition-all duration-300 whitespace-nowrap" data-cat="<?= htmlspecialchars($cat['nombre']) ?>">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Grid de productos -->
            <div id="productos-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="product-card bg-white rounded-xl border border-primary/20 overflow-hidden cursor-pointer hover:shadow-md hover:border-tertiary/30 transition-all duration-300"
                        data-id="<?= $prod['id'] ?>"
                        data-nombre="<?= htmlspecialchars($prod['nombre']) ?>"
                        data-precio="<?= $prod['precio'] ?>"
                        data-categoria="<?= htmlspecialchars($prod['categoria_nombre'] ?? '') ?>"
                        data-sku="<?= htmlspecialchars($prod['sku'] ?? '') ?>"
                        data-stock="<?= $prod['stock'] ?>"
                        onclick="agregarAlCarrito(this)">

                        <!-- Imagen -->
                        <div class="h-28 sm:h-36 bg-primary/10 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= htmlspecialchars(driveDirectLink($prod['imagen'])) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex flex-col items-center gap-1 text-primary">
                                    <i data-lucide="ice-cream-cone" class="w-8 h-8 sm:w-10 sm:h-10"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="p-2.5 sm:p-3">
                            <h3 class="text-xs sm:text-sm font-heading font-semibold text-tertiary-dark truncate"><?= htmlspecialchars($prod['nombre']) ?></h3>
                            <p class="text-[10px] sm:text-xs text-tertiary-light mt-0.5"><?= htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría') ?></p>
                            <p class="text-sm font-bold text-tertiary mt-1.5 sm:mt-2">$<?= number_format($prod['precio'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($productos) === 0): ?>
                <div class="flex flex-col items-center justify-center py-20 text-tertiary-light">
                    <i data-lucide="ice-cream-cone" class="w-16 h-16 mb-4"></i>
                    <p class="text-lg font-heading font-medium">No hay productos disponibles</p>
                    <p class="text-sm mt-1">Agrega productos desde el inventario</p>
                </div>
            <?php endif; ?>
        </main>

        <!-- Cart overlay (mobile) -->
        <div id="cart-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleCart()"></div>

        <!-- Panel de orden actual -->
        <aside id="cart-panel" class="w-full sm:w-96 lg:w-80 lg:min-w-[20rem] bg-white border-l border-primary/20 h-screen flex flex-col fixed lg:relative bottom-0 left-0 right-0 z-50 cart-closed lg:!transform-none rounded-t-2xl lg:rounded-none shadow-2xl lg:shadow-none max-h-[85vh] lg:max-h-none">

            <!-- Header de la orden -->
            <div class="flex items-center justify-between p-5 border-b border-primary/20">
                <div>
                    <h2 class="text-lg font-heading font-bold text-tertiary-dark">Orden Actual</h2>
                    <p class="text-xs text-tertiary-light mt-0.5" id="orden-info">
                        Orden #<?= rand(1000, 9999) ?> - <?= date('d/m/Y, H:i') ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="limpiarCarrito()" class="p-2 text-tertiary-light hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200" title="Limpiar orden">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                    <!-- Close button (mobile) -->
                    <button onclick="toggleCart()" class="p-2 text-tertiary-light hover:text-tertiary rounded-lg transition-all duration-200 lg:hidden" title="Cerrar">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Items del carrito -->
            <div id="carrito-items" class="flex-1 overflow-y-auto p-5">
                <!-- Estado vacío -->
                <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-primary">
                    <i data-lucide="shopping-bag" class="w-12 h-12 mb-3"></i>
                    <p class="text-sm font-medium text-tertiary-light">Carrito vacío</p>
                    <p class="text-xs mt-1 text-tertiary-light/60">Selecciona productos para agregar</p>
                </div>
            </div>

            <!-- Footer: Totales y cobrar -->
            <div class="border-t border-primary/20">

                <!-- Totales -->
                <div class="px-5 pt-4 pb-2 space-y-2">
                    <div class="flex justify-between text-sm text-tertiary-light">
                        <span>Subtotal</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm text-tertiary-light">
                        <span>IVA (16%)</span>
                        <span id="impuesto">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-heading font-bold text-tertiary-dark pt-2 border-t border-primary/20">
                        <span>Total</span>
                        <span id="total" class="text-tertiary">$0.00</span>
                    </div>
                </div>

                <!-- Botón cobrar -->
                <div class="p-5 pt-3 pb-safe">
                    <button onclick="cobrar()" id="btn-cobrar" class="w-full py-3.5 bg-tertiary text-white font-heading font-semibold rounded-xl hover:bg-tertiary-dark transition-all duration-300 shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span>Cobrar</span>
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

        </aside>
    </div>

    <script>
        lucide.createIcons();

        // === SIDEBAR TOGGLE (mobile) ===
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }

        // === CART TOGGLE (mobile) ===
        function toggleCart() {
            const cart = document.getElementById('cart-panel');
            const overlay = document.getElementById('cart-overlay');
            cart.classList.toggle('cart-closed');
            overlay.classList.toggle('hidden');
        }

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
            updateCartBadge();
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
            updateCartBadge();
        }

        function limpiarCarrito() {
            carrito = [];
            renderCarrito();
            updateCartBadge();
        }

        function updateCartBadge() {
            const badge = document.getElementById('cart-badge');
            const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            if (totalItems > 0) {
                badge.textContent = totalItems;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function renderCarrito() {
            const container = document.getElementById('carrito-items');
            const btnCobrar = document.getElementById('btn-cobrar');

            if (carrito.length === 0) {
                container.innerHTML = `
                    <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <p class="text-sm font-medium mt-3 text-tertiary-light">Carrito vacío</p>
                        <p class="text-xs mt-1 text-tertiary-light/60">Selecciona productos para agregar</p>
                    </div>`;
                btnCobrar.disabled = true;
            } else {
                let html = '<div class="space-y-3">';
                carrito.forEach(item => {
                    const subtotal = (item.precio * item.cantidad).toFixed(2);
                    const iniciales = item.nombre.substring(0, 2).toUpperCase();
                    html += `
                        <div class="flex items-center gap-3 p-3 bg-primary/10 rounded-xl">
                            <div class="w-10 h-10 rounded-lg bg-primary/30 flex items-center justify-center text-tertiary font-bold text-xs flex-shrink-0">
                                ${iniciales}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-tertiary-dark truncate">${item.nombre}</p>
                                <p class="text-xs text-tertiary-light">$${item.precio.toFixed(2)}</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button onclick="cambiarCantidad('${item.id}', -1)" class="w-7 h-7 rounded-md bg-white border border-primary/30 text-tertiary-light hover:bg-primary/10 flex items-center justify-center text-sm font-bold transition-all">−</button>
                                <span class="w-7 text-center text-sm font-semibold text-tertiary-dark">${item.cantidad}</span>
                                <button onclick="cambiarCantidad('${item.id}', 1)" class="w-7 h-7 rounded-md bg-white border border-primary/30 text-tertiary-light hover:bg-primary/10 flex items-center justify-center text-sm font-bold transition-all">+</button>
                            </div>
                            <p class="text-sm font-bold text-tertiary-dark w-16 text-right">$${subtotal}</p>
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
            // Close cart on mobile after checkout
            if (window.innerWidth < 1024) {
                toggleCart();
            }
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
                    btn.classList.add('bg-tertiary', 'text-white', 'border-tertiary');
                    btn.classList.remove('bg-white', 'text-tertiary-light', 'border-primary/30');
                } else {
                    btn.classList.remove('bg-tertiary', 'text-white', 'border-tertiary');
                    btn.classList.add('bg-white', 'text-tertiary-light', 'border-primary/30');
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