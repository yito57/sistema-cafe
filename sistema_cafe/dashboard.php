<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['idusuario'])) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT u.nombreu, p.nom1, p.apell1, p.correo, pr.descripc 
    FROM usuario u 
    JOIN persona p ON u.idpersona = p.idpersona 
    JOIN perfil pr ON u.idperfil = pr.idperfil 
    WHERE u.idusuario = ?");
$stmt->bind_param('i', $_SESSION['idusuario']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Determinar la sección a mostrar
$section = isset($_GET['section']) ? $_GET['section'] : 'welcome';
$allowed_sections = ['welcome', 'perfil', 'persona', 'usuario'];
if (!in_array($section, $allowed_sections)) {
    $section = 'welcome';
}

// Verificar si el usuario es administrador para la sección perfil
$is_admin = $user['descripc'] === 'Administrador';
if ($section === 'perfil' && !$is_admin) {
    $section = 'welcome';
}

// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema ganadero - Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background-image: url('https://source.unsplash.com/random/1920x1080/?coffee'); background-size: cover; }
        .sidebar { background: rgba(51, 65, 85, 0.95); }
        .content { background: rgba(255, 255, 255, 0.95); }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 w-64 p-4 text-white transition-transform duration-300 md:static md:translate-x-0">
        <h1 class="text-2xl font-bold mb-6">Sistema ganadero</h1>
        <nav>
            <a href="?section=welcome" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo $section === 'welcome' ? 'bg-gray-700' : ''; ?>">Inicio</a>
            <?php if ($is_admin): ?>
                <a href="?section=perfil" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo $section === 'perfil' ? 'bg-gray-700' : ''; ?>">Perfiles</a>
            <?php endif; ?>
            <a href="?section=persona" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo $section === 'persona' ? 'bg-gray-700' : ''; ?>">Personas</a>
            <a href="?section=usuario" class="block py-2 px-4 rounded hover:bg-gray-700 <?php echo $section === 'usuario' ? 'bg-gray-700' : ''; ?>">Usuarios</a>
            <a href="logout.php" class="block py-2 px-4 rounded hover:bg-red-700 bg-red-600 mt-4">Cerrar Sesión</a>
        </nav>
    </div>

    <!-- Botón para abrir sidebar en móviles -->
    <button class="md:hidden fixed top-4 left-4 z-50 text-white bg-gray-800 p-2 rounded" onclick="toggleSidebar()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Contenido Principal -->
    <div class="content flex-1 p-6 overflow-auto">
        <?php
        if ($section === 'welcome') {
            echo '
                <h1 class="text-3xl font-bold mb-4">Bienvenido, ' . htmlspecialchars($user['nom1'] . ' ' . $user['apell1']) . '</h1>
                <div class="bg-white p-6 rounded-lg shadow">
                    <p><strong>Usuario:</strong> ' . htmlspecialchars($user['nombreu']) . '</p>
                    <p><strong>Correo:</strong> ' . htmlspecialchars($user['correo']) . '</p>
                    <p><strong>Perfil:</strong> ' . htmlspecialchars($user['descripc']) . '</p>
                    <p class="mt-4">Selecciona una opción del menú para gestionar el sistema.</p>
                </div>
            ';
        } elseif ($section === 'perfil' && $is_admin) {
            include 'perfil_form.php';
        } elseif ($section === 'persona') {
            include 'persona_form.php';
        } elseif ($section === 'usuario') {
            include 'usuario_form.php';
        }
        ?>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }
    </script>
</body>
</html>