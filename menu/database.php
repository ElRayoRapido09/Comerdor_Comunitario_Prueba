<?php
/**
 * Configuración de base de datos para Neon PostgreSQL
 * Compatible con Vercel deployment
 */
class Database {
    private $host = 'ep-noisy-bush-a4xycth8-pooler.us-east-1.aws.neon.tech';
    private $db_name = 'neondb';
    private $username = 'neondb_owner';
    private $password = 'npg_hyng4Q2aGNdP';
    private $port = '5432';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // DSN para PostgreSQL con SSL
            $dsn = "pgsql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";sslmode=require";
                   
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }

        return $this->conn;
    }
}
?>