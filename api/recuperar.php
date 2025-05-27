<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir configuración
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../recuperacion/config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $correo = filter_var(trim($data['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
    
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Correo electrónico no válido');
    }
    
    // Verificar si el correo existe
    $stmt = $conn->prepare("SELECT nombre, apellidos FROM usuarios WHERE correo = :correo AND activo = TRUE");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Generar token de recuperación
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos (necesitarás crear esta tabla)
        $stmt = $conn->prepare("INSERT INTO tokens_recuperacion (correo, token, expiracion) VALUES (:correo, :token, :expiracion) ON CONFLICT (correo) DO UPDATE SET token = :token, expiracion = :expiracion");
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiracion', $expiracion);
        $stmt->execute();
        
        // Crear enlace de recuperación
        $enlace = "https://tu-dominio-vercel.vercel.app/recuperacion/restablecer.php?token=" . $token;
        
        // Crear mensaje de correo
        $asunto = "Recuperación de contraseña - Comedor Comunitario";
        $mensaje = "
        <h2>Recuperación de Contraseña</h2>
        <p>Hola {$usuario['nombre']} {$usuario['apellidos']},</p>
        <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
        <p><a href='$enlace'>Restablecer Contraseña</a></p>
        <p>Este enlace expirará en 1 hora.</p>
        <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
        ";
        
        // Enviar correo
        if (enviarCorreo($correo, $asunto, $mensaje)) {
            echo json_encode([
                'success' => true,
                'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico'
            ]);
        } else {
            throw new Exception('Error al enviar el correo electrónico');
        }
        
    } else {
        // Por seguridad, no revelar si el correo existe o no
        echo json_encode([
            'success' => true,
            'message' => 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en recuperar: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
