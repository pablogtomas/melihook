<?php
// Cargar autoload de Composer
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    die("❌ Error: vendor/autoload.php no encontrado");
}

// Verificar clases del SDK
if (!class_exists('MercadoPago\MercadoPagoConfig')) {
    die("❌ Error: Clase MercadoPagoConfig no disponible");
}

if (!class_exists('MercadoPago\Client\Payment\PaymentClient')) {
    die("❌ Error: Clase PaymentClient no disponible");
}

use MercadoPago\MercadoPagoConfig;

class Config {
    const DB_HOST = '66.97.43.58';
    const DB_NAME = 'mercado_pago_db';
    const DB_USER = 'gaston';
    const DB_PASS = 'campo40164234';

    const MP_ACCESS_TOKEN = 'APP_USR-5726001139090018-082008-c9a942225ec19ee0ea666d2a1dc236d5-188036360';

    public static function getDB() {
        try {

            // OJO: quitar charset del DSN (bug PDO)
            $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME;

            // CONFIGURACIÓN CORRECTA UTF8MB4
            $pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",   // <-- la clave
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            return $pdo;

        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function initMercadoPago() {
        MercadoPagoConfig::setAccessToken(self::MP_ACCESS_TOKEN);
    }
}

Config::initMercadoPago();

// Debug
error_log("✅ MercadoPago configurado correctamente con token: " . substr(Config::MP_ACCESS_TOKEN, 0, 12) . "...");
