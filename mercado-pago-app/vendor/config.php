<?php
// Cargar autoload de Composer
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    die("❌ Error: vendor/autoload.php no encontrado");
}

// Verificar que la clase SDK existe
if (!class_exists('MercadoPago\SDK')) {
    die("❌ Error: Clase MercadoPago\SDK no disponible");
}

use MercadoPago\SDK;

class Config {
    const DB_HOST = 'localhost';
    const DB_NAME = 'mercado_pago_db';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    // Configuración MercadoPago - CREDENCIALES DE PRUEBA
    const MP_ACCESS_TOKEN = 'TEST-489689800042269-091923-6a5c714b5f4d5ba7d66c4119210923f6-185928508';
    
    public static function getDB() {
        try {
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8";
            $pdo = new PDO($dsn, self::DB_USER, self::DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public static function initMercadoPago() {
        // Inicializar SDK de MercadoPago
        SDK::setAccessToken(self::MP_ACCESS_TOKEN);
    }
}

// Inicializar MercadoPago
Config::initMercadoPago();
?>