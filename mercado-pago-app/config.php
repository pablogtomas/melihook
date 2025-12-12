<?php

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("❌ Error: archivo .env no encontrado");
}

$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) continue;
    putenv($line);
}


$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    die("❌ Error: vendor/autoload.php no encontrado");
}


if (!class_exists('MercadoPago\MercadoPagoConfig')) {
    die("❌ Error: Clase MercadoPagoConfig no disponible");
}

if (!class_exists('MercadoPago\Client\Payment\PaymentClient')) {
    die("❌ Error: Clase PaymentClient no disponible");
}

use MercadoPago\MercadoPagoConfig;

class Config {

    public static function getDB() {
        try {
            $dsn = "mysql:host=" . getenv('DB_HOST') .
                   ";dbname=" . getenv('DB_NAME');

            $pdo = new PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASS'),
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            return $pdo;

        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public static function initMercadoPago() {
        MercadoPagoConfig::setAccessToken(getenv('MP_ACCESS_TOKEN'));
    }
}

Config::initMercadoPago();

// Log seguro (sin token)
error_log("✅ MercadoPago configurado correctamente (.env)");

