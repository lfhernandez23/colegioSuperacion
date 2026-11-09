<?php
$colegio_base_path = dirname(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/colegioSuperacion/vistas/eliminar_pago.php')));
$colegio_fallback_historial = rtrim($colegio_base_path, '/') . '/vistas/historial_pago.php';
?>
<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<?php
// La sesión autenticada ya fue iniciada al comienzo del archivo.

// --- INICIO DE SECCIÓN DE SEGURIDAD ---
// Es MUY RECOMENDABLE añadir aquí tu lógica de autenticación y autorización.
// Ejemplo básico (descomentar y adaptar según tus necesidades):
/*
if (!isset($_SESSION['id_usuario'])) { // Verificar si el usuario está logueado
    $_SESSION['mensaje_error'] = "Acceso denegado. Debe iniciar sesión.";
    header('Location: /colegio_superacion/login.php'); // Redirigir a la página de login
    exit;
}
// Ejemplo de verificación de rol (si tienes roles de usuario)
if (!isset($_SESSION['rol_permite_eliminar']) || $_SESSION['rol_permite_eliminar'] !== true) {
    $_SESSION['mensaje_error'] = "Acceso denegado. No tiene permisos para eliminar recibos.";
    // Redirigir a una página anterior o a una página de error específica
    $redirect_url_error = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $colegio_fallback_historial;
    header('Location: ' . $redirect_url_error . (strpos($redirect_url_error, '?') ? '&' : '?') . 'status=delete_auth_failed');
    exit;
}
*/
// --- FIN DE SECCIÓN DE SEGURIDAD ---

// Configuración de reporte de errores (útil durante el desarrollo)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Verificar que la solicitud sea por método POST para mayor seguridad
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // $_SESSION['mensaje_error'] = "Acceso no permitido (método de solicitud incorrecto).";
    // Redirigir si no es POST
    $redirect_url_error = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $colegio_fallback_historial;
    header('Location: ' . $redirect_url_error . (strpos($redirect_url_error, '?') ? '&' : '?') . 'status=invalid_request_method');
    exit;
}

// Verificar que los parámetros necesarios para la eliminación hayan sido enviados y no estén vacíos
if (!isset($_POST["id_pago"]) || empty(trim($_POST["id_pago"])) || !isset($_POST["serie_recibo"]) || empty(trim($_POST["serie_recibo"]))) {
    // $_SESSION['mensaje_error'] = "Información insuficiente para eliminar el recibo (faltan ID o Serie).";
    $redirect_url_error = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $colegio_fallback_historial;
    header('Location: ' . $redirect_url_error . (strpos($redirect_url_error, '?') ? '&' : '?') . 'status=missing_params');
    exit;
}

// Sanitizar y validar los datos de entrada
$id_pago_a_eliminar = filter_var(trim($_POST["id_pago"]), FILTER_VALIDATE_INT);
$serie_recibo_a_eliminar = htmlspecialchars(trim($_POST["serie_recibo"])); // Sanitización básica para la serie

if ($id_pago_a_eliminar === false || $id_pago_a_eliminar <= 0) {
    // $_SESSION['mensaje_error'] = "Error: El ID del pago no es válido.";
    $redirect_url_error = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $colegio_fallback_historial;
    header('Location: ' . $redirect_url_error . (strpos($redirect_url_error, '?') ? '&' : '?') . 'status=invalid_id');
    exit;
}

// Aquí puedes añadir una validación más específica para $serie_recibo_a_eliminar si es necesario
// Ejemplo: if (!preg_match('/^[A-Z0-9]{1,10}$/', $serie_recibo_a_eliminar)) { ... }

// --- Conexión y Lógica de Eliminación ---
require_once "../controlador/conexion_bd.php"; // Ajusta la ruta si es necesario

// Variable para almacenar el resultado de la operación de eliminación
$filas_afectadas = -1; // Inicializar con -1 para indicar un posible error o no ejecución

// Preparar la consulta de eliminación.
// DECIDE SI NECESITAS `serie_recibo` EN EL WHERE.
// Si `id_pago` es único y es la clave primaria, la serie podría no ser necesaria para la eliminación directa,
// pero es bueno tenerla para confirmación o si forma parte de una clave compuesta.
// Opción 1: Eliminar solo por id_pago (si es la clave primaria única)
$consulta_eliminar = "DELETE FROM control_pagos WHERE id_pago = ? AND serie_recibo = ?";
$tipos_bind = "is";
$params_bind = [$id_pago_a_eliminar, $serie_recibo_a_eliminar];

// Opción 2: Eliminar por id_pago Y serie_recibo (si forman una clave primaria compuesta)
// $consulta_eliminar = "DELETE FROM control_pagos WHERE id_pago = ? AND serie_recibo = ?";
// $tipos_bind = "is";
// $params_bind = [$id_pago_a_eliminar, $serie_recibo_a_eliminar];


$sentencia_eliminar = $conn->prepare($consulta_eliminar);

if ($sentencia_eliminar) {
    // Usar call_user_func_array para bind_param dinámico si los parámetros varían
    $sentencia_eliminar->bind_param($tipos_bind, ...$params_bind);

    if ($sentencia_eliminar->execute()) {
        $filas_afectadas = $sentencia_eliminar->affected_rows;
        if ($filas_afectadas > 0) {
            // $_SESSION['mensaje_exito'] = "Recibo (ID: {$id_pago_a_eliminar}, Serie: {$serie_recibo_a_eliminar}) eliminado correctamente.";
            $status_param_redirect = 'deleted_ok';
        } else {
            // No se eliminó ninguna fila (0 filas afectadas)
            // $_SESSION['mensaje_info'] = "No se encontró el recibo para eliminar (ID: {$id_pago_a_eliminar}, Serie: {$serie_recibo_a_eliminar}) o ya había sido eliminado.";
            $status_param_redirect = 'not_found';
        }
    } else {
        // Error al ejecutar la eliminación.
        // $_SESSION['mensaje_error'] = "Error al ejecutar la eliminación del recibo: " . htmlspecialchars($sentencia_eliminar->error);
        error_log("Error al ejecutar la eliminación del recibo (ID: {$id_pago_a_eliminar}, Serie: {$serie_recibo_a_eliminar}): " . $sentencia_eliminar->error);
        $status_param_redirect = 'delete_execution_error';
    }
    $sentencia_eliminar->close();
} else {
    // Error en la preparación de la consulta.
    // $_SESSION['mensaje_error'] = "Error al preparar la consulta de eliminación: " . htmlspecialchars($conn->error);
    error_log("Error al preparar la consulta de eliminación: " . $conn->error);
    $status_param_redirect = 'delete_prepare_error';
}
$conn->close();

// --- Redirección ---
// Por defecto, redirigir a una página general si HTTP_REFERER no está disponible o es problemático.
$redirect_url_base = $colegio_fallback_historial; // Cambia esto a una página de historial adecuada

if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
    $referer_parts = parse_url($referer);

    // Asegurarse de que el referer es de nuestro propio sitio para evitar Open Redirect Vulnerability
    // Ejemplo: if (isset($referer_parts['host']) && $referer_parts['host'] == $_SERVER['HTTP_HOST']) {
    if (isset($referer_parts['path']) && str_starts_with($referer_parts['path'], rtrim($colegio_base_path, '/') . '/vistas/')) {
        $redirect_url_base = $referer_parts['path'];
        $query_string = "";
        if (isset($referer_parts['query'])) {
            parse_str($referer_parts['query'], $query_params);
            // Eliminar cualquier parámetro 'status' existente para no duplicarlo
            unset($query_params['status']);
            // Conservar otros parámetros de la URL original para que la página de historial pueda recargarse con los filtros correctos
            $query_string = http_build_query($query_params);
        }
        $redirect_url_base .= ($query_string ? '?' . $query_string : '');
    }
    // }
}

// Construir la URL de redirección final
$final_redirect_url = $redirect_url_base . (strpos($redirect_url_base, '?') === false ? '?' : '&') . 'status=' . $status_param_redirect;

header("Location: " . $final_redirect_url);
exit;

?>