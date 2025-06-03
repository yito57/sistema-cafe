<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';
require_once 'vendor/autoload.php'; // Cargar PHPMailer vía Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validar CSRF
function validate_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'login':
        validate_csrf();
        $nombreu = filter_input(INPUT_POST, 'nombreu', FILTER_SANITIZE_STRING);
        $contrasena = $_POST['contrasena'];
        $stmt = $conn->prepare("SELECT idusuario, idperfil, contrasena FROM usuario WHERE nombreu = ? AND estado = 'activo'");
        $stmt->bind_param('s', $nombreu);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($contrasena, $row['contrasena'])) {
                $_SESSION['idusuario'] = $row['idusuario'];
                session_regenerate_id(true);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        }
        $stmt->close();
        break;

    case 'forgotPassword':
        validate_csrf();
        $nombreu = filter_input(INPUT_POST, 'nombreu', FILTER_SANITIZE_STRING);
        $stmt = $conn->prepare("SELECT u.idusuario, p.correo FROM usuario u JOIN persona p ON u.idpersona = p.idpersona WHERE u.nombreu = ? AND u.estado = 'activo'");
        $stmt->bind_param('s', $nombreu);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt_token = $conn->prepare("INSERT INTO reset_tokens (idusuario, token, expires_at) VALUES (?, ?, ?)");
            $stmt_token->bind_param('iss', $row['idusuario'], $token, $expires_at);
            $stmt_token->execute();
            $stmt_token->close();

            // Configurar PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ventass12112@gmail.com'; // Reemplaza con tu correo de Gmail
                $mail->Password = 'ieib jqhz btcr ogqd'; // Reemplaza con la contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuración del correo
                $mail->setFrom('no-reply@sistemacafe.com', 'Sistema Café');
                $mail->addAddress($row['correo']);
                $mail->Subject = 'Sistema Café - Restablecer Contraseña';
                $mail->Body = "Haga clic en el siguiente enlace para restablecer su contraseña: http://localhost/sistema_cafe/reset_password.php?token=$token\nEl enlace expira en 1 hora.";
                $mail->AltBody = "Haga clic en el siguiente enlace para restablecer su contraseña: http://localhost/sistema_cafe/reset_password.php?token=$token\nEl enlace expira en 1 hora.";

                $mail->send();
                echo json_encode(['success' => true, 'message' => 'Se ha enviado un enlace de restablecimiento a su correo']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o inactivo']);
        }
        $stmt->close();
        break;

    case 'resetPassword':
        validate_csrf();
        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("SELECT idusuario FROM reset_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $idusuario = $row['idusuario'];
            $stmt_update = $conn->prepare("UPDATE usuario SET contrasena = ? WHERE idusuario = ?");
            $stmt_update->bind_param('si', $contrasena, $idusuario);
            $stmt_update->execute();
            $stmt_update->close();
            $stmt_delete = $conn->prepare("DELETE FROM reset_tokens WHERE token = ?");
            $stmt_delete->bind_param('s', $token);
            $stmt_delete->execute();
            $stmt_delete->close();
            echo json_encode(['success' => true, 'message' => 'Contraseña restablecida correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Enlace inválido o expirado']);
        }
        $stmt->close();
        break;

    case 'savePerfil':
        validate_csrf();
        $idperfil = $_POST['idperfil'] ? filter_input(INPUT_POST, 'idperfil', FILTER_VALIDATE_INT) : null;
        $descripc = filter_input(INPUT_POST, 'descripc', FILTER_SANITIZE_STRING);
        if ($idperfil) {
            $stmt = $conn->prepare("UPDATE perfil SET descripc = ? WHERE idperfil = ?");
            $stmt->bind_param('si', $descripc, $idperfil);
        } else {
            $stmt = $conn->prepare("INSERT INTO perfil (descripc) VALUES (?)");
            $stmt->bind_param('s', $descripc);
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'disablePerfil':
        validate_csrf();
        $idperfil = filter_input(INPUT_POST, 'idperfil', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("UPDATE perfil SET estado = 'inactivo' WHERE idperfil = ?");
        $stmt->bind_param('i', $idperfil);
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'getPerfiles':
        $result = $conn->query("SELECT * FROM perfil");
        $perfiles = [];
        while ($row = $result->fetch_assoc()) {
            $perfiles[] = $row;
        }
        echo json_encode($perfiles);
        break;

    case 'savePersona':
        validate_csrf();
        $idpersona = $_POST['idpersona'] ? filter_input(INPUT_POST, 'idpersona', FILTER_VALIDATE_INT) : null;
        $nom1 = filter_input(INPUT_POST, 'nom1', FILTER_SANITIZE_STRING);
        $nom2 = filter_input(INPUT_POST, 'nom2', FILTER_SANITIZE_STRING);
        $apell1 = filter_input(INPUT_POST, 'apell1', FILTER_SANITIZE_STRING);
        $apell2 = filter_input(INPUT_POST, 'apell2', FILTER_SANITIZE_STRING);
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        $tele = filter_input(INPUT_POST, 'tele', FILTER_SANITIZE_STRING);
        $movil = filter_input(INPUT_POST, 'movil', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $fecha_nac = $_POST['fecha_nac'] ? filter_input(INPUT_POST, 'fecha_nac', FILTER_SANITIZE_STRING) : null;
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo inválido']);
            exit;
        }
        if ($idpersona) {
            $stmt = $conn->prepare("UPDATE persona SET nom1 = ?, nom2 = ?, apell1 = ?, apell2 = ?, direccion = ?, tele = ?, movil = ?, correo = ?, fecha_nac = ? WHERE idpersona = ?");
            $stmt->bind_param('sssssssssi', $nom1, $nom2, $apell1, $apell2, $direccion, $tele, $movil, $correo, $fecha_nac, $idpersona);
        } else {
            $stmt = $conn->prepare("INSERT INTO persona (nom1, nom2, apell1, apell2, direccion, tele, movil, correo, fecha_nac) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $nom1, $nom2, $apell1, $apell2, $direccion, $tele, $movil, $correo, $fecha_nac);
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'disablePersona':
        validate_csrf();
        $idpersona = filter_input(INPUT_POST, 'idpersona', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("UPDATE persona SET estado = 'inactivo' WHERE idpersona = ?");
        $stmt->bind_param('i', $idpersona);
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'getPersonas':
        $result = $conn->query("SELECT * FROM persona");
        $personas = [];
        while ($row = $result->fetch_assoc()) {
            $personas[] = $row;
        }
        echo json_encode($personas);
        break;

    case 'saveUsuario':
        validate_csrf();
        $idusuario = $_POST['idusuario'] ? filter_input(INPUT_POST, 'idusuario', FILTER_VALIDATE_INT) : null;
        $idpersona = filter_input(INPUT_POST, 'idpersona', FILTER_VALIDATE_INT);
        $nombreu = filter_input(INPUT_POST, 'nombreu', FILTER_SANITIZE_STRING);
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $idperfil = filter_input(INPUT_POST, 'idperfil', FILTER_VALIDATE_INT);
        if ($idusuario) {
            $stmt = $conn->prepare("UPDATE usuario SET nombreu = ?, contrasena = ?, idperfil = ? WHERE idusuario = ?");
            $stmt->bind_param('ssii', $nombreu, $contrasena, $idperfil, $idusuario);
        } else {
            $stmt = $conn->prepare("INSERT INTO usuario (idpersona, nombreu, contrasena, idperfil) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('issi', $idpersona, $nombreu, $contrasena, $idperfil);
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'disableUsuario':
        validate_csrf();
        $idusuario = filter_input(INPUT_POST, 'idusuario', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("UPDATE usuario SET estado = 'inactivo' WHERE idusuario = ?");
        $stmt->bind_param('i', $idusuario);
        $stmt->execute();
        echo json_encode(['success' => true]);
        $stmt->close();
        break;

    case 'getUsuarios':
        $result = $conn->query("SELECT u.*, p.descripc FROM usuario u JOIN perfil p ON u.idperfil = p.idperfil");
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        echo json_encode($usuarios);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

$conn->close();
?>