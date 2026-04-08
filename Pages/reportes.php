<?php
include '../db/auth.php';
include '../db/conexion.php';

// KPIs Hoy
$fecha_hoy = date('Y-m-d');
$stmtHoy = $pdo->prepare("SELECT COUNT(id) as total_ventas, SUM(total) as ingresos FROM ventas WHERE DATE(fecha) = :hoy");
$stmtHoy->execute([':hoy' => $fecha_hoy]);
$kpisObj = $stmtHoy->fetch(PDO::FETCH_ASSOC);

$total_ventas_hoy = $kpisObj['total_ventas'] ?: 0;
$ingresos_hoy = floatval($kpisObj['ingresos']) ?: 0;
$ticket_promedio_hoy = $total_ventas_hoy > 0 ? $ingresos_hoy / $total_ventas_hoy : 0;

// Ventas ultimos 7 dias (para grafica)
$stmt7Dias = $pdo->query("
    SELECT DATE(fecha) as dia, SUM(total) as total_dia
    FROM ventas 
    WHERE fecha >= DATE(NOW() - INTERVAL 6 DAY)
    GROUP BY DATE(fecha)
    ORDER BY DATE(fecha) ASC
");
$ventas_7_dias = $stmt7Dias->fetchAll(PDO::FETCH_ASSOC);

$dias_chart = [];
$totales_chart = [];

// Generar últimos 7 días explícitos para no tener vacíos en la gráfica
for ($i = 6; $i >= 0; $i--) {
    $fecha_iter = date('Y-m-d', strtotime("-$i days"));
    $dias_chart[] = date('d/m', strtotime($fecha_iter));
    
    // Buscar si hay venta en esa fecha
    $total_encontra = 0;
    foreach ($ventas_7_dias as $v) {
        if ($v['dia'] === $fecha_iter) {
            $total_encontra = floatval($v['total_dia']);
            break;
        }
    }
    $totales_chart[] = $total_encontra;
}

// Productos mas vendidos (para grafica)
$stmtTopProductos = $pdo->query("
    SELECT nombre_producto, SUM(cantidad) as total_vendido
    FROM venta_detalle
    GROUP BY producto_id, nombre_producto
    ORDER BY total_vendido DESC
    LIMIT 5
");
$top_productos = $stmtTopProductos->fetchAll(PDO::FETCH_ASSOC);

$productos_nombres = [];
$productos_cantidades = [];
foreach($top_productos as $tp) {
    $productos_nombres[] = htmlspecialchars($tp['nombre_producto']);
    $productos_cantidades[] = intval($tp['total_vendido']);
}

// Ultimas 10 ventas
$stmtUltimasVentas = $pdo->query("
    SELECT v.*, u.nombre as cajero
    FROM ventas v
    LEFT JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.fecha DESC
    LIMIT 10
");
$ultimas_ventas = $stmtUltimasVentas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - K&Kream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Ocultar barra de scroll en tabla en móviles para forzar scroll dentro del div si es necesario */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>

<body class="font-body text-tertiary-dark bg-neutral">
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
                            <a href="reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
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
        <main class="flex-1 bg-neutral h-screen overflow-y-auto p-4 sm:p-6 lg:p-8">

            <!-- Mobile top bar -->
            <div class="flex items-center justify-between lg:hidden mb-6">
                <button onclick="toggleSidebar()" class="p-2 text-tertiary-dark hover:bg-primary/20 rounded-lg transition-all duration-300">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                <div class="w-10"></div>
            </div>

            <!-- Encabezado -->
            <div class="mb-6 lg:mb-8">
                <h2 class="text-2xl font-heading font-bold text-tertiary-dark">Dashboard</h2>
                <p class="text-tertiary-light text-sm mt-1">Resumen general de actividad y ventas</p>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">
                <!-- Ventas Totales Hoy -->
                <div class="bg-white rounded-2xl p-5 border border-primary/20 shadow-sm flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary/20 flex items-center justify-center text-tertiary">
                        <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-tertiary-light uppercase tracking-wider mb-1">Ventas Hoy</p>
                        <h3 class="text-2xl font-heading font-extrabold text-tertiary-dark"><?= $total_ventas_hoy ?></h3>
                    </div>
                </div>

                <!-- Ingresos Hoy -->
                <div class="bg-white rounded-2xl p-5 border border-primary/20 shadow-sm flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-secondary/30 flex items-center justify-center text-green-700">
                        <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-tertiary-light uppercase tracking-wider mb-1">Ingresos Hoy</p>
                        <h3 class="text-2xl font-heading font-extrabold text-tertiary-dark">$<?= number_format($ingresos_hoy, 2) ?></h3>
                    </div>
                </div>

                <!-- Ticket Promedio -->
                <div class="bg-white rounded-2xl p-5 border border-primary/20 shadow-sm flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-tertiary/10 flex items-center justify-center text-tertiary-light">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-tertiary-light uppercase tracking-wider mb-1">Ticket Promedio</p>
                        <h3 class="text-2xl font-heading font-extrabold text-tertiary-dark">$<?= number_format($ticket_promedio_hoy, 2) ?></h3>
                    </div>
                </div>
            </div>

            <!-- Gráficas (2 columnas) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Ingresos últimos 7 días -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-primary/20 shadow-sm">
                    <h3 class="text-sm font-heading font-bold text-tertiary-dark mb-4">Ingresos Últimos 7 Días</h3>
                    <div class="w-full h-64 relative">
                        <canvas id="ingresosChart"></canvas>
                    </div>
                </div>

                <!-- Top Productos -->
                <div class="bg-white rounded-2xl p-5 border border-primary/20 shadow-sm">
                    <h3 class="text-sm font-heading font-bold text-tertiary-dark mb-4">Top 5 Productos</h3>
                    <?php if (count($productos_nombres) > 0): ?>
                        <div class="w-full h-64 relative flex items-center justify-center">
                            <canvas id="productosChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full min-h-[16rem] flex flex-col items-center justify-center text-tertiary-light/50">
                            <i data-lucide="pie-chart" class="w-12 h-12 mb-2"></i>
                            <p class="text-sm">No hay suficientes datos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Últimas Ventas -->
            <div class="bg-white rounded-2xl border border-primary/20 shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-5 border-b border-primary/20 flex justify-between items-center bg-neutral/30">
                    <h3 class="text-lg font-heading font-bold text-tertiary-dark">Últimas Transacciones</h3>
                    <div class="p-2 rounded-lg bg-primary/20 text-tertiary">
                        <i data-lucide="history" class="w-4 h-4"></i>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="text-xs font-bold text-tertiary-light uppercase tracking-wider border-b border-primary/20">
                                <th class="px-6 py-4">Orden</th>
                                <th class="px-6 py-4">Cajero</th>
                                <th class="px-6 py-4">Método</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            <?php if(count($ultimas_ventas) > 0): ?>
                                <?php foreach($ultimas_ventas as $v): ?>
                                    <tr class="hover:bg-primary/5 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-heading font-semibold text-tertiary-dark"><?= htmlspecialchars($v['numero_orden']) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-tertiary/20 flex items-center justify-center text-[10px] font-bold text-tertiary">
                                                    <?= strtoupper(substr($v['cajero'] ?? 'K', 0, 1)) ?>
                                                </div>
                                                <span class="text-sm font-medium text-tertiary-dark"><?= htmlspecialchars($v['cajero'] ?? 'Desconocido') ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-1 text-sm text-tertiary-light capitalize">
                                                <?php if($v['metodo_pago'] === 'efectivo'): ?>
                                                    <i data-lucide="banknote" class="w-4 h-4 text-green-600"></i>
                                                <?php elseif($v['metodo_pago'] === 'tarjeta'): ?>
                                                    <i data-lucide="credit-card" class="w-4 h-4 text-blue-600"></i>
                                                <?php else: ?>
                                                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-orange-500"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($v['metodo_pago']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-tertiary">$<?= number_format($v['total'], 2) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-tertiary-light"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm font-medium text-tertiary-light">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                        No hay transacciones registradas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

    </div>

    <!-- Scripts para Gráficas y UI -->
    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }

        // Datos para las gráficas inyectados desde PHP
        const diasChart = <?= json_encode($dias_chart) ?>;
        const totalesChart = <?= json_encode($totales_chart) ?>;
        
        const prodNombres = <?= json_encode($productos_nombres) ?>;
        const prodCantidades = <?= json_encode($productos_cantidades) ?>;

        // Colores de la marca
        const colorPrimary = '#F4C2C2';
        const colorSecondary = '#98FF98';
        const colorTertiary = '#6F4E37';
        const colorNeutral = '#FDF5E6';
        
        // Colores para dona de productos
        const doughnutColors = [
            '#F4C2C2', // primary
            '#98FF98', // secondary
            '#6F4E37', // tertiary
            '#8B6F5C', // tertiary-light
            '#5a3d2b'  // tertiary-dark
        ];

        // Config global de fuentes para Chart.js
        Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
        Chart.defaults.color = '#8B6F5C'; // tertiary-light

        // 1. Gráfica de Líneas (Ingresos)
        const ctxIngresos = document.getElementById('ingresosChart').getContext('2d');
        
        // Crear gradiente para la gráfica
        const gradientIngresos = ctxIngresos.createLinearGradient(0, 0, 0, 400);
        gradientIngresos.addColorStop(0, 'rgba(244, 194, 194, 0.6)'); // primary con opacidad
        gradientIngresos.addColorStop(1, 'rgba(244, 194, 194, 0.05)');

        new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: diasChart,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: totalesChart,
                    borderColor: colorPrimary,
                    backgroundColor: gradientIngresos,
                    borderWidth: 3,
                    pointBackgroundColor: colorTertiary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: colorTertiary,
                        titleFont: { family: '"Plus Jakarta Sans"', size: 13 },
                        bodyFont: { family: '"Plus Jakarta Sans"', size: 14, weight: 'bold' },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(244, 194, 194, 0.2)' },
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // 2. Gráfica Doughnut (Productos)
        if (prodNombres.length > 0) {
            const ctxProductos = document.getElementById('productosChart').getContext('2d');
            new Chart(ctxProductos, {
                type: 'doughnut',
                data: {
                    labels: prodNombres,
                    datasets: [{
                        data: prodCantidades,
                        backgroundColor: doughnutColors,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: colorTertiary,
                            padding: 10,
                            bodyFont: { family: '"Plus Jakarta Sans"', size: 13 },
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.parsed} uds. vendidas`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>