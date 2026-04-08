<?php
include '../db/auth.php';
include '../db/conexion.php';

$isAdmin = ($_SESSION['rol'] === 'admin');

$usuarios = [];
$categorias = [];

if ($isAdmin) {
    $stmtU = $pdo->query("SELECT id, username, nombre, rol, created_at FROM usuarios ORDER BY nombre ASC");
    $usuarios = $stmtU->fetchAll(PDO::FETCH_ASSOC);

    $stmtC = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
    $categorias = $stmtC->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes - K&Kream</title>
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
        #sidebar-overlay { transition: opacity 0.3s ease; }
        #sidebar-overlay.hidden { opacity: 0; pointer-events: none; }
        #sidebar-mobile { transition: transform 0.3s ease; }
        #sidebar-mobile.sidebar-closed { transform: translateX(-100%); }

        /* Toast animations */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.3s ease forwards; }
        .toast-exit { animation: slideOutRight 0.3s ease forwards; }

        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
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
                            <a href="reportes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-primary/70 hover:bg-tertiary/50 font-heading font-medium transition-all duration-300">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                                Reportes
                            </a>
                        </li>
                        <li>
                            <a href="ajustes.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-tertiary text-primary font-heading font-semibold transition-all duration-300">
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

        <!-- Main Content -->
        <main class="flex-1 bg-neutral h-screen overflow-y-auto p-4 sm:p-6 lg:p-8">

            <!-- Mobile top bar -->
            <div class="flex items-center justify-between lg:hidden mb-6">
                <button onclick="toggleSidebar()" class="p-2 text-tertiary-dark hover:bg-primary/20 rounded-lg transition-all duration-300">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <img src="/pos-system/src/kkream_logo.png" alt="KKream" class="h-10">
                <div class="w-10"></div>
            </div>

            <!-- Page Header -->
            <div class="mb-6 lg:mb-8 max-w-4xl mx-auto">
                <h2 class="text-2xl font-heading font-bold text-tertiary-dark">Ajustes</h2>
                <p class="text-tertiary-light text-sm mt-1">Configuración del sistema y tu cuenta</p>
            </div>

            <div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-8">
                
                <!-- Tabs Menu -->
                <div class="w-full md:w-64 flex-shrink-0">
                    <div class="bg-white rounded-2xl border border-primary/20 shadow-sm p-3 flex flex-row md:flex-col gap-2 overflow-x-auto">
                        <?php if ($isAdmin): ?>
                            <button onclick="switchTab('usuarios')" id="tab-btn-usuarios" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-heading font-semibold text-tertiary-light hover:bg-primary/10 transition-all text-left whitespace-nowrap active-tab bg-tertiary/10 !text-tertiary">
                                <i data-lucide="users" class="w-5 h-5"></i>Usuarios
                            </button>
                            <button onclick="switchTab('categorias')" id="tab-btn-categorias" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-heading font-semibold text-tertiary-light hover:bg-primary/10 transition-all text-left whitespace-nowrap">
                                <i data-lucide="tags" class="w-5 h-5"></i>Categorías
                            </button>
                        <?php endif; ?>
                        <button onclick="switchTab('seguridad')" id="tab-btn-seguridad" class="tab-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-heading font-semibold text-tertiary-light hover:bg-primary/10 transition-all text-left whitespace-nowrap <?php echo !$isAdmin ? 'active-tab bg-tertiary/10 !text-tertiary' : ''; ?>">
                            <i data-lucide="shield" class="w-5 h-5"></i>Seguridad
                        </button>
                    </div>
                </div>

                <!-- Tabs Content -->
                <div class="flex-1">
                    
                    <?php if ($isAdmin): ?>
                    <!-- TAB: USUARIOS -->
                    <div id="tab-usuarios" class="tab-content active">
                        <div class="bg-white rounded-2xl border border-primary/20 shadow-sm overflow-hidden mb-6">
                            <div class="px-6 py-5 border-b border-primary/20 flex justify-between items-center bg-neutral/30">
                                <h3 class="text-lg font-heading font-bold text-tertiary-dark">Gestión de Usuarios</h3>
                                <button onclick="abrirModalUsuario()" class="px-4 py-2 bg-tertiary text-white text-sm font-semibold rounded-lg hover:bg-tertiary-dark transition flex items-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Nuevo
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="text-xs font-bold text-tertiary-light uppercase tracking-wider border-b border-primary/20">
                                            <th class="px-6 py-4">Usuario</th>
                                            <th class="px-6 py-4">Rol</th>
                                            <th class="px-6 py-4 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-primary/10">
                                        <?php foreach($usuarios as $u): ?>
                                            <tr class="hover:bg-primary/5 transition-colors group">
                                                <td class="px-6 py-4">
                                                    <p class="font-heading font-semibold text-tertiary-dark"><?= htmlspecialchars($u['nombre']) ?></p>
                                                    <p class="text-xs text-tertiary-light">@<?= htmlspecialchars($u['username']) ?></p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $u['rol'] === 'admin' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800'; ?>">
                                                        <?= htmlspecialchars($u['rol']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($u)) ?>)" class="p-2 text-tertiary-light hover:text-blue-600 transition">
                                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                    </button>
                                                    <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                        <button onclick="eliminarUsuario(<?= $u['id'] ?>)" class="p-2 text-tertiary-light hover:text-red-600 transition">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: CATEGORÍAS -->
                    <div id="tab-categorias" class="tab-content">
                        <div class="bg-white rounded-2xl border border-primary/20 shadow-sm overflow-hidden mb-6">
                            <div class="px-6 py-5 border-b border-primary/20 flex justify-between items-center bg-neutral/30">
                                <h3 class="text-lg font-heading font-bold text-tertiary-dark">Categorías</h3>
                            </div>
                            
                            <!-- Crear rápida -->
                            <div class="p-6 border-b border-primary/20 bg-primary/5">
                                <form id="form-categoria" onsubmit="guardarCategoria(event)" class="flex gap-4">
                                    <input type="text" id="cat-nombre" placeholder="Nombre de categoría" required
                                        class="flex-1 px-4 py-2 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary transition">
                                    <button type="submit" class="px-5 py-2 bg-tertiary text-white text-sm font-semibold rounded-xl hover:bg-tertiary-dark transition">
                                        Crear
                                    </button>
                                </form>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <tbody class="divide-y divide-primary/10">
                                        <?php foreach($categorias as $c): ?>
                                            <tr class="hover:bg-primary/5 transition-colors">
                                                <td class="px-6 py-4">
                                                    <span class="font-heading font-medium text-tertiary-dark"><?= htmlspecialchars($c['nombre']) ?></span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button onclick="eliminarCategoria(<?= $c['id'] ?>)" class="p-2 text-tertiary-light hover:text-red-600 transition">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if(empty($categorias)): ?>
                                            <tr><td colspan="2" class="px-6 py-4 text-center text-sm text-tertiary-light">No hay categorías</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- TAB: SEGURIDAD -->
                    <div id="tab-seguridad" class="tab-content <?php echo !$isAdmin ? 'active' : ''; ?>">
                        <div class="bg-white rounded-2xl border border-primary/20 shadow-sm overflow-hidden mb-6 p-6">
                            <h3 class="text-lg font-heading font-bold text-tertiary-dark mb-2">Cambiar Contraseña</h3>
                            <p class="text-sm text-tertiary-light mb-6">Actualiza la contraseña con la que inicias sesión.</p>

                            <form id="form-seguridad" onsubmit="cambiarPassword(event)" class="space-y-4 max-w-sm">
                                <div>
                                    <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Contraseña Actual</label>
                                    <input type="password" id="pass-actual" required
                                        class="w-full px-4 py-3 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Nueva Contraseña</label>
                                    <input type="password" id="pass-nueva" required
                                        class="w-full px-4 py-3 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-tertiary/30 focus:border-tertiary transition">
                                </div>
                                
                                <button type="submit" class="w-full mt-4 px-5 py-3 bg-tertiary text-white text-sm font-semibold rounded-xl hover:bg-tertiary-dark transition shadow-sm">
                                    Actualizar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- TOAST CONTAINER -->
    <div id="toast-container" class="fixed top-4 right-4 z-[999] flex flex-col gap-2 pointer-events-none"></div>

    <?php if ($isAdmin): ?>
    <!-- MODAL: USUARIO -->
    <div id="usuario-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50" onclick="cerrarModalUsuario()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden pointer-events-auto">
                <div class="bg-tertiary-dark px-6 py-5 flex justify-between items-center">
                    <h3 id="modal-user-title" class="text-lg font-heading font-bold text-primary">Nuevo Usuario</h3>
                    <button onclick="cerrarModalUsuario()" class="text-primary hover:text-white transition"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form id="form-usuario" onsubmit="guardarUsuario(event)" class="p-6 space-y-4">
                    <input type="hidden" id="user-id" value="0">
                    
                    <div>
                        <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Nombre Completo</label>
                        <input type="text" id="user-nombre" required class="w-full px-4 py-2 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-tertiary transition">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Username</label>
                        <input type="text" id="user-username" required class="w-full px-4 py-2 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-tertiary transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">Rol</label>
                        <select id="user-rol" class="w-full px-4 py-2 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-tertiary bg-white transition">
                            <option value="cajero">Cajero</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-tertiary-light uppercase tracking-wider mb-2">
                            Contraseña <span id="pwd-req" class="text-xs text-primary font-normal">(Requerida)</span>
                        </label>
                        <input type="password" id="user-pwd" class="w-full px-4 py-2 border border-primary/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-tertiary transition">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="cerrarModalUsuario()" class="flex-1 py-3 text-sm font-semibold text-tertiary-light hover:bg-primary/10 rounded-xl transition">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-tertiary text-white text-sm font-semibold rounded-xl hover:bg-tertiary-dark transition shadow-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        lucide.createIcons();

        // UI Generics
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-mobile');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = { success: 'bg-green-600', error: 'bg-red-600', info: 'bg-tertiary' };
            const icons = { success: '✓', error: '✕', info: 'ℹ' };

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

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-tertiary/10', '!text-tertiary');
            });
            
            document.getElementById('tab-' + tabId).classList.add('active');
            document.getElementById('tab-btn-' + tabId).classList.add('bg-tertiary/10', '!text-tertiary');
        }

        // SEGURIDAD
        async function cambiarPassword(e) {
            e.preventDefault();
            const actual = document.getElementById('pass-actual').value;
            const nueva = document.getElementById('pass-nueva').value;

            try {
                const res = await fetch('../api/cambiar_password.php', {
                    method: 'POST',
                    body: JSON.stringify({ password_actual: actual, password_nueva: nueva }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if(data.success) {
                    showToast('Contraseña actualizada correctamente', 'success');
                    document.getElementById('form-seguridad').reset();
                } else {
                    showToast(data.error || 'Error al actualizar', 'error');
                }
            } catch(error) {
                showToast('Error de conexión', 'error');
            }
        }

        <?php if ($isAdmin): ?>
        // CATEGORIAS
        async function guardarCategoria(e) {
            e.preventDefault();
            const nombre = document.getElementById('cat-nombre').value;
            try {
                const res = await fetch('../api/guardar_categoria.php', {
                    method: 'POST',
                    body: JSON.stringify({ nombre }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if(data.success) {
                    showToast('Categoría creada', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error, 'error');
                }
            } catch(e) { showToast('Error', 'error'); }
        }

        async function eliminarCategoria(id) {
            if(!confirm('¿Estás seguro de eliminar esta categoría?')) return;
            try {
                const res = await fetch('../api/eliminar_categoria.php', {
                    method: 'POST',
                    body: JSON.stringify({ id }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if(data.success) {
                    showToast('Categoría eliminada', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error, 'error');
                }
            } catch(e) { showToast('Error', 'error'); }
        }

        // USUARIOS
        function abrirModalUsuario() {
            document.getElementById('form-usuario').reset();
            document.getElementById('user-id').value = '0';
            document.getElementById('modal-user-title').textContent = 'Nuevo Usuario';
            document.getElementById('pwd-req').textContent = '(Requerida)';
            document.getElementById('user-pwd').required = true;
            document.getElementById('usuario-modal').classList.remove('hidden');
        }

        function editarUsuario(user) {
            document.getElementById('form-usuario').reset();
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-nombre').value = user.nombre;
            document.getElementById('user-username').value = user.username;
            document.getElementById('user-rol').value = user.rol;
            
            document.getElementById('modal-user-title').textContent = 'Editar Usuario';
            document.getElementById('pwd-req').textContent = '(Opcional)';
            document.getElementById('user-pwd').required = false;

            document.getElementById('usuario-modal').classList.remove('hidden');
        }

        function cerrarModalUsuario() {
            document.getElementById('usuario-modal').classList.add('hidden');
        }

        async function guardarUsuario(e) {
            e.preventDefault();
            const id = document.getElementById('user-id').value;
            const payload = {
                id: id,
                nombre: document.getElementById('user-nombre').value,
                username: document.getElementById('user-username').value,
                rol: document.getElementById('user-rol').value,
                password: document.getElementById('user-pwd').value
            };

            try {
                const res = await fetch('../api/guardar_usuario.php', {
                    method: 'POST',
                    body: JSON.stringify(payload),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if(data.success) {
                    showToast('Usuario guardado', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error, 'error');
                }
            } catch(err) { showToast('Error de conexión', 'error'); }
        }

        async function eliminarUsuario(id) {
            if(!confirm('¿Estás seguro de eliminar este usuario?')) return;
            try {
                const res = await fetch('../api/eliminar_usuario.php', {
                    method: 'POST',
                    body: JSON.stringify({ id }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if(data.success) {
                    showToast('Usuario eliminado', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error, 'error');
                }
            } catch(e) { showToast('Error', 'error'); }
        }
        <?php endif; ?>
    </script>
</body>

</html>