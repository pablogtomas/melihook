<?php
// api_get_pagos.php
// Endpoint simple para devolver los registros de la tabla `pagos` en JSON.
// Colócalo en el mismo directorio donde está `config.php` para reutilizar la conexión PDO.

// --- CONFIGURACIÓN RÁPIDA ---
// Si quieres proteger el endpoint con una clave, define aquí un valor no vacío
// y llama al endpoint con ?key=TU_VALOR o usando el header X-API-KEY.
// Para producción, mueve la clave a una variable de entorno o a `config.php`.
define('API_READ_KEY', ''); // '' = deshabilitado (no requiere key)

// CORS mínimo (ajusta en producción)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Validar clave si está configurada
if (!empty(API_READ_KEY)) {
    $provided = null;
    if (isset($_GET['key'])) $provided = $_GET['key'];
    // leer header X-API-KEY (si se envía)
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (empty($provided) && isset($headers['X-API-KEY'])) $provided = $headers['X-API-KEY'];

    if ($provided !== API_READ_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

// Parámetros de paginación
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
$perPage = max(1, min(100, $perPage)); // límites para evitar overfetch
$offset = ($page - 1) * $perPage;

// Opcional: permitir filtro por id, fecha, etc. (añadir parámetros y WHERE seguro)

// Cargar configuración y obtener PDO
require_once __DIR__ . '/config.php';

try {
    $pdo = Config::getDB();

    // Contar total
    $countStmt = $pdo->query('SELECT COUNT(*) as cnt FROM pagos');
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Consultar registros paginados
    $stmt = $pdo->prepare('SELECT * FROM pagos ORDER BY id DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'meta' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / $perPage)
        ],
        'data' => $pagos
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
    exit;
}

?>
