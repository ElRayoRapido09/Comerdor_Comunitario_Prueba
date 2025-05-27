<?php
/**
 * Script de prueba de conexión a Neon PostgreSQL
 * Ejecutar este archivo para verificar que la conexión funciona correctamente
 */

header('Content-Type: application/json');

// Incluir la configuración de base de datos
require_once 'config/database.php';

try {
    echo "🔄 Probando conexión a Neon PostgreSQL...\n\n";
    
    // Crear instancia de Database
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Conexión exitosa!\n\n";
        
        // Probar una consulta simple
        $stmt = $conn->query("SELECT version() as version, current_database() as database, current_user as user");
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 Información de la base de datos:\n";
        echo "- Versión: " . $info['version'] . "\n";
        echo "- Base de datos: " . $info['database'] . "\n";
        echo "- Usuario: " . $info['user'] . "\n\n";
        
        // Verificar si las tablas existen
        $stmt = $conn->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            ORDER BY table_name
        ");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tablas) > 0) {
            echo "📋 Tablas encontradas en la base de datos:\n";
            foreach ($tablas as $tabla) {
                echo "- " . $tabla . "\n";
            }
        } else {
            echo "⚠️  No se encontraron tablas. Necesitas ejecutar el script de migración.\n";
        }
        
        $response = [
            'success' => true,
            'message' => 'Conexión exitosa a Neon PostgreSQL',
            'database_info' => $info,
            'tables' => $tablas,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } else {
        throw new Exception("No se pudo establecer la conexión");
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    
    echo "🔧 Posibles soluciones:\n";
    echo "1. Verificar que las credenciales de Neon sean correctas\n";
    echo "2. Asegurar que la extensión pdo_pgsql esté instalada en PHP\n";
    echo "3. Verificar que tu IP esté permitida en Neon (si tienes restricciones)\n";
    echo "4. Comprobar la conectividad a Internet\n\n";
    
    $response = [
        'success' => false,
        'message' => 'Error de conexión: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

echo "\n" . json_encode($response, JSON_PRETTY_PRINT) . "\n";
?>
