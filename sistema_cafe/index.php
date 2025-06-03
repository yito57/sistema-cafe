<?php
session_start();
require_once 'config.php';
if (isset($_SESSION['idusuario'])) {
    header('Location: dashboard.php');
    exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema ganadero - Iniciar Sesión</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background-image: url('https://source.unsplash.com/random/1920x1080/?coffee'); background-size: cover; }
        .login-container { background: rgba(255, 255, 255, 0.9); }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="login-container p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-4">Sistema ganadero - Iniciar Sesión</h1>
        <form id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium">Usuario</label>
                <input type="text" id="nombreu" name="nombreu" class="w-full p-2 border rounded" maxlength="50" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" class="w-full p-2 border rounded" maxlength="50" required>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Iniciar Sesión</button>
            <button type="button" id="forgotPassword" class="w-full mt-2 bg-gray-300 p-2 rounded hover:bg-gray-400">Olvidé mi Contraseña</button>
            
        </form>
        <p id="message" class="text-center mt-4 text-red-500"></p>
    </div>

    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                e.preventDefault();
                const nombreu = $('#nombreu').val().trim();
                const contrasena = $('#contrasena').val();
                const csrf_token = $('input[name="csrf_token"]').val();
                if (!nombreu || !contrasena) {
                    $('#message').text('Por favor, complete todos los campos');
                    return;
                }
                $.ajax({
                    url: 'backend.php?action=login',
                    method: 'POST',
                    data: { nombreu, contrasena, csrf_token },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            $('#message').text(response.message);
                        }
                    },
                    error: function() {
                        $('#message').text('Error en el servidor. Intente de nuevo.');
                    }
                });
            });

            $('#forgotPassword').click(function() {
                const nombreu = $('#nombreu').val().trim();
                const csrf_token = $('input[name="csrf_token"]').val();
                if (!nombreu) {
                    $('#message').text('Por favor, ingrese un usuario');
                    return;
                }
                $.ajax({
                    url: 'backend.php?action=forgotPassword',
                    method: 'POST',
                    data: { nombreu, csrf_token },
                    success: function(response) {
                        $('#message').text(response.message);
                    }
                });
            });

            $('#register').click(function() {
                window.location.href = 'dashboard.php?section=persona';
            });
        });
    </script>
</body>
</html>