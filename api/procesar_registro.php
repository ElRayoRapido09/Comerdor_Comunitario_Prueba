<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Incluir configuración de base de datos
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        // Intentar obtener datos de POST form
        $data = $_POST;
    }
    
    if (empty($data)) {
        throw new Exception('No se recibieron datos');
    }
    
    // Recoger y sanitizar los datos del formulario
    $nombre = htmlspecialchars(trim($data['nombre'] ?? ''));
    $apellidos = htmlspecialchars(trim($data['apellidos'] ?? ''));
    $direccion = htmlspecialchars(trim($data['direccion'] ?? ''));
    $edad = intval($data['edad'] ?? 0);
    $correo = filter_var(trim($data['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
    $contrasena = $data['contrasena'] ?? '';
    $sexo = htmlspecialchars(trim($data['sexo'] ?? ''));
    
    // Validar los datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es requerido";
    }
    
    if (empty($apellidos)) {
        $errores[] = "Los apellidos son requeridos";
    }
    
    if (empty($direccion)) {
        $errores[] = "La dirección es requerida";
    }
    
    if ($edad < 18) {
        $errores[] = "Debes ser mayor de 18 años";
    }
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (strlen($contrasena) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if (!in_array($sexo, ['masculino', 'femenino', 'otro'])) {
        $errores[] = "Debe seleccionar un sexo válido";
    }
    
    // Si no hay errores, insertar en la base de datos
    if (empty($errores)) {
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errores[] = "El correo electrónico ya está registrado";
        } else {
            // Hash de la contraseña con SHA-256
            $contrasenaHash = hash('sha256', $contrasena);
            $tipo_usuario = "beneficiario";
            
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, correo, direccion, edad, sexo, contrasena, tipo_usuario, activo, fecha_registro) VALUES (:nombre, :apellidos, :correo, :direccion, :edad, :sexo, :contrasena, :tipo_usuario, TRUE, CURRENT_TIMESTAMP)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellidos', $apellidos);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':edad', $edad, PDO::PARAM_INT);
            $stmt->bindParam(':sexo', $sexo);
            $stmt->bindParam(':contrasena', $contrasenaHash);
            $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado correctamente'
                ]);
            } else {
                throw new Exception("Error al registrar el usuario");
            }
        }
    }
    
    // Si hay errores, devolverlos
    if (!empty($errores)) {
        echo json_encode([
            'success' => false,
            'errors' => $errores
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en procesar_registro: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
