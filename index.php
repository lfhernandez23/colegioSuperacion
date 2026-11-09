<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Colegio Superación</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <link rel="stylesheet" href="css/css/all.min.css" type="text/css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Trocchi&display=swap" rel="stylesheet">
</head>
<body>
    <img class="wave" src="img/wave.png" alt="">
    <div class="contenedor">
        <div class="img">
            <img src="img/log_colegio.png" alt="Logotipo del Colegio Superación">            
        </div>
        <div class="contenido-login">
            <form action="./controlador/consulta_buscar_usuario_login.php" method="post">
                <img src="img/ico_lapiz.png" alt="">
                <h2>Colegio privado mixto "Superación"</h2>
                <div class="input-div user">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <label for="user">Usuario o Correo Electrónico</label>
                        <input type="text" id="user" name="user" class="input" required>
                    </div>                    
                </div>
                <div class="input-div pass">
                    <div class="i">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <label for="pass">Contraseña</label>
                        <input type="password" id="pass" name="pass" class="input" required>
                    </div>                    
                </div>
                <input type="submit" class="btn" value="Iniciar Sesión">
            </form>
        </div>
    </div>
</body>
</html>
