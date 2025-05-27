<?php
// config.php
// PostgreSQL database configuration for Neon
define('DB_HOST', getenv('DB_HOST') ?: 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech');
define('DB_USER', getenv('DB_USER') ?: 'neondb_owner');
define('DB_PASS', getenv('DB_PASS') ?: 'npg_t5LQpPJkNnm2bRTKJ4dBZHs7vcftR9HG');
define('DB_NAME', getenv('DB_NAME') ?: 'neondb');
define('DB_PORT', getenv('DB_PORT') ?: '5432');

// Configuración para el correo electrónico
define('MAIL_HOST', 'smtp.gmail.com'); // Cambia según tu proveedor
define('MAIL_USER', 'kawiim98@gmail.com');
define('MAIL_PASS', 'ysju mioa oiyn jder'); // Usa contraseña de aplicación para Gmail
define('MAIL_PORT', 587);
define('MAIL_FROM', 'kawiim98@gmail.com');
define('MAIL_FROM_NAME', 'Comedor Comunitario');

// Conexión a la base de datos PostgreSQL
function conectarDB() {
    try {
        $endpoint_id = "ep-noisy-bush-a4xycth8"; // Extraído del hostname de Neon
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require;options=endpoint=" . $endpoint_id;
        $conn = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $conn;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para enviar correos
function enviarCorreo($destinatario, $asunto, $cuerpo) {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        
        // Remitente y destinatario
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($destinatario);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}
?>