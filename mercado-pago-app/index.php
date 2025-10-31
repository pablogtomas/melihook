<?php
echo "<h1>🏠 PÁGINA PRINCIPAL - MercadoPago App</h1>";
echo "<p>Sistema de pagos QR con MercadoPago</p>";

echo "<h3>🔗 Enlaces de prueba:</h3>";
echo "<ul>";
echo "<li><a href='test_simple.php'>Test Simple SDK</a></li>";
echo "<li><a href='test_completo.php'>Test Completo Sistema</a></li>";
echo "</ul>";

echo "<h3>📁 Estructura de archivos:</h3>";
$archivos = scandir(__DIR__);
foreach ($archivos as $archivo) {
    if ($archivo != '.' && $archivo != '..') {
        if (is_dir(__DIR__ . '/' . $archivo)) {
            echo "📁 $archivo/<br>";
        } else {
            echo "📄 $archivo<br>";
        }
    }
}
?>