<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario y sanitizarlos
    $jornada = $_POST["jornada"];
    $nivel = $_POST["nivel"];
    $grado = $_POST["grado"];
    $curso = ($_POST["curso"]);
    $descripcion_nivel = $_POST["nivel"];

    require_once "conexion_bd.php";

        // Preparar la consulta SQL para la inserción
        $sql_insertar = "INSERT INTO cursos (jornada, nivel, grado, descripcion_nivel , curso) VALUES (?, ?, ?, ?, ?)";

        // Preparar la sentencia de inserción
        $stmt_insertar = $conn->prepare($sql_insertar);

        // Vincular parámetros para la inserción
        $stmt_insertar->bind_param("sssss", $jornada, $nivel, $grado, $descripcion_nivel ,$curso);

        // Ejecutar la sentencia de inserción
        if ($stmt_insertar->execute()) {
            // Envío de correo de confirmación                        
            $mensaje = "Curso creado de forma exitosa.";
            echo "<script>alert('$mensaje'); window.location.href='../vistas/navegacion.php';</script>";
            // Salir del script PHP
            exit();
        } else {
            $error = "Error al procesar la creación del curso: " . $stmt_insertar->error;
            echo "<script>alert('$error'); window.location.href='../vistas/creacion_de_cursos_generales.php'</script>";
            exit();
        }

        // Cerrar la sentencia de inserción
        $stmt_insertar->close();


    // Cerrar la conexión y la sentencia de verificación
    $stmt_verificar->close();
    $conn->close();
} else {
    // Acceso incorrecto al archivo, redirigir o manejar de acuerdo a las necesidades    
    header("Location: ../vistas/creacion_de_cursos_generales.php");
    exit();
}
