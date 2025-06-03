<?php
session_start();
// Si el usuario ya está autenticado, redirigir a menu.php
if (isset($_SESSION['idusuario'])) {
    header('Location: menu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema ganadero - Menú</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background-image: url('https://source.unsplash.com/random/1920x1080/?coffee'); background-size: cover; }
        .menu-container { background: rgba(255, 255, 255, 0.9); }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="menu-container p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-4">Sistema ganadero - Menú</h1>
        <a href="perfil_form.php" class="block w-full bg-green-600 text-white p-2 rounded mb-2 hover:bg-green-700">Gestionar Perfiles</a>
        <a href="usuario_form.php" class="block w-full bg-green-600 text-white p-2 rounded mb-2 hover:bg-green-700">Gestionar Usuarios</a>
        <a href="persona_form.php" class="block w-full bg-green-600 text-white p-2 rounded mb-2 hover:bg-green-700">Gestionar Personas</a>
        <a href="index.php" class="block w-full bg-red-600 text-white p-2 rounded hover:bg-red-700">Cerrar Sesión</a>
    </div>
</body>
</html>