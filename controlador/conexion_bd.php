<?php
/**
 * Conexión de MySQL/MariaDB compatible con XAMPP.
 * Las variables de entorno permiten evitar credenciales dentro del código.
 *
 * COLEGIO_DB_HOST, COLEGIO_DB_PORT, COLEGIO_DB_USER,
 * COLEGIO_DB_PASSWORD, COLEGIO_DB_NAME
 */
$colegioDbConfig = static function (string $key, string $fallback): string {
    $value = getenv($key);
    return $value === false ? $fallback : $value;
};

$servername = $colegioDbConfig('COLEGIO_DB_HOST', 'localhost');
$username = $colegioDbConfig('COLEGIO_DB_USER', 'root');
$password = $colegioDbConfig('COLEGIO_DB_PASSWORD', '');
$dbname = $colegioDbConfig('COLEGIO_DB_NAME', 'colegio_superacion');
$port = (int) $colegioDbConfig('COLEGIO_DB_PORT', '3306');

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Error de conexión a la base de datos Colegio Superación: ' . $e->getMessage());
    http_response_code(503);
    exit('No fue posible conectar con la base de datos.');
}
