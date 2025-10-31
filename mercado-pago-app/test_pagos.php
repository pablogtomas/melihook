<?php
require_once 'config.php';
require_once 'MercadoPagoHandler.php';
require_once 'DatabaseManager.php';

echo "<h1>🧪 TEST DE PAGOS MELI</h1>";

$mpHandler = new MercadoPagoHandler();

// Crear pago de prueba
echo "<h3>1. MELI TEST...</h3>";
$pago = $mpHandler->crearPagoPrueba(50.00, "Pago prueba MELI");

if ($pago) {
    echo "✅ Pago creado: {$pago['id']}<br>";
    echo "💰 Monto: {$pago['transaction_amount']}<br>";
    echo "📱 Estado: {$pago['status']}<br>";
    
    // Mostrar QR code si existe
    if (!empty($pago['point_of_interaction']['transaction_data']['qr_code'])) {
        echo "🔷 QR Code: {$pago['point_of_interaction']['transaction_data']['qr_code']}<br>";
    }
    
    // Guardar en base de datos
    echo "<h3>2. Guardando en base de datos...</h3>";
    if (DatabaseManager::guardarPago($pago)) {
        echo "✅ Pago guardado en BD<br>";
    } else {
        echo "❌ Error guardando en BD<br>";
    }
    
    // Verificar pago
    echo "<h3>3. Verificando estado del pago...</h3>";
    $pago_verificado = $mpHandler->verificarPago($pago['id']);
    if ($pago_verificado) {
        echo "✅ Estado actual: {$pago_verificado['status']}<br>";
    }
    
} else {
    echo "❌ Error creando pago<br>";
}
?>