<?php
require_once 'config.php';
require_once 'MercadoPagoHandler.php';
require_once 'DatabaseManager.php';

// Log para debugging
error_log("🔔 Webhook llamado: " . date('Y-m-d H:i:s'));

// Leer input JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("📦 Datos recibidos: " . print_r($data, true));

if (isset($data['type']) && $data['type'] == 'payment' && isset($data['data']['id'])) {
    $payment_id = $data['data']['id'];
    
    error_log("🔄 Procesando pago: " . $payment_id);
    
    $mpHandler = new MercadoPagoHandler();
    $pago_actualizado = $mpHandler->verificarPago($payment_id);
    
    if ($pago_actualizado) {
        error_log("📊 Estado del pago {$payment_id}: " . $pago_actualizado['status']);
        
        // Si el pago existe en BD, actualizar estado
        if (DatabaseManager::pagoExiste($payment_id)) {
            DatabaseManager::actualizarEstadoPago($payment_id, $pago_actualizado['status']);
            error_log("✅ Pago {$payment_id} actualizado a: " . $pago_actualizado['status']);
        } else {
            // Si no existe, guardarlo
            DatabaseManager::guardarPago($pago_actualizado);
            error_log("📥 Pago {$payment_id} guardado en BD");
        }
        
        http_response_code(200);
        echo "OK";
        
    } else {
        error_log("❌ No se pudo verificar pago: " . $payment_id);
        http_response_code(400);
    }
    
} else {
    error_log("❌ Datos de webhook inválidos");
    http_response_code(400);
    echo "Datos inválidos";
}
?>