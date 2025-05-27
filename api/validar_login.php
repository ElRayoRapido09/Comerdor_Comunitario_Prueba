<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir configuración de Neon PostgreSQL
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('No se recibieron datos');
    }
    
    $correo = $data['correo'] ?? '';
    $contrasena = $data['contrasena'] ?? '';
    $tipoUsuarioEsperado = $data['tipoUsuario'] ?? '';
    
    if (empty($correo) || empty($contrasena) || empty($tipoUsuarioEsperado)) {
        throw new Exception('Datos incompletos');
    }
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :correo AND activo = TRUE");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificación para SHA-256
        $hashIngresado = hash('sha256', $contrasena);
        
        if ($hashIngresado === $usuario['contrasena']) {
            if ($usuario['tipo_usuario'] === $tipoUsuarioEsperado || 
                ($tipoUsuarioEsperado === 'empleado' && $usuario['tipo_usuario'] === 'admin')) {
                
                // Simulación de sesión para API
                $sessionData = [
                    'id_usuario' => $usuario['id_usuario'],
                    'nombre' => $usuario['nombre'],
                    'apellidos' => $usuario['apellidos'],
                    'correo' => $usuario['correo'],
                    'tipo_usuario' => $usuario['tipo_usuario']
                ];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'usuario' => $sessionData,
                    'redirect' => $tipoUsuarioEsperado === 'beneficiario' ? '/menu/menu.php' : '/empleado/empleado.php'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Tipo de usuario incorrecto'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en validar_login: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con el servidor: ' . $e->getMessage()
    ]);
}
?>
