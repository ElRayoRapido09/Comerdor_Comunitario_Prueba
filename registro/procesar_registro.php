<?php
// Limpiar buffers de salida
while (ob_get_level()) ob_end_clean();

// Configurar cabeceras
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Opcional para desarrollo

// Incluir configuración de Neon PostgreSQL
require_once '../config/database.php';

// Respuesta JSON
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '../inicio/index.html' // Ruta absoluta recomendada
];

try {
    // 1. Validar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Solo se permiten peticiones POST", 405);
    }

    // 2. Validar campos obligatorios
    $requiredFields = ['nombre', 'apellidos', 'direccion', 'edad', 'sexo', 'contrasena', 'terminos'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo '$field' es obligatorio", 400);
        }
        }

    // 3. Validar términos
    if ($_POST['terminos'] !== 'on') {
        throw new Exception("Debes aceptar los términos y condiciones", 400);
    }

    // 4. Validar edad
    $edad = filter_var($_POST['edad'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 120]
    ]);
    if ($edad === false) {
        throw new Exception("La edad debe ser entre 1 y 120 años", 400);
    }

    // 5. Conectar a la base de datos PostgreSQL
    $database = new Database();
    $conn = $database->getConnection();

    // 6. Preparar INSERT (con todos los campos)
        // 6. Preparar consulta para PostgreSQL
    $stmt = $conn->prepare("
        INSERT INTO usuarios (
            nombre, 
            apellidos, 
            direccion, 
            edad, 
            sexo, 
            correo, 
            contrasena, 
            fecha_registro
        ) VALUES (:nombre, :apellidos, :direccion, :edad, :sexo, :correo, 
                 ENCODE(DIGEST(:contrasena, 'sha256'), 'hex'), CURRENT_TIMESTAMP)
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta", 500);
    }

    $correo = !empty($_POST['correo']) ? $_POST['correo'] : NULL;

    // 7. Ejecutar consulta con parámetros nombrados
    $result = $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':apellidos' => $_POST['apellidos'],
        ':direccion' => $_POST['direccion'],
        ':edad' => $edad,
        ':sexo' => $_POST['sexo'],
        ':correo' => $correo,
        ':contrasena' => $_POST['contrasena']
    ]);

    // 8. Ejecutar consulta
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "¡Registro exitoso!";
    } else {
        throw new Exception("Error al guardar: " . $stmt->error, 500);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 500);
} finally {
    // 9. Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

// 10. Enviar respuesta
echo json_encode($response);
exit;
?>