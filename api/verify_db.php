<?php
/**
 * API de Verificación para Vercel - Comedor Comunitario
 * Endpoint: /api/verify_db.php - Verifica el estado de la base de datos después de la migración
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar estructura de la base de datos
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => 'vercel',
        'message' => 'Verificación completa de base de datos'
    ];
    
    // 1. Verificar conexión
    $response['connection'] = [
        'status' => 'connected',
        'driver' => $conn->getAttribute(PDO::ATTR_DRIVER_NAME),
        'version' => $conn->query('SELECT version()')->fetchColumn()
    ];
    
    // 2. Listar todas las tablas
    $stmt = $conn->query("
        SELECT schemaname, tablename 
        FROM pg_tables 
        WHERE schemaname = 'public' 
        ORDER BY tablename
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['database'] = [
        'name' => 'neondb',
        'tables_found' => count($tables),
        'tables' => array_column($tables, 'tablename'),
        'expected_tables' => [
            'asistencias', 'gastos', 'inventario', 'menu_platillos',
            'menus_dia', 'platillos', 'reportes', 'reservacion_platillos',
            'reservaciones', 'secciones_menu', 'usuarios'
        ]
    ];
    
    // 3. Estado general
    $expectedTables = 11;
    $foundTables = count($tables);
    
    if ($foundTables === $expectedTables) {
        $response['migration_status'] = 'complete';
        $response['ready_for_production'] = true;
    } else {
        $response['migration_status'] = 'incomplete';
        $response['ready_for_production'] = false;
        $response['missing_tables'] = $expectedTables - $foundTables;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => 'vercel',
        'message' => 'Error en verificación de base de datos'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
