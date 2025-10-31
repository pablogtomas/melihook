<?php
echo "<h1>🧪 TEST SIMPLE SDK</h1>";

// Verificar vendor/autoload.php
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "✅ vendor/autoload.php EXISTE<br>";
    require_once $autoload_path;
    echo "✅ vendor/autoload.php CARGADO<br>";
} else {
    echo "❌ vendor/autoload.php NO EXISTE<br>";
    exit;
}

// Verificar clase SDK
if (class_exists('MercadoPago\SDK')) {
    echo "✅ Clase MercadoPago\SDK ENCONTRADA<br>";
    
    // Probar configuración
    try {
        MercadoPago\SDK::setAccessToken("TEST-123");
        echo "✅ SDK CONFIGURADO<br>";
        
        // Mostrar versión
        echo "Versión SDK: " . MercadoPago\SDK::VERSION . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error configurando SDK: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Clase MercadoPago\SDK NO ENCONTRADA<br>";
}

// Mostrar información del sistema
echo "<h3>🔧 Sistema:</h3>";
echo "PHP: " . PHP_VERSION . "<br>";
echo "Directorio: " . __DIR__ . "<br>";
?>