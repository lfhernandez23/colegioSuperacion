<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<?php
session_start(); // Recomendado para futuras mejoras como mensajes flash PRG

include_once "../controlador/conexion_bd.php"; // Conexión a la base de datos

// Función para limpiar datos (principalmente para trim(), mysqli_real_escape_string es redundante con sentencias preparadas)
function limpiar_dato($dato) {
    global $conn; // Usando $conn global como en el código original
    // mysqli_real_escape_string es para SQL injection, no estrictamente necesario aquí si solo se usa para basename o file paths
    // pero trim es útil.
    return trim($dato); 
}

// --- Inicialización de variables ---
$search_carnet_val = isset($_GET['carnet']) ? htmlspecialchars($_GET['carnet']) : '';
$search_ciclo_escolar_val = isset($_GET['ciclo_escolar']) ? htmlspecialchars($_GET['ciclo_escolar']) : '';

$student_details = null; // Datos de registro_estudiantes
$existing_carnet_details = null; // Datos de carnet_estudiante si existe
$is_update_mode = false; // Bandera para modo actualización

$messages = ['error' => '', 'success' => '', 'info' => ''];

// Mapeos de nivel y grado (como en el código original)
$nivel_nombres = [
    "preprimaria" => "Pre-Primaria", "primaria" => "Primaria", "basico" => "Básico",
    "basicopormadurez" => "Básico por Madurez", "diversificado1" => "Perito Contador con Orientación en Computación",
    "diversificado2" => "Perito en Administración de Empresa", "diversificado3" => "Bachiller en Computación con Orientación Comercial",
    "diversificado4" => "Bachiller en Ciencias y Letras por Madurez"
];
$grado_nombres = [
    "1grado" => "1er Grado", "2grado" => "2do Grado", "3grado" => "3er Grado",
    "4grado" => "4to Grado", "5grado" => "5to Grado", "6grado" => "6to Grado"
];

// --- LÓGICA DE PROCESAMIENTO ---

// 1. Procesar Búsqueda de Estudiante (solicitud GET con parámetro 'buscar')
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) {
    // Limpieza de datos de búsqueda se hace antes de la consulta
    $carnet_to_search_raw = isset($_GET["carnet"]) ? trim($_GET["carnet"]) : '';
    $ciclo_to_search_raw = isset($_GET["ciclo_escolar"]) ? trim($_GET["ciclo_escolar"]) : '';
    
    $search_carnet_val = htmlspecialchars($carnet_to_search_raw); // Para repoblar el formulario
    $search_ciclo_escolar_val = htmlspecialchars($ciclo_to_search_raw); // Para repoblar el formulario

    if (!empty($carnet_to_search_raw) && !empty($ciclo_to_search_raw)) {
        // Buscar en registro_estudiantes
        $consulta_estudiante = "SELECT * FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
        $sentencia_estudiante = $conn->prepare($consulta_estudiante);
        $sentencia_estudiante->bind_param("ss", $carnet_to_search_raw, $ciclo_to_search_raw);
        $sentencia_estudiante->execute();
        $resultados_estudiante = $sentencia_estudiante->get_result();

        if ($resultados_estudiante->num_rows > 0) {
            $student_details = $resultados_estudiante->fetch_assoc();

            // Verificar si ya existe un carnet para este estudiante en carnet_estudiante
            $consulta_carnet_existente = "SELECT * FROM carnet_estudiante WHERE carnet = ?";
            $sentencia_carnet_existente = $conn->prepare($consulta_carnet_existente);
            $sentencia_carnet_existente->bind_param("s", $student_details['carnet']);
            $sentencia_carnet_existente->execute();
            $resultado_carnet_existente = $sentencia_carnet_existente->get_result();

            if ($resultado_carnet_existente->num_rows > 0) {
                $existing_carnet_details = $resultado_carnet_existente->fetch_assoc();
                $is_update_mode = true;
                $messages['info'] = "Ya existe un carnet para este estudiante. Puede actualizarlo. Se requiere una nueva fotografía si desea cambiar la actual.";
            } else {
                $messages['info'] = "Estudiante encontrado. Complete los datos para crear el carnet. Se requiere una fotografía.";
            }
            $sentencia_carnet_existente->close();
        } else {
            $messages['error'] = "No se encontraron resultados para el carnet '" . htmlspecialchars($carnet_to_search_raw) . "' en el ciclo escolar '" . htmlspecialchars($ciclo_to_search_raw) . "'.";
        }
        $sentencia_estudiante->close();
    } else {
        $messages['error'] = "Por favor, ingrese el número de carnet y el ciclo escolar para buscar.";
    }
}

// 2. Procesar Creación/Actualización de Carnet (solicitud POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_carnet"])) {
    $carnet_post = limpiar_dato($_POST["carnet"]);
    $grado_post = limpiar_dato($_POST["grado"]); 
    $nivel_post = limpiar_dato($_POST["nivel"]); 
    $nombres_post = limpiar_dato($_POST["nombres"]);
    $apellidos_post = limpiar_dato($_POST["apellidos"]);
    $fecha_vencimiento_post = limpiar_dato($_POST["fecha_vencimiento"]);
    $is_update_action = isset($_POST["is_update"]) && $_POST["is_update"] == '1';

    $nombre_completo_post = $nombres_post . ' ' . $apellidos_post;
    $fecha_creacion_actual = date("Y-m-d H:i:s"); 
    $foto_path_db = ''; // Variable que almacenará la ruta web de la foto para la BD

    // --- Manejo de Carga de Foto ---
    $foto_cargada_correctamente = false;
    // MODIFICADO: Definir la ruta base del servidor y la ruta base web para las fotos
    $directorio_destino_servidor = "C:/xampp/htdocs/colegio_superacion/img/fotos_estudiantes/"; 
    $directorio_web_fotos_base = "/colegio_superacion/img/fotos_estudiantes/"; // Asume que 'colegio_superacion' está en la raíz de htdocs

    if (isset($_FILES["foto_estudiante"]) && $_FILES["foto_estudiante"]["error"] == UPLOAD_ERR_OK) {
        if (!is_dir($directorio_destino_servidor)) {
            if (!mkdir($directorio_destino_servidor, 0777, true)) {
                 $messages['error'] = "Error: No se pudo crear el directorio para las fotos. Verifique permisos en: " . htmlspecialchars(dirname($directorio_destino_servidor));
            }
        }
        
        if (empty($messages['error']) && is_writable($directorio_destino_servidor)) {
            $nombre_original_foto = basename($_FILES["foto_estudiante"]["name"]);
            $extension_foto = strtolower(pathinfo($nombre_original_foto, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extension_foto, $allowed_extensions)) {
                if ($_FILES["foto_estudiante"]["size"] <= 5 * 1024 * 1024) { // Max 5MB
                    $nombre_archivo_foto_unic = uniqid('student_', true) . "." . $extension_foto; 
                    $ruta_completa_foto_servidor = $directorio_destino_servidor . $nombre_archivo_foto_unic;

                    if (move_uploaded_file($_FILES["foto_estudiante"]["tmp_name"], $ruta_completa_foto_servidor)) { 
                        // MODIFICADO: Guardar la ruta web completa en la variable para la BD
                        $foto_path_db = $directorio_web_fotos_base . $nombre_archivo_foto_unic; 
                        $foto_cargada_correctamente = true;

                        // MODIFICADO: Lógica para eliminar foto antigua
                        if ($is_update_action && isset($_POST['current_foto_path']) && !empty($_POST['current_foto_path'])) {
                            // $_POST['current_foto_path'] contiene la ruta web (ej: /colegio_superacion/img/fotos_estudiantes/imagen.jpg)
                            $old_photo_web_path = limpiar_dato($_POST['current_foto_path']);
                            $old_photo_filename = basename($old_photo_web_path); // Extrae solo el nombre del archivo
                            $old_photo_server_path = $directorio_destino_servidor . $old_photo_filename;
                            if (file_exists($old_photo_server_path)) {
                                @unlink($old_photo_server_path); 
                            }
                        }
                    } else {
                        $messages['error'] = "Error al mover el archivo de la foto subida."; 
                    }
                } else {
                    $messages['error'] = "Error: El archivo de la foto es demasiado grande (máximo 5MB).";
                }
            } else {
                $messages['error'] = "Error: Tipo de archivo de foto no permitido (solo JPG, JPEG, PNG, GIF).";
            }
        } elseif (empty($messages['error'])) { 
             $messages['error'] = "Error: El directorio de destino para las fotos no tiene permisos de escritura (" . htmlspecialchars($directorio_destino_servidor) . ").";
        }
    } else {
        // Si es una actualización y no se sube foto nueva, se conserva la existente (si hay)
        if ($is_update_action && isset($_POST['current_foto_path']) && !empty($_POST['current_foto_path'])) {
            $foto_path_db = limpiar_dato($_POST['current_foto_path']); // Conservar la ruta de la foto actual
            $foto_cargada_correctamente = true; // Se considera "correcto" porque se mantiene la foto existente
        } elseif (!isset($_FILES["foto_estudiante"]) || $_FILES["foto_estudiante"]["error"] == UPLOAD_ERR_NO_FILE) {
            // Solo es error si no es actualización o siéndolo, no había foto antes y no se sube nueva.
             if (!$is_update_action) { // Para creación, la foto es obligatoria.
                $messages['error'] = "Error: Se requiere una fotografía.";
             } else if ($is_update_action && empty($_POST['current_foto_path'])) { // Para actualización, si no había foto y no se sube nueva.
                $messages['error'] = "Error: Se requiere una fotografía para actualizar.";
             }
        } else if ($_FILES["foto_estudiante"]["error"] != UPLOAD_ERR_OK) {
            $messages['error'] = "Error al subir la foto. Código: " . $_FILES["foto_estudiante"]["error"];
        }
    }
    // --- Fin Manejo de Carga de Foto ---

    if (empty($messages['error'])) {
        if (!$foto_cargada_correctamente && !$is_update_action) { // Si es creación y la foto no se cargó
             if (empty($messages['error'])) { 
                $messages['error'] = "Error: La carga de la fotografía es obligatoria y ha fallado o no se proporcionó.";
             }
        } else if ($is_update_action && !$foto_cargada_correctamente && !isset($_POST['current_foto_path'])) { 
            // Si es actualización, no se cargó nueva foto y no había una anterior
            if (empty($messages['error'])) {
                $messages['error'] = "Error: Se requiere una fotografía para la actualización.";
            }
        } else {
             if (empty($foto_path_db) && $is_update_action && isset($_POST['current_foto_path'])) {
                // Caso especial: si en la actualización no se subió nueva foto, se usa la anterior.
                $foto_path_db = limpiar_dato($_POST['current_foto_path']);
             } else if (empty($foto_path_db) && !$is_update_action) {
                // Si es creación y foto_path_db sigue vacío, es un error no capturado antes.
                 $messages['error'] = "Error: La ruta de la fotografía no pudo ser determinada.";
             }


            if (empty($messages['error'])) { // Procede solo si no hay errores y tenemos una ruta de foto
                $ciclo_escolar_para_redireccion = '';
                if (isset($_POST['original_ciclo_buscado_post']) && !empty($_POST['original_ciclo_buscado_post'])) {
                    $ciclo_escolar_para_redireccion = $_POST['original_ciclo_buscado_post'];
                } elseif (!empty($search_ciclo_escolar_val)) { 
                    $ciclo_escolar_para_redireccion = $search_ciclo_escolar_val;
                }
                
                $enlace_visualizar = htmlspecialchars("buscar_carnet_estudiante.php?carnet=" . urlencode($carnet_post) . "&ciclo_escolar=" . urlencode($ciclo_escolar_para_redireccion) . "&accion=mostrar");

                if ($is_update_action) {
                    $consulta_actualizar = "UPDATE carnet_estudiante SET grado = ?, nivel = ?, nombre_completo = ?, foto_estudiante = ?, fecha_vencimiento = ?  WHERE carnet = ?";
                    $sentencia_actualizar = $conn->prepare($consulta_actualizar);
                    // $foto_path_db ya contiene la ruta web completa
                    $sentencia_actualizar->bind_param("ssssss", $grado_post, $nivel_post, $nombre_completo_post, $foto_path_db, $fecha_vencimiento_post, $carnet_post);

                    if ($sentencia_actualizar->execute()) {
                        $messages['success'] = "Carnet actualizado con éxito. <br><a href='" . $enlace_visualizar . "'>¿Desea visualizar o imprimir el carnet?</a>";
                        // No limpiar $student_details aquí si se quiere mantener la info en pantalla tras éxito
                        // $student_details = null; 
                        // $existing_carnet_details = null; // Se recargará si se busca de nuevo
                        // $is_update_mode = false; // Se recargará si se busca de nuevo
                    } else {
                        $messages['error'] = "Error al actualizar el carnet: " . $sentencia_actualizar->error;
                    }
                    $sentencia_actualizar->close();
                } else {
                    // INSERT nuevo carnet
                    $consulta_insertar = "INSERT INTO carnet_estudiante (carnet, grado, nivel, nombre_completo, foto_estudiante, fecha_vencimiento, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?)"; 
                    $sentencia_insertar = $conn->prepare($consulta_insertar);
                    // $foto_path_db ya contiene la ruta web completa
                    $sentencia_insertar->bind_param("sssssss", $carnet_post, $grado_post, $nivel_post, $nombre_completo_post, $foto_path_db, $fecha_vencimiento_post, $fecha_creacion_actual); 

                    if ($sentencia_insertar->execute()) {
                        $messages['success'] = "Carnet creado con éxito. <br><a href='" . $enlace_visualizar . "'>¿Desea visualizar o imprimir el carnet?</a>"; 
                        // $student_details = null; 
                    } else {
                        $messages['error'] = "Error al crear el carnet: " . $sentencia_insertar->error; 
                        if ($conn->errno == 1062) { 
                            $messages['error'] = "Error: Ya existe un carnet con este número. Verifique los datos o intente actualizar.";
                        }
                    }
                    $sentencia_insertar->close();
                }
            }
        }
    }

    // Si hubo un error en POST y necesitamos repoblar el formulario de detalles del estudiante:
    if (!empty($messages['error']) && (isset($_POST['original_carnet_buscado_post']) && isset($_POST['original_ciclo_buscado_post']))) {
        $carnet_repop_raw = limpiar_dato($_POST["original_carnet_buscado_post"]);
        $ciclo_repop_raw = limpiar_dato($_POST["original_ciclo_buscado_post"]);
        
        $search_carnet_val = htmlspecialchars($carnet_repop_raw); 
        $search_ciclo_escolar_val = htmlspecialchars($ciclo_repop_raw);

        $stmt_refetch = $conn->prepare("SELECT * FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?");
        $stmt_refetch->bind_param("ss", $carnet_repop_raw, $ciclo_repop_raw);
        $stmt_refetch->execute();
        $res_refetch = $stmt_refetch->get_result();
        if ($res_refetch->num_rows > 0) {
            $student_details = $res_refetch->fetch_assoc();
            
            $stmt_carnet_check = $conn->prepare("SELECT * FROM carnet_estudiante WHERE carnet = ?");
            $stmt_carnet_check->bind_param("s", $student_details['carnet']);
            $stmt_carnet_check->execute();
            $res_carnet_check = $stmt_carnet_check->get_result();
            if ($res_carnet_check->num_rows > 0) {
                $existing_carnet_details = $res_carnet_check->fetch_assoc();
                $is_update_mode = true; 
            } else {
                $is_update_mode = false;
            }
            $stmt_carnet_check->close();
        }
        $stmt_refetch->close();
    }
}
// --- FIN LÓGICA DE PROCESAMIENTO ---
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" /> <link rel="stylesheet" href="../css/navstyle.css" /> <title>Crear o Actualizar Carnet de Estudiante</title>
    <style>
        .error { color: red; font-weight: bold; margin-bottom: 10px; padding: 10px; border: 1px solid red; background-color: #ffebeb; }
        .success { color: green; font-weight: bold; margin-bottom: 10px; padding: 10px; border: 1px solid green; background-color: #e6ffe6; }
        .info { color: blue; font-weight: bold; margin-bottom: 10px; padding: 10px; border: 1px solid blue; background-color: #e6f7ff;}
        .current-photo { max-width: 100px; max-height: 100px; display: block; margin-top: 5px; border: 1px solid #ccc; }
        hr { margin-top: 20px; margin-bottom: 20px; }
        .success a { color: #006400; text-decoration: underline; }
        .success a:hover { color: #004d00; }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

    <?php include './header.php'; ?> <div class="container"> <h2><?php echo $is_update_mode ? "Actualizar Carnet de Estudiante" : "Crear Carnet de Estudiante"; ?></h2>

        <?php if (!empty($messages['error'])): ?>
            <p class="error"><?php echo $messages['error']; ?></p>
        <?php endif; ?>
        <?php if (!empty($messages['success'])): ?>
            <p class="success"><?php echo $messages['success']; /* El enlace ya está incluido aquí */ ?></p>
        <?php endif; ?>
        <?php if (!empty($messages['info']) && empty($messages['success']) && empty($messages['error'])): ?>
            <p class="info"><?php echo $messages['info']; ?></p>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get"> <label for="carnet_search">Número de Carnet del Estudiante:</label> <input type="text" id="carnet_search" name="carnet" value="<?php echo $search_carnet_val; ?>" required /> <label for="ciclo_escolar_search">Ciclo Escolar Inscrito:</label> <input type="text" id="ciclo_escolar_search" name="ciclo_escolar" value="<?php echo $search_ciclo_escolar_val; ?>" placeholder="Ej: 2024 o 2025" required /> <button type="submit" name="buscar">Buscar Estudiante</button> </form>
        <hr>

        <?php
        if ($student_details) {
            $display_nivel = $nivel_nombres[$student_details['nivel']] ?? htmlspecialchars($student_details['nivel']);
            $display_grado = $grado_nombres[$student_details['grado']] ?? htmlspecialchars($student_details['grado']);

            $default_fecha_vencimiento = '';
            $ciclo_escolar_para_vencimiento = $search_ciclo_escolar_val; 
            
            if (!empty($ciclo_escolar_para_vencimiento)) {
                preg_match('/^(\d{4})/', $ciclo_escolar_para_vencimiento, $matches_año);
                if (isset($matches_año[1])) {
                    $año_ciclo = $matches_año[1];
                    $default_fecha_vencimiento = $año_ciclo . "-12-31"; 
                } else {
                    $default_fecha_vencimiento = date('Y') . "-12-31";
                }
            }

            $form_carnet_val = htmlspecialchars($student_details['carnet']);
            $form_nombres_val = htmlspecialchars($student_details['nombre']);
            $form_apellidos_val = htmlspecialchars($student_details['apellido']);
            $form_nivel_val = $display_nivel; 
            $form_grado_val = $display_grado; 

            $form_fecha_vencimiento_val = ($is_update_mode && $existing_carnet_details && !empty($existing_carnet_details['fecha_vencimiento']))
                                          ? htmlspecialchars($existing_carnet_details['fecha_vencimiento'])
                                          : $default_fecha_vencimiento;
            
            // MODIFICADO: $current_photo_web_path ahora contiene la ruta web completa desde la BD
            $current_photo_web_path = ($is_update_mode && $existing_carnet_details && !empty($existing_carnet_details['foto_estudiante']))
                                      ? htmlspecialchars($existing_carnet_details['foto_estudiante'])
                                      : '';
        ?>
            <h3><?php echo $is_update_mode ? "Actualizar Datos del Carnet" : "Datos para el Nuevo Carnet"; ?></h3>
            <form id="carnetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?carnet=<?php echo urlencode($search_carnet_val); ?>&ciclo_escolar=<?php echo urlencode($search_ciclo_escolar_val); ?>&buscar=true" method="post" enctype="multipart/form-data"> <input type="hidden" name="original_carnet_buscado_post" value="<?php echo $search_carnet_val; ?>">
                <input type="hidden" name="original_ciclo_buscado_post" value="<?php echo $search_ciclo_escolar_val; ?>">

                <input type="hidden" name="is_update" value="<?php echo $is_update_mode ? '1' : '0'; ?>">
                <?php if ($is_update_mode && $current_photo_web_path): ?>
                    <input type="hidden" name="current_foto_path" value="<?php echo $current_photo_web_path; ?>">
                <?php endif; ?>

                <label for="carnet_display">Número de Carnet:</label> <input type="text" id="carnet_display" name="carnet" value="<?php echo $form_carnet_val; ?>" readonly /> <label for="nombres_display">Nombre del Estudiante:</label> <input type="text" id="nombres_display" name="nombres" value="<?php echo $form_nombres_val; ?>" readonly /> <label for="apellidos_display">Apellido del Estudiante:</label> <input type="text" id="apellidos_display" name="apellidos" value="<?php echo $form_apellidos_val; ?>" readonly /> <label for="nivel_display">Nivel Asignado:</label> <input type="text" id="nivel_display" name="nivel" value="<?php echo $form_nivel_val; ?>" readonly /> <label for="grado_display">Grado Asignado:</label> <input type="text" id="grado_display" name="grado" value="<?php echo $form_grado_val; ?>" readonly /> <?php if ($is_update_mode && $current_photo_web_path): ?>
                    <div>
                        <label>Foto Actual:</label>
                        <img src="<?php echo $current_photo_web_path; ?>" alt="Foto Actual del Estudiante" class="current-photo">
                        <p><em>Para actualizar, seleccione una nueva foto. La foto anterior será reemplazada si la carga es exitosa. Si no selecciona una nueva, se conservará la actual.</em></p>
                    </div>
                <?php endif; ?>

                <label for="foto_estudiante">Foto del Estudiante <?php echo ($is_update_mode && $current_photo_web_path) ? "(Opcional si no desea cambiarla)" : "(Requerida)"; ?>:</label> 
                <input type="file" id="foto_estudiante" name="foto_estudiante" accept="image/*" <?php echo !($is_update_mode && $current_photo_web_path) ? 'required' : ''; ?> /> 
                
                <label for="fecha_vencimiento">Fecha de Vencimiento del Carnet:</label> <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo $form_fecha_vencimiento_val; ?>" required /> <button type="submit" name="submit_carnet"><?php echo $is_update_mode ? "Actualizar Carnet" : "Guardar Carnet"; ?></button> </form>
        <?php
        } 
        ?>
    </div> </body>
</html>
<?php
if (isset($conn)) {
    $conn->close(); 
}
?>