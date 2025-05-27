<?php
/**
 * Configuración de conexión a la base de datos Neon PostgreSQL
 * Compatible con Vercel y variables de entorno
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Configurar desde variables de entorno (Vercel) o valores por defecto
        $this->host = $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech';
        $this->db_name = $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? 'neondb';
        $this->username = $_ENV['PGUSER'] ?? $_SERVER['PGUSER'] ?? 'neondb_owner';
        $this->password = $_ENV['PGPASSWORD'] ?? $_SERVER['PGPASSWORD'] ?? 'npg_hyng4Q2aGNdP';
        $this->port = $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? '5432';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // Intentar primero con la URL completa si está disponible
            $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
              if ($database_url) {
                $this->conn = new PDO($database_url, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                // Fallback a parámetros individuales con endpoint ID para Neon
                $endpoint_id = "ep-noisy-bush-a4xycth8"; // Extraído del hostname
                $dsn = "pgsql:host=" . $this->host . 
                       ";port=" . $this->port . 
                       ";dbname=" . $this->db_name . 
                       ";sslmode=require" .
                       ";options=endpoint=" . $endpoint_id;
                
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
            
        } catch(PDOException $exception) {
            error_log("Error de conexión a Neon PostgreSQL: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $exception->getMessage());
        }

        return $this->conn;
    }

    /**
     * Método alternativo usando la URL completa de conexión
     */
    public function getConnectionFromUrl() {
        $this->conn = null;
        
        try {
            // URL de conexión completa de Neon
            $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? 
                           'postgres://neondb_owner:npg_hyng4Q2aGNdP@ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require';
            
            $this->conn = new PDO($database_url, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
        } catch(PDOException $exception) {
            error_log("Error de conexión a Neon PostgreSQL via URL: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $exception->getMessage());
        }

        return $this->conn;
    }

    /**
     * Verifica si la conexión está activa
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT version()");
            $version = $stmt->fetchColumn();
            return [
                'success' => true,
                'message' => 'Conexión exitosa',
                'version' => $version
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener información de configuración (para debugging)
     */
    public function getConfig() {
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'port' => $this->port,
            'password_set' => !empty($this->password),
            'env_database_url_set' => !empty($_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL']),
        ];
    }
}

/**
 * Función helper para obtener conexión rápida
 */
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

/**
 * Variables de entorno para compatibilidad con código legacy
 */
define('DB_HOST', $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech');
define('DB_NAME', $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? 'neondb');
define('DB_USER', $_ENV['PGUSER'] ?? $_SERVER['PGUSER'] ?? 'neondb_owner');
define('DB_PASS', $_ENV['PGPASSWORD'] ?? $_SERVER['PGPASSWORD'] ?? 'npg_hyng4Q2aGNdP');
define('DB_PORT', $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? '5432');
define('DATABASE_URL', $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? 'postgres://neondb_owner:npg_hyng4Q2aGNdP@ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech/neondb?sslmode=require');

/**
 * Configuración alternativa optimizada para Vercel
 */
class DatabaseConfig {
    public static function getConnection() {
        // Obtener variables de entorno de Vercel
        $database_url = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
        
        if ($database_url) {
            // Usar la URL completa si está disponible
            try {
                $conn = new PDO($database_url, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30
                ]);
                return $conn;
            } catch (PDOException $e) {
                error_log("Error con DATABASE_URL: " . $e->getMessage());
            }
        }
        
        // Fallback a parámetros individuales
        $host = getenv('PGHOST') ?: $_ENV['PGHOST'] ?? $_SERVER['PGHOST'] ?? 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech';
        $dbname = getenv('PGDATABASE') ?: $_ENV['PGDATABASE'] ?? $_SERVER['PGDATABASE'] ?? 'neondb';
        $user = getenv('PGUSER') ?: $_ENV['PGUSER'] ?? $_SERVER['PGUSER'] ?? 'neondb_owner';
        $password = getenv('PGPASSWORD') ?: $_ENV['PGPASSWORD'] ?? $_SERVER['PGPASSWORD'] ?? 'npg_hyng4Q2aGNdP';
        $port = getenv('PGPORT') ?: $_ENV['PGPORT'] ?? $_SERVER['PGPORT'] ?? '5432';
        
        $endpoint_id = "ep-noisy-bush-a4xycth8";
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require;options=endpoint={$endpoint_id}";
        
        try {
            $conn = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30
            ]);
            return $conn;
        } catch (PDOException $e) {
            error_log("Error de conexión PostgreSQL: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
?>