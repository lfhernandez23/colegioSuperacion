<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $nombre = htmlspecialchars($_POST["nombre"]);
    $apellido = htmlspecialchars($_POST["apellido"]);        
    $carnet = $_POST["carnet"];
    $codigo = $_POST["codigo"];
    $jornada = $_POST["jornada"];
    $nivel = $_POST["nivel"];
    $grado = $_POST["grado"];    
    $ciclo_escolar = $_POST["ciclo_escolar"];    
    $id_pago = $_POST["id_pago"];
    $num_autorizacion = $_POST["num_autorizacion"];
    $fecha_pago = $_POST["fecha_pago"];
    $mes_pago = $_POST["mes_pago"];
    $tipo_pago = $_POST["tipo_pago"];
    $monto = $_POST["monto"];
    $observacion_adicional = $_POST["observacion_adicional"];

    require_once "conexion_bd.php";
 
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar la consulta SQL para la actualización
    $sql_actualizar = "UPDATE control_pagos cp
                        SET cp.fecha_pago = ?, cp.mes_pago = ?, cp.tipo_pago = ?, cp.monto = ?, cp.observacion_adicional = ?
                        WHERE cp.carnet = ? AND cp.ciclo_escolar = ? AND cp.num_autorizacion = ? AND cp.id_pago = ?";

    // Preparar la sentencia de actualización
    $stmt_actualizar = $conn->prepare($sql_actualizar);

    // Vincular parámetros para la actualización
    $stmt_actualizar->bind_param("ssssssssi", $fecha_pago, $mes_pago, $tipo_pago, $monto, $observacion_adicional, $carnet, $ciclo_escolar, $num_autorizacion, $id_pago);

    // Ejecutar la sentencia de actualización
    if ($stmt_actualizar->execute()) {
        $mensaje = "Pago actualizado exitosamente. Número de Código: $codigo Número de Carnet: $carnet";
        echo "<script>alert('$mensaje'); window.location.href='../vistas/historial_pago.php';</script>";
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
    header("Location: ../vistas/navegacion.php");
    exit();
}
?>
