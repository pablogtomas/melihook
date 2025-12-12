<?php
require_once 'config.php';
require_once 'MercadoPagoHandler.php';
require_once 'DatabaseManager.php';

error_log("🔔 Webhook/IPN llamado: " . date('Y-m-d H:i:s'));

// Leer JSON del body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si no hay JSON, usar GET (modo IPN legacy)
if (empty($data)) {
    $data = $_GET;
}

$payment_id = null;

// Formato moderno (webhook JSON)
if (isset($data['type']) && $data['type'] === 'payment' && isset($data['data']['id'])) {
    $payment_id = $data['data']['id'];
}
// Formato legacy (?topic=payment&id=123456)
elseif (isset($_GET['topic']) && $_GET['topic'] === 'payment' && isset($_GET['id'])) {
    $payment_id = $_GET['id'];
}
// Fallback genérico
elseif (isset($data['id']) && is_numeric($data['id'])) {
    $payment_id = $data['id'];
}

// Validación
if (!$payment_id) {
    error_log("❌ Datos inválidos o sin payment_id: " . print_r($data, true));
    http_response_code(400);
    exit("Datos inválidos");
}


if ($payment_id == '123456') {
    error_log("🧪 Prueba IPN recibida correctamente (id=123456)");
    http_response_code(200);
    echo "Test OK";
    exit;
}

error_log("🔄 Procesando pago con ID: {$payment_id}");


$url = "https://api.mercadopago.com/v1/payments/" . $payment_id;
$headers = [
   "Authorization: Bearer " . getenv('MP_ACCESS_TOKEN'),

    "Content-Type: application/json"
];

$context = stream_context_create([
    'http' => [
        'header' => implode("\r\n", $headers),
        'method' => 'GET'
    ]
]);

$response = file_get_contents($url, false, $context);

// Guardar respuesta cruda para debugging
file_put_contents(__DIR__ . '/mp_debug.log', "[" . date('Y-m-d H:i:s') . "] Pago ID {$payment_id}: " . $response . PHP_EOL, FILE_APPEND);

// Parsear respuesta JSON
$pago = json_decode($response, true);

if (!empty($pago['id'])) {
    error_log("📊 Pago {$pago['id']} recibido con estado: {$pago['status']}");

    // Guardar o actualizar en la BD
    if (DatabaseManager::pagoExiste($pago['id'])) {
        DatabaseManager::actualizarEstadoPago($pago['id'], $pago['status']);
        error_log("✅ Pago {$pago['id']} actualizado a: {$pago['status']}");
    } else {
        DatabaseManager::guardarPago($pago);
        error_log("📥 Pago {$pago['id']} guardado en BD");
    }

    http_response_code(200);
    echo "OK";
} else {
    error_log("⚠️ No se pudo obtener información del pago ID {$payment_id}");
    http_response_code(400);
    echo "Error al consultar pago";
}
?>
