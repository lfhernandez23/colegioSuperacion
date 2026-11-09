<?php
declare(strict_types=1);

/**
 * Control de acceso compartido. No incluye ni abre la conexión a BD.
 * Las sesiones se mantienen entre vistas y controladores.
 */
function colegio_iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    session_name('colegio_superacion');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function colegio_ruta_inicio(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/vistas/navegacion.php');
    $base = dirname(dirname($script));
    return ($base === '/' || $base === '.') ? '/index.php' : $base . '/index.php';
}

function colegio_requerir_sesion(): void
{
    colegio_iniciar_sesion();
    if (!isset($_SESSION['colegio_usuario_id']) || !is_numeric($_SESSION['colegio_usuario_id'])) {
        if (!headers_sent()) {
            header('Cache-Control: no-store, private');
            header('Location: ' . colegio_ruta_inicio(), true, 302);
        } else {
            http_response_code(401);
            echo 'Se requiere iniciar sesión para acceder al sistema.';
        }
        exit;
    }
    if (!headers_sent()) {
        header('Cache-Control: no-store, private');
    }
}
