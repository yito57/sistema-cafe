<?php
$host = 'localhost';
$username = 'root';
$password = '123456';
$database = 'sistema_cafe';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}
$conn->set_charset('utf8');

?>