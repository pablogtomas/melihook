<?php
echo "<h1>🔍 VERIFICANDO ESTRUCTURA DE CARPETAS</h1>";

$base_path = __DIR__ . '/vendor/mercadopago/dx-php';

echo "Ruta base: $base_path<br><br>";

function listarEstructura($dir, $nivel = 0) {
    if (!is_dir($dir)) {
        echo "❌ No es un directorio: $dir<br>";
        return;
    }
    
    $items = scandir($dir);
    $output = "";
    
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $ruta = $dir . '/' . $item;
        $sangria = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $nivel);
        
        if (is_dir($ruta)) {
            $output .= $sangria . "📁 <strong>$item/</strong><br>";
            $output .= listarEstructura($ruta, $nivel + 1);
        } else {
            $icon = (strpos($item, 'SDK.php') !== false) ? "🎯" : "📄";
            $output .= $sangria . "$icon $item<br>";
        }
    }
    
    return $output;
}

echo listarEstructura($base_path);

// Verificar SDK específicamente
echo "<h3>🎯 Buscando SDK.php...</h3>";
$sdk_candidates = [
    'sdk-php/MercadoPago/SDK.php',
    'sdk-php/SDK.php', 
    'src/MercadoPago/SDK.php',
    'src/SDK.php'
];

foreach ($sdk_candidates as $candidate) {
    $ruta = $base_path . '/' . $candidate;
    $existe = file_exists($ruta);
    $status = $existe ? '✅' : '❌';
    echo "$status $candidate<br>";
    
    if ($existe) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;📍 Ruta completa: $ruta<br>";
    }
}
?>