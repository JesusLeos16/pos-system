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
        /* Modal animations */
        .modal-overlay {
            transition: opacity 0.3s ease;
        }
        .modal-content {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        /* Toast animations */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter {
            animation: slideInRight 0.3s ease forwards;
        }
        .toast-exit {
            animation: slideOutRight 0.3s ease forwards;
        }
        /* Print styles */
        @media print {
            body * { visibility: hidden; }
            #recibo-modal, #recibo-modal * { visibility: visible; }
            #recibo-modal { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
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
                        <?= date('d/m/Y, H:i') ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="limpiarCarrito()" class="p-2 text-tertiary-light hover:text-red-500 hover:bg-red-50 rounded-lg transition-all duration-200" title="Limpiar orden">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                    <button onclick="toggleCart()" class="p-2 text-tertiary-light hover:text-tertiary rounded-lg transition-all duration-200 lg:hidden" title="Cerrar">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Items del carrito -->
            <div id="carrito-items" class="flex-1 overflow-y-auto p-5">
                <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-primary">
                    <i data-lucide="shopping-bag" class="w-12 h-12 mb-3"></i>
                    <p class="text-sm font-medium text-tertiary-light">Carrito vacío</p>
                    <p class="text-xs mt-1 text-tertiary-light/60">Selecciona productos para agregar</p>
                </div>
            </div>

            <!-- Footer: Totales y cobrar -->
            <div class="border-t border-primary/20">
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

                <div class="p-5 pt-3 pb-safe">
                    <button onclick="abrirModalCobro()" id="btn-cobrar" class="w-full py-3.5 bg-tertiary text-white font-heading font-semibold rounded-xl hover:bg-tertiary-dark transition-all duration-300 shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span>Cobrar</span>
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- TOAST CONTAINER -->
    <div id="toast-container" class="fixed top-4 right-4 z-[999] flex flex-col gap-2 pointer-events-none"></div>

    <!-- MODAL: Cobrar -->
    <div id="cobro-modal" class="fixed inset-0 z-[100] hidden">
        <div class="modal-overlay absolute inset-0 bg-black/50" onclick="cerrarModalCobro()"></div>
        <div class="modal-content absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-tertiary-dark px-6 py-5">
                    <h3 class="text-lg font-heading font-bold text-primary">Confirmar Cobro</h3>
                    <p class="text-primary/60 text-xs mt-1">Selecciona método de pago</p>
                </div>

                <div class="p-6">
                    <!-- Total -->
                    <div class="text-center mb-6">
                        <p class="text-sm text-tertiary-light">Total a cobrar</p>
                        <p id="modal-total" class="text-4xl font-heading font-extrabold text-tertiary-dark mt-1">$0.00</p>
                    </div>

                    <!-- Método de pago -->
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <button onclick="seleccionarMetodo('efectivo')" id="btn-efectivo" class="metodo-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-tertiary bg-tertiary/5 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-tertiary"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                            <span class="text-xs font-semibold text-tertiary">Efectivo</span>
                        </button>
                        <button onclick="seleccionarMetodo('tarjeta')" id="btn-tarjeta" class="metodo-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-primary/30 hover:border-tertiary/50 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-tertiary-light"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            <span class="text-xs font-semibold text-tertiary-light">Tarjeta</span>
                        </button>
                        <button onclick="seleccionarMetodo('transferencia')" id="btn-transferencia" class="metodo-btn flex flex-col items-center gap-2 p-4 rounded-xl border-2 border-primary/30 hover:border-tertiary/50 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-tertiary-light"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                            <span class="text-xs font-semibold text-tertiary-light">Transferencia</span>
                        </button>
                    </div>

                    <!-- Campo de monto recibido (solo efectivo) -->
                    <div id="campo-efectivo" class="mb-6">
                        <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Monto Recibido</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-tertiary-light text-sm font-semibold">$</span>
                            <input type="number" id="monto-recibido" step="0.01" min="0" placeholder="0.00"
                                class="w-full pl-8 pr-4 py-3 border border-primary/30 rounded-lg text-lg font-bold text-tertiary-dark focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary/40 transition-all duration-300"
                                oninput="calcularCambio()">
                        </div>
                        <div id="cambio-display" class="mt-3 p-3 bg-secondary/20 border border-secondary/40 rounded-lg hidden">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-tertiary-dark">Cambio:</span>
                                <span id="cambio-valor" class="text-lg font-bold text-green-700">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3">
                        <button onclick="cerrarModalCobro()" class="flex-1 py-3 text-sm font-semibold text-tertiary-light hover:text-tertiary rounded-xl hover:bg-primary/10 transition-all duration-300">
                            Cancelar
                        </button>
                        <button onclick="procesarCobro()" id="btn-procesar" class="flex-1 py-3 bg-tertiary text-white text-sm font-heading font-semibold rounded-xl hover:bg-tertiary-dark transition-all duration-300 shadow-sm flex items-center justify-center gap-2">
                            <span>Confirmar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Recibo -->
    <div id="recibo-modal" class="fixed inset-0 z-[100] hidden">
        <div class="modal-overlay absolute inset-0 bg-black/50"></div>
        <div class="modal-content absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden">
                <!-- Ticket -->
                <div class="p-6" id="recibo-contenido">
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-heading font-bold text-tertiary-dark">K&Kream</h3>
                        <p class="text-xs text-tertiary-light">Punto de Venta</p>
                    </div>

                    <div class="border-t border-dashed border-tertiary-light/30 pt-3 mb-3">
                        <div class="flex justify-between text-xs text-tertiary-light">
                            <span>Orden:</span>
                            <span id="recibo-orden" class="font-semibold text-tertiary-dark">—</span>
                        </div>
                        <div class="flex justify-between text-xs text-tertiary-light mt-1">
                            <span>Fecha:</span>
                            <span id="recibo-fecha" class="text-tertiary-dark">—</span>
                        </div>
                        <div class="flex justify-between text-xs text-tertiary-light mt-1">
                            <span>Cajero:</span>
                            <span id="recibo-cajero" class="text-tertiary-dark">—</span>
                        </div>
                        <div class="flex justify-between text-xs text-tertiary-light mt-1">
                            <span>Pago:</span>
                            <span id="recibo-metodo" class="text-tertiary-dark capitalize">—</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-tertiary-light/30 pt-3 mb-3">
                        <div id="recibo-items" class="space-y-2"></div>
                    </div>

                    <div class="border-t border-dashed border-tertiary-light/30 pt-3 space-y-1">
                        <div class="flex justify-between text-xs text-tertiary-light">
                            <span>Subtotal</span>
                            <span id="recibo-subtotal">—</span>
                        </div>
                        <div class="flex justify-between text-xs text-tertiary-light">
                            <span>IVA (16%)</span>
                            <span id="recibo-impuesto">—</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-tertiary-dark pt-1">
                            <span>TOTAL</span>
                            <span id="recibo-total">—</span>
                        </div>
                        <div id="recibo-cambio-row" class="flex justify-between text-xs text-green-700 hidden">
                            <span>Cambio</span>
                            <span id="recibo-cambio">—</span>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-t border-dashed border-tertiary-light/30">
                        <p class="text-xs text-tertiary-light">¡Gracias por tu compra!</p>
                    </div>
                </div>

                <!-- Botones del recibo -->
                <div class="px-6 pb-6 flex gap-3 no-print">
                    <button onclick="window.print()" class="flex-1 py-3 border border-primary/30 text-sm font-semibold text-tertiary rounded-xl hover:bg-primary/10 transition-all duration-300 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        Imprimir
                    </button>
                    <button onclick="cerrarRecibo()" class="flex-1 py-3 bg-tertiary text-white text-sm font-heading font-semibold rounded-xl hover:bg-tertiary-dark transition-all duration-300">
                        Nueva Venta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // === TOAST NOTIFICATIONS ===
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = {
                success: 'bg-green-600',
                error: 'bg-red-600',
                info: 'bg-tertiary',
            };
            const icons = {
                success: '✓',
                error: '✕',
                info: 'ℹ',
            };

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center gap-3 px-5 py-3 ${colors[type]} text-white rounded-xl shadow-lg text-sm font-medium toast-enter`;
            toast.innerHTML = `
                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">${icons[type]}</span>
                <span>${message}</span>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

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
        let metodoPago = 'efectivo';

        function agregarAlCarrito(el) {
            const id = el.dataset.id;
            const nombre = el.dataset.nombre;
            const precio = parseFloat(el.dataset.precio);
            const stock = parseInt(el.dataset.stock);

            const existente = carrito.find(item => item.id === id);

            if (existente) {
                if (existente.cantidad < stock) {
                    existente.cantidad++;
                    showToast(`${nombre} (x${existente.cantidad})`, 'info');
                } else {
                    showToast('Stock máximo alcanzado', 'error');
                }
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1, stock });
                showToast(`${nombre} agregado`, 'success');
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

        function getTotal() {
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            return subtotal + (subtotal * 0.16);
        }

        // === MODAL COBRO ===
        function abrirModalCobro() {
            if (carrito.length === 0) return;
            document.getElementById('cobro-modal').classList.remove('hidden');
            document.getElementById('modal-total').textContent = '$' + getTotal().toFixed(2);
            document.getElementById('monto-recibido').value = '';
            document.getElementById('cambio-display').classList.add('hidden');
            seleccionarMetodo('efectivo');
        }

        function cerrarModalCobro() {
            document.getElementById('cobro-modal').classList.add('hidden');
        }

        function seleccionarMetodo(metodo) {
            metodoPago = metodo;
            document.querySelectorAll('.metodo-btn').forEach(btn => {
                btn.classList.remove('border-tertiary', 'bg-tertiary/5');
                btn.classList.add('border-primary/30');
                btn.querySelector('span').classList.remove('text-tertiary');
                btn.querySelector('span').classList.add('text-tertiary-light');
            });
            const activeBtn = document.getElementById('btn-' + metodo);
            activeBtn.classList.add('border-tertiary', 'bg-tertiary/5');
            activeBtn.classList.remove('border-primary/30');
            activeBtn.querySelector('span').classList.add('text-tertiary');
            activeBtn.querySelector('span').classList.remove('text-tertiary-light');

            // Mostrar/ocultar campo de efectivo
            document.getElementById('campo-efectivo').style.display = metodo === 'efectivo' ? 'block' : 'none';
        }

        function calcularCambio() {
            const monto = parseFloat(document.getElementById('monto-recibido').value) || 0;
            const total = getTotal();
            const cambio = monto - total;
            const display = document.getElementById('cambio-display');
            const valor = document.getElementById('cambio-valor');

            if (monto > 0) {
                display.classList.remove('hidden');
                if (cambio >= 0) {
                    valor.textContent = '$' + cambio.toFixed(2);
                    valor.className = 'text-lg font-bold text-green-700';
                } else {
                    valor.textContent = '-$' + Math.abs(cambio).toFixed(2);
                    valor.className = 'text-lg font-bold text-red-600';
                }
            } else {
                display.classList.add('hidden');
            }
        }

        // === PROCESAR VENTA ===
        async function procesarCobro() {
            if (carrito.length === 0) return;

            const btnProcesar = document.getElementById('btn-procesar');
            btnProcesar.disabled = true;
            btnProcesar.innerHTML = '<span>Procesando...</span>';

            const montoRecibido = metodoPago === 'efectivo' ? parseFloat(document.getElementById('monto-recibido').value) || null : null;

            // Validar monto en efectivo
            if (metodoPago === 'efectivo' && montoRecibido !== null && montoRecibido < getTotal()) {
                showToast('El monto recibido es menor al total', 'error');
                btnProcesar.disabled = false;
                btnProcesar.innerHTML = '<span>Confirmar</span>';
                return;
            }

            try {
                const response = await fetch('../ventas/procesar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        items: carrito,
                        metodo_pago: metodoPago,
                        monto_recibido: montoRecibido,
                    })
                });

                const data = await response.json();

                if (data.success) {
                    cerrarModalCobro();
                    mostrarRecibo(data);
                    showToast('¡Venta registrada correctamente!', 'success');

                    // Actualizar stock visual en las cards
                    carrito.forEach(item => {
                        const card = document.querySelector(`.product-card[data-id="${item.id}"]`);
                        if (card) {
                            const newStock = parseInt(card.dataset.stock) - item.cantidad;
                            card.dataset.stock = newStock;
                            if (newStock <= 0) {
                                card.style.display = 'none';
                            }
                        }
                    });

                    carrito = [];
                    renderCarrito();
                    updateCartBadge();

                    if (window.innerWidth < 1024) {
                        const cart = document.getElementById('cart-panel');
                        const overlay = document.getElementById('cart-overlay');
                        if (!cart.classList.contains('cart-closed')) {
                            cart.classList.add('cart-closed');
                            overlay.classList.add('hidden');
                        }
                    }
                } else {
                    showToast(data.error || 'Error al procesar la venta', 'error');
                }
            } catch (error) {
                showToast('Error de conexión con el servidor', 'error');
                console.error(error);
            }

            btnProcesar.disabled = false;
            btnProcesar.innerHTML = '<span>Confirmar</span>';
        }

        // === RECIBO ===
        function mostrarRecibo(data) {
            document.getElementById('recibo-orden').textContent = data.numero_orden;
            document.getElementById('recibo-fecha').textContent = data.fecha;
            document.getElementById('recibo-cajero').textContent = data.cajero;
            document.getElementById('recibo-metodo').textContent = data.metodo_pago;
            document.getElementById('recibo-subtotal').textContent = '$' + parseFloat(data.subtotal).toFixed(2);
            document.getElementById('recibo-impuesto').textContent = '$' + parseFloat(data.impuesto).toFixed(2);
            document.getElementById('recibo-total').textContent = '$' + parseFloat(data.total).toFixed(2);

            // Cambio
            const cambioRow = document.getElementById('recibo-cambio-row');
            if (data.cambio !== null && data.cambio !== undefined) {
                cambioRow.classList.remove('hidden');
                document.getElementById('recibo-cambio').textContent = '$' + parseFloat(data.cambio).toFixed(2);
            } else {
                cambioRow.classList.add('hidden');
            }

            // Items
            let itemsHtml = '';
            data.items.forEach(item => {
                const sub = (parseFloat(item.precio) * parseInt(item.cantidad)).toFixed(2);
                itemsHtml += `
                    <div class="flex justify-between text-xs">
                        <span class="text-tertiary-dark">${item.cantidad}x ${item.nombre}</span>
                        <span class="text-tertiary-dark font-semibold">$${sub}</span>
                    </div>`;
            });
            document.getElementById('recibo-items').innerHTML = itemsHtml;

            document.getElementById('recibo-modal').classList.remove('hidden');
        }

        function cerrarRecibo() {
            document.getElementById('recibo-modal').classList.add('hidden');
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
            document.querySelectorAll('.cat-btn').forEach(btn => {
                if (btn.dataset.cat === cat) {
                    btn.classList.add('bg-tertiary', 'text-white', 'border-tertiary');
                    btn.classList.remove('bg-white', 'text-tertiary-light', 'border-primary/30');
                } else {
                    btn.classList.remove('bg-tertiary', 'text-white', 'border-tertiary');
                    btn.classList.add('bg-white', 'text-tertiary-light', 'border-primary/30');
                }
            });

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