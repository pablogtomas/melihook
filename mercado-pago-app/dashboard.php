<?php
require_once 'DatabaseManager.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Dashboard - MercadoPago</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { background: #f5f5f5; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .approved { color: green; }
        .pending { color: orange; }
        .rejected { color: red; }
    </style>
</head>
<body>
    <h1>📊 Dashboard de Pagos</h1>";

// Obtener estadísticas
$stats = DatabaseManager::obtenerEstadisticas();

echo "<div class='card'>
    <h3>📈 Estadísticas</h3>
    <p>Total de pagos: <strong>{$stats['total_pagos']}</strong></p>
    <p>Pagos aprobados: <strong>{$stats['pagos_aprobados']}</strong></p>
    <p>Total recaudado: <strong>\${$stats['total_recaudado']}</strong></p>
</div>";

// Obtener últimos pagos
$pagos = DatabaseManager::obtenerTodosLosPagos(20);

echo "<h3>🕒 Últimos Pagos</h3>";
foreach ($pagos as $pago) {
    $status_class = $pago['status'];
    echo "<div class='card'>
        <p><strong>ID:</strong> {$pago['payment_id']}</p>
        <p><strong>Estado:</strong> <span class='{$status_class}'>{$pago['status']}</span></p>
        <p><strong>Monto:</strong> \${$pago['amount']} {$pago['currency']}</p>
        <p><strong>Email:</strong> {$pago['payer_email']}</p>
        <p><strong>Fecha:</strong> {$pago['created_at']}</p>
    </div>";
}

echo "</body>
</html>";
?>