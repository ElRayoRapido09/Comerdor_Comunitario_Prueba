<?php
/**
 * Conexión a Neon PostgreSQL para módulo de empleados/escanear
 */
$host = 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech';
$dbname = 'neondb';
$username = 'neondb_owner';
$password = 'npg_hyng4Q2aGNdP';
$port = '5432';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos Neon PostgreSQL',
        'error' => $e->getMessage()
    ]));
}
?>