<?php
session_start();
require_once 'config.php';

if (!isset($_GET['token'])) {
    header('Location: index.php');
    exit;
}

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$stmt = $conn->prepare("SELECT idusuario FROM reset_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    echo '<p>Enlace inválido o expirado.</p>';
    exit;
}
$stmt->close();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Café - Restablecer Contraseña</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background-image: url('https://source.unsplash.com/random/1920x1080/?coffee'); background-size: cover; }
        .reset-container { background: rgba(255, 255, 255, 0.9); }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="reset-container p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-4">Restablecer Contraseña</h1>
        <form id="resetForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium">Nueva Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" class="w-full p-2 border rounded" maxlength="50" required>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Restablecer</button>
        </form>
        <p id="message" class="text-center mt-4 text-red-500"></p>
    </div>

    <script>
        $(document).ready(function() {
            $('#resetForm').submit(function(e) {
                e.preventDefault();
                const contrasena = $('#contrasena').val();
                const token = $('input[name="token"]').val();
                const csrf_token = $('input[name="csrf_token"]').val();
                if (!contrasena) {
                    $('#message').text('Por favor, ingrese una contraseña');
                    return;
                }
                $.ajax({
                    url: 'backend.php?action=resetPassword',
                    method: 'POST',
                    data: { contrasena, token, csrf_token },
                    success: function(response) {
                        if (response.success) {
                            $('#message').removeClass('text-red-500').addClass('text-green-500').text('Contraseña restablecida. Redirigiendo...');
                            setTimeout(() => { window.location.href = 'index.php'; }, 2000);
                        } else {
                            $('#message').text(response.message);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>