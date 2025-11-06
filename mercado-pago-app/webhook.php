<?php
require_once 'config.php';
require_once 'MercadoPagoHandler.php';
require_once 'DatabaseManager.php';

// Log inicial
error_log("🔔 Webhook/IPN llamado: " . date('Y-m-d H:i:s'));

// Leer input JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si no hay JSON, usar $_GET (modo IPN legacy)
if (empty($data)) {
    $data = $_GET;
}

$payment_id = null;

// ✅ Formato moderno (Webhook JSON con POST)
if (isset($data['type']) && $data['type'] === 'payment' && isset($data['data']['id'])) {
    $payment_id = $data['data']['id'];
}
// ✅ Formato legacy IPN (?topic=payment&id=123456)
elseif (isset($data['topic']) && $data['topic'] === 'payment' && isset($data['id'])) {
    $payment_id = $data['id'];
}

elseif (isset($data['id']) && is_numeric($data['id'])) {
    $payment_id = $data['id'];
}

// Si no se detectó un payment_id válido
if (!$payment_id) {
    error_log("❌ Datos de webhook/IPN inválidos o sin payment_id: " . print_r($data, true));
    http_response_code(400);
    exit("Datos inválidos");
}

error_log("🔄 Procesando pago: " . $payment_id);

// Instanciar el manejador de Mercado Pago
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
