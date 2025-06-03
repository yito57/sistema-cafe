
# ☕ Sistema de Gestión - Ingeniería de Software III

Este proyecto fue desarrollado como parte del **Parcial I** de la asignatura *Ingeniería de Software III*, cumpliendo con los requerimientos de diseño, desarrollo e implementación de un sistema funcional que incluye:

* Acceso al sistema (login)
* Recuperación de contraseña por correo electrónico
* CRUD de Perfiles, Personas y Usuarios
* Base de datos relacional en MySQL (modelada en Workbench)
* Validaciones y seguridad básica (CSRF, hash, token, etc.)

---

## 🛠 Tecnologías utilizadas

* PHP 8+
* MySQL 5.7+ (usando MySQL Workbench)
* PHPMailer (para envío de correo)
* HTML, CSS, JS (con AJAX para comunicación backend)
* XAMPP (Apache + MySQL local)

---

## 🚀 Instrucciones para ejecutar el sistema localmente

### 1. Clonar el repositorio

```bash
git clone https://github.com/tuusuario/sistema-cafe.git
```

### 2. Colocar el proyecto en el servidor local

* Copiar la carpeta en `C:/xampp/htdocs/`
* Acceder en navegador: `http://localhost/sistema-cafe`

### 3. Importar la base de datos

* Abrir **phpMyAdmin** o **MySQL Workbench**
* Crear una base de datos llamada `sistema`
* Ejecutar el script SQL (`sistema.sql`) con las tablas `usuario`, `persona`, `perfil`, `reset_tokens`

### 4. Configurar archivo `db.php`

```php
$host = "localhost";
$user = "root";
$password = "123456"; // tu contraseña de MySQL
$dbname = "sistema";
```

### 5. Configurar PHPMailer (`backend.php` o `recuperar.php`)

```php
$mail->Username = 'ventass12112@gmail.com';
$mail->Password = 'ieib jqhz btcr ogqd'; // contraseña de aplicación de Gmail
```

### 6. Probar funcionalidades

* Login con usuario existente
* Recuperar contraseña vía email
* Crear, editar y deshabilitar registros desde formularios CRUD

---

## 📸 Capturas del sistema

(agrega aquí imágenes si deseas o desde el video del proyecto)

---

## 👥 Desarrolladores

* Integrante 1: \[Tu Nombre]
* Integrante 2: \[Tu Nombre]
* Integrante 3: \[Tu Nombre]

---

## 📽 Video demostrativo

📺 Ver en YouTube: \[enlace al video aquí]
