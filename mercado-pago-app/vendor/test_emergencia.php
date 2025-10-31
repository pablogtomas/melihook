<?php
echo "<h1>🚨 TEST DE EMERGENCIA</h1>";

// 1. Verificar que estamos en la carpeta correcta
echo "<h3>1. Verificando ubicación...</h3>";
echo "Directorio: " . __DIR__ . "<br>";
echo "Archivo: " . __FILE__ . "<br>";

// 2. Verificar vendor/autoload.php
echo "<h3>2. Verificando vendor/autoload.php...</h3>";
$autoload_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo "✅ vendor/autoload.php EXISTE<br>";
    
    // Mostrar tamaño
    echo "Tamaño: " . filesize($autoload_path) . " bytes<br>";
    
    // Intentar cargar
    require_once $autoload_path;
    echo "✅ vendor/autoload.php CARGADO<br>";
} else {
    echo "❌ vendor/autoload.php NO EXISTE<br>";
    echo "Buscado en: " . $autoload_path . "<br>";
}

// 3. Verificar si vendor/mercadopago existe
echo "<h3>3. Verificando vendor/mercadopago...</h3>";
$mp_path = __DIR__ . '/vendor/mercadopago';
if (is_dir($mp_path)) {
    echo "✅ vendor/mercadopago EXISTE<br>";
    
    // Listar contenido
    $items = scandir($mp_path);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            echo "• $item<br>";
        }
    }
} else {
    echo "❌ vendor/mercadopago NO EXISTE<br>";
}

// 4. Verificar clases manualmente
echo "<h3>4. Verificando clases manualmente...</h3>";
$sdk_path = __DIR__ . '/vendor/mercadopago/dx-php/src/MercadoPago/SDK.php';
if (file_exists($sdk_path)) {
    echo "✅ SDK.php EXISTE<br>";
    
    // Cargar manualmente
    require_once $sdk_path;
    echo "✅ SDK.php CARGADO MANUALMENTE<br>";
    
    // Verificar clase
    if (class_exists('MercadoPago\SDK')) {
        echo "✅ Clase MercadoPago\SDK DISPONIBLE<br>";
        
        // Probar
        MercadoPago\SDK::setAccessToken("TEST-123");
        echo "✅ SDK CONFIGURADO<br>";
    } else {
        echo "❌ Clase MercadoPago\SDK NO DISPONIBLE<br>";
    }
} else {
    echo "❌ SDK.php NO EXISTE<br>";
    echo "Buscado en: " . $sdk_path . "<br>";
}

echo "<h2>🎯 DIAGNÓSTICO COMPLETO</h2>";
?>