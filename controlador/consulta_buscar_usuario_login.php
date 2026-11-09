<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . colegio_ruta_inicio());
    exit;
}

$identificador = trim((string) ($_POST['user'] ?? ''));
$contrasena = (string) ($_POST['pass'] ?? '');
if ($identificador === '' || $contrasena === '') {
    header('Location: ' . colegio_ruta_inicio() . '?error=credenciales');
    exit;
}

try {
    require_once __DIR__ . '/conexion_bd.php';
    $sql = 'SELECT idusuario, nombreusuario, contrasena FROM usuarios '
         . 'WHERE nombreusuario = ? OR correoelectronico = ? LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $identificador, $identificador);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $valido = false;
    $migrar = false;
    if ($usuario) {
        $guardada = (string) ($usuario['contrasena'] ?? '');
        $info = password_get_info($guardada);
        if (!empty($info['algo'])) {
            $valido = password_verify($contrasena, $guardada);
            $migrar = $valido && password_needs_rehash($guardada, PASSWORD_DEFAULT);
        } elseif ($guardada !== '') {
            // Compatibilidad temporal: la cuenta se migra a hash tras un login correcto.
            $valido = hash_equals($guardada, $contrasena);
            $migrar = $valido;
        }
    }
    if (!$valido) {
        $conn->close();
        header('Location: ' . colegio_ruta_inicio() . '?error=credenciales');
        exit;
    }

    if ($migrar) {
        $id = (int) $usuario['idusuario'];
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $update = $conn->prepare('UPDATE usuarios SET contrasena = ? WHERE idusuario = ?');
        $update->bind_param('si', $hash, $id);
        $update->execute();
        $update->close();
    }
    $conn->close();
    colegio_iniciar_sesion();
    session_regenerate_id(true);
    $_SESSION['colegio_usuario_id'] = (int) $usuario['idusuario'];
    $_SESSION['colegio_nombre_usuario'] = (string) ($usuario['nombreusuario'] ?? '');
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/controlador/consulta_buscar_usuario_login.php'));
    $base = dirname(dirname($script));
    header('Location: ' . (($base === '/' || $base === '.') ? '' : $base) . '/vistas/navegacion.php', true, 302);
    exit;
} catch (Throwable $e) {
    error_log('Error de autenticación: ' . $e->getMessage());
    http_response_code(503);
    echo 'No se pudo iniciar sesión. Inténtelo de nuevo.';
    exit;
}
