
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtén los datos del formulario
    $usuarioCorreo = $_POST["user"];
    $contrasena = $_POST["pass"];

    // Credenciales de conexión a la base de datos
    include_once "conexion_bd.php";
    

    // Consulta SQL para validar el inicio de sesión
    $sql = "SELECT * FROM usuarios WHERE (nombreusuario = ? OR correoelectronico = ?) AND contrasena = ?";
    
    // Preparar la declaración
    $stmt = $conn->prepare($sql);
    
    // Vincular los parámetros
    $stmt->bind_param("sss", $usuarioCorreo, $usuarioCorreo, $contrasena);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Obtener el resultado
    $result = $stmt->get_result();
    
    // Verificar si se encontró un usuario
    if ($result->num_rows > 0) {
        // Usuario autenticado, redirige a otra página
        header("Location: ../vistas/navegacion.php");
        exit(); // Asegura que el script se detenga después de la redirección
    } else {
        // Usuario no encontrado o contraseña incorrecta
        $mensaje = "Usuario o Contraseña incorrectos";
         echo "<script>alert('$mensaje'); window.location.href='../index.php';</script>";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
?>
