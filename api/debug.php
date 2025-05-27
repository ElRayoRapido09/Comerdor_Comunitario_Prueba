<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Archivo de debug para verificar la configuración en Vercel
$debug_info = [
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'environment_variables' => [
        'DATABASE_URL' => $_ENV['DATABASE_URL'] ?? 'NOT_SET',
        'PGHOST' => $_ENV['PGHOST'] ?? 'NOT_SET',
        'PGUSER' => $_ENV['PGUSER'] ?? 'NOT_SET',
        'PGDATABASE' => $_ENV['PGDATABASE'] ?? 'NOT_SET',
        'PGPORT' => $_ENV['PGPORT'] ?? 'NOT_SET'
    ],
    'server_variables' => [
        'DATABASE_URL' => $_SERVER['DATABASE_URL'] ?? 'NOT_SET',
        'PGHOST' => $_SERVER['PGHOST'] ?? 'NOT_SET',
        'PGUSER' => $_SERVER['PGUSER'] ?? 'NOT_SET',
        'PGDATABASE' => $_SERVER['PGDATABASE'] ?? 'NOT_SET',
        'PGPORT' => $_SERVER['PGPORT'] ?? 'NOT_SET'
    ],
    'pdo_extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql')
    ],
    'current_directory' => __DIR__,
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'
];

// Intentar conexión
try {
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    $debug_info['database_connection'] = 'SUCCESS';
    
    // Probar una consulta simple
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetchColumn();
    $debug_info['database_version'] = $version;
    
} catch (Exception $e) {
    $debug_info['database_connection'] = 'FAILED';
    $debug_info['database_error'] = $e->getMessage();
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>
