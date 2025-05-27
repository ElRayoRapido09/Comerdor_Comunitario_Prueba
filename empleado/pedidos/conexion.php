<?php
/**
 * Conexión a Neon PostgreSQL para módulo de empleados/pedidos
 */
try {
    $host = 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech';
    $dbname = 'neondb';
    $username = 'neondb_owner';
    $password = 'npg_hyng4Q2aGNdP';
    $port = '5432';

    // DSN para PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
} catch (PDOException $e) {
    error_log("Error de conexión PostgreSQL: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}

// Función helper para convertir resultado de mysqli a formato esperado
function mysqli_to_pdo_result($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    return $stmt;
}
?>