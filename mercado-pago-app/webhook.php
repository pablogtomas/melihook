<?php
require_once 'config.php';
require_once 'MercadoPagoHandler.php';
require_once 'DatabaseManager.php';

// Log para debugging
error_log("🔔 Webhook llamado: " . date('Y-m-d H:i:s'));

// Leer input JSON (formato actual)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si el JSON está vacío, usar $_GET como fallback (modo legacy)
if (!$data || !isset($data['data']['id'])) {
    $data = $_GET;
}

// Detectar ID de pago en ambos formatos posibles
$payment_id = null;
if (isset($data['type']) && $data['type'] === 'payment') {
    if (isset($data['data']['id'])) {
        $payment_id = $data['data']['id']; // Formato nuevo (API v2)
    } elseif (isset($data['id'])) {
        $payment_id = $data['id']; // Formato legacy (Point Smart o IPN)
    }
}

// Si no se detectó un payment_id válido
if (!$payment_id) {
    error_log("❌ Datos de webhook inválidos o sin payment_id: " . print_r($data, true));
    http_response_code(400);
    exit("Datos inválidos");
}

error_log("🔄 Procesando pago: " . $payment_id);

$mpHandler = new MercadoPagoHandler();
$pago_actualizado = $mpHandler->verificarPago($payment_id);

if ($pago_actualizado) {
    error_log("📊 Estado del pago {$payment_id}: " . $pago_actualizado['status']);

    // Si el pago ya existe en la BD → actualizar estado
    if (DatabaseManager::pagoExiste($payment_id)) {
        DatabaseManager::actualizarEstadoPago($payment_id, $pago_actualizado['status']);
        error_log("✅ Pago {$payment_id} actualizado a: " . $pago_actualizado['status']);
    } else {
        // Si no existe → guardar nuevo registro
        DatabaseManager::guardarPago($pago_actualizado);
        error_log("📥 Pago {$payment_id} guardado en BD");
    }

    http_response_code(200);
    echo "OK";
} else {
    error_log("❌ No se pudo verificar pago {$payment_id}");
    http_response_code(400);
    echo "Error verificando pago";
}
?>
