// Función para generar un código único de 8 caracteres
function generarCodigoUnico() {
    // Caracteres permitidos para el código
    var caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var longitud = 8; // Longitud del código

    // Inicializar el código como una cadena vacía
    var codigo = '';

    // Generar el código aleatorio
    for (var i = 0; i < longitud; i++) {
        codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }

    return codigo;
}

