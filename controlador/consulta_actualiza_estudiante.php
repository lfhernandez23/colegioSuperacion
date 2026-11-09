<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $carnet = $_POST["carnet"];
    $codigo = $_POST["codigo"];
    $nombre = htmlspecialchars($_POST["nombres"]);
    $apellido = htmlspecialchars($_POST["apellidos"]);
    $sexo = isset($_POST["sexo"]) ? $_POST["sexo"] : "";
    $fecha_nacimiento = $_POST["fecha-nacimiento"];
    $jornada = $_POST["jornada"];
    $grado = $_POST["grado"];
    $nivel = $_POST["nivel"];
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

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar la consulta SQL para la actualización
    $sql_actualizar = "UPDATE registro_estudiantes SET codigo=?, nombre=?, apellido=?, sexo=?, fecha_nacimiento=?, jornada=?, nivel=?, grado=?,  ciclo_escolar=?, tipo_sangre=?, direccion_vivienda=?, nombre_padre=?, nombre_madre=?, fecha_inscripcion=?, info_adicional=?, tel_alumno=?, tel_encargado=?, email_encargado=?, nombre_tutor=?, tipo_inscripcion=? WHERE carnet=? AND fecha_inscripcion=?";

    // Preparar la sentencia de actualización
    $stmt_actualizar = $conn->prepare($sql_actualizar);

    // Vincular parámetros para la actualización
    $stmt_actualizar->bind_param("ssssssssssssssssssssss", $codigo, $nombre, $apellido, $sexo, $fecha_nacimiento, $jornada, $nivel, $grado,  $ciclo_escolar, $tipo_sangre, $direccion_vivienda, $nombre_padre, $nombre_madre, $fecha_inscripcion, $info_adicional, $tel_alumno, $tel_encargado, $email_encargado, $nombre_tutor, $tipo_inscripcion, $carnet, $fecha_inscripcion);

    // Ejecutar la sentencia de actualización
    if ($stmt_actualizar->execute()) {
        $mensaje = "Inscripción actualizada exitosamente. Número de Código: $codigo Número de Carnet: $carnet";
        echo "<script>alert('$mensaje'); window.location.href='../vistas/buscar_estudiante.php';</script>";
    } else {
        $error = "Error al actualizar la inscripción: " . $stmt_actualizar->error;
        echo "<script>alert('$error');</script>";
    }

    // Cerrar la sentencia de actualización
    $stmt_actualizar->close();

    // Cerrar la conexión
    $conn->close();
} else {
    // Acceso incorrecto al archivo, redirigir o manejar de acuerdo a las necesidades    
    header("Location: ../vistas/buscar_estudiante.php");
    exit();
    }
