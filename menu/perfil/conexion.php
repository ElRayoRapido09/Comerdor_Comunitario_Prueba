<?php
/**
 * Conexión a Neon PostgreSQL para módulo de perfil
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    die("Error de conexión a Neon PostgreSQL: " . $e->getMessage());
}

function obtenerPrimerUsuario($conn) {
    $stmt = $conn->query("SELECT * FROM usuarios LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>