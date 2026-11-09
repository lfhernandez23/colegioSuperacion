<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario y sanitizarlos
    $carnet = $_POST["carnet"];
    $codigo = $_POST["codigo"];
    $nombre = htmlspecialchars($_POST["nombres"]);
    $apellido = htmlspecialchars($_POST["apellidos"]);
    $sexo = isset($_POST["sexo"]) ? $_POST["sexo"] : "";
    $fecha_nacimiento = $_POST["fecha-nacimiento"];
    $jornada = $_POST["jornada"];
    $nivel = $_POST["nivel"];
    $grado = $_POST["grado"];
    $ciclo_escolar = $_POST["ciclo_escolar"];
    $tipo_sangre = $_POST["tipo-sangre"];
    $direccion_vivienda = htmlspecialchars($_POST["direccion-vivienda"]);
    $nombre_padre = htmlspecialchars($_POST["nombre-padre"]);
    $nombre_madre = htmlspecialchars($_POST["nombre-madre"]);
    $fecha_inscripcion = $_POST["fecha_inscripcion"];
    $info_adicional = htmlspecialchars($_POST["info-adicional"]);
    $tel_alumno = $_POST["tel-alumno"];
    $tel_encargado = $_POST["tel-encargado"];
    $email_encargado = $_POST["email-encargado"];
    $nombre_tutor = htmlspecialchars($_POST["nombre-tutor"]);
    $tipo_inscripcion = $_POST["tipo-inscripcion"];

    require_once "conexion_bd.php";



    // Verificar si el código o carnet ya existen
    $sql_verificar = "SELECT carnet, ciclo_escolar FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("ss", $carnet, $ciclo_escolar);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows > 0) {
        // Código o carnet ya existen, mostrar mensaje de error
        $error = "Los datos de registro de carnet y ciclo escolar ya están en uso.";
        echo "<script>alert('$error'); window.location.href='../vistas/registro_estudiante.php'</script>";
    } else {
        // Preparar la consulta SQL para la inserción
        $sql_insertar = "INSERT INTO registro_estudiantes (carnet, codigo, nombre, apellido, sexo, fecha_nacimiento, jornada, nivel, grado,  ciclo_escolar, tipo_sangre, direccion_vivienda, nombre_padre, nombre_madre, fecha_inscripcion, info_adicional, tel_alumno, tel_encargado, email_encargado, nombre_tutor, tipo_inscripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        // Preparar la sentencia de inserción
        $stmt_insertar = $conn->prepare($sql_insertar);

        // Vincular parámetros para la inserción
        $stmt_insertar->bind_param("sssssssssssssssssssss", $carnet, $codigo, $nombre, $apellido, $sexo, $fecha_nacimiento, $jornada, $nivel, $grado,  $ciclo_escolar, $tipo_sangre, $direccion_vivienda, $nombre_padre, $nombre_madre, $fecha_inscripcion, $info_adicional, $tel_alumno, $tel_encargado, $email_encargado, $nombre_tutor, $tipo_inscripcion);

        // Ejecutar la sentencia de inserción
        if ($stmt_insertar->execute()) {
            // Envío de correo de confirmación                        
            $mensaje = "Inscripción exitosa. Número de Código: $codigo Número de Carnet: $carnet";
            echo "<script>alert('$mensaje'); window.location.href='../vistas/registro_estudiante.php';</script>";
            echo "setTimeout(function() { window.location.href = '../vistas/registro_estudiante.php'; }, 1000);</script>";
            // Salir del script PHP
            exit();
        } else {
            $error = "Error al procesar la inscripción: " . $stmt_insertar->error;
            echo "<script>alert('$error'); window.location.href='../vistas/registro_estudiante.php'</script>";
            exit();
        }

        // Cerrar la sentencia de inserción
        $stmt_insertar->close();
    }

    // Cerrar la conexión y la sentencia de verificación
    $stmt_verificar->close();
    $conn->close();
} else {
    // Acceso incorrecto al archivo, redirigir o manejar de acuerdo a las necesidades    
    header("Location: ../vistas/registro_estudiantes.php");
    exit();
}
