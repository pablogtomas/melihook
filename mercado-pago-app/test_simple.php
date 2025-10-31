<?php
require_once 'config.php';

echo "<h1>🧪 TEST SDK MERCADO PAGO - VERSIÓN CORRECTA</h1>";

try {
    // 1. Verificar configuración
    echo "<h3>1. Verificando configuración...</h3>";
    $access_token = MercadoPago\MercadoPagoConfig::getAccessToken();
    echo "✅ Access Token CONFIGURADO: " . substr($access_token, 0, 15) . "...<br>";
    
    // 2. Verificar clases
    echo "<h3>2. Verificando clases...</h3>";
    if (class_exists('MercadoPago\Client\Payment\PaymentClient')) {
        echo "✅ PaymentClient DISPONIBLE<br>";
    } else {
        echo "❌ PaymentClient NO DISPONIBLE<br>";
    }
    
    if (class_exists('MercadoPago\Client\Preference\PreferenceClient')) {
        echo "✅ PreferenceClient DISPONIBLE<br>";
    }
    
    // 3. Probar crear cliente
    echo "<h3>3. Probando cliente de pagos...</h3>";
    $paymentClient = new MercadoPago\Client\Payment\PaymentClient();
    echo "✅ PaymentClient INSTANCIADO<br>";
    
    // 4. Información del SDK
    echo "<h3>4. Información del SDK...</h3>";
    echo "Versión PHP: " . PHP_VERSION . "<br>";
    echo "Directorio: " . __DIR__ . "<br>";
    
    echo "<h2 style='color: green;'>🎉 ¡SDK DE MERCADO PAGO FUNCIONANDO CORRECTAMENTE!</h2>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ ERROR:</h3>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
}
?>