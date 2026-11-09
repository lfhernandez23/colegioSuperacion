<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Ver Carnet del Estudiante</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .carnet-card {
            position: relative;
            width: 86mm; /* MODIFICADO: Dimensiones estándar ID-1 */
            height: 54mm; /* MODIFICADO: Dimensiones estándar ID-1 */
            background-color: #FFFFFF; /* MODIFICADO: Fondo blanco sólido */
            padding: 8px; /* Mantenido para espaciado interno */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 10px;
            line-height: 1.1;
            overflow: hidden; /* Evita que se desborde */
        }

        .carnet-card > * {
            position: relative;
            z-index: 1; /* Coloca el contenido encima del fondo */
        }

        .carnet-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
        }

        .carnet-header img {
            width: 50px; 
            height: auto;
            margin-left: -240px; /* Revisar este margen, podría necesitar ajuste */
        }

        .carnet-header .school-name {
            font-size: 12px;
            font-weight: bold;
            color: black;
            line-height: 1.2;
            text-align: center;
            flex-grow: 1;
            margin-top: -50px; /* Revisar este margen, podría necesitar ajuste */
        }

        .carnet-header .school-name span {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #800000;
            margin-top: 50px; /* Revisar este margen, podría necesitar ajuste */
        }

        .carnet-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .info-container {
            width: 60%; /* Revisar si este porcentaje sigue siendo adecuado */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info {
            font-size: 10px;
            text-align: left;
            margin: 5px 0; /* MODIFICADO: Increased vertical margin for better distribution */
        }

        .red-text {
            color: red;
            font-weight: 600;
        }

        .blue-text {
            color: blue;
            font-weight: 600;
        }

        /* .photo class styles are now applied to the wrapper or directly to the image inside the wrapper */
        /* Original .photo styles were:
        .photo {
            width: 2cm; 
            height: 3cm; 
            object-fit: cover;
            border-radius: 5px; 
            margin-right: 30px; 
            width: 30%; 
        }
        */

        /* Estilo para el botón de generar imágenes */
        #generateImagesButton {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        #generateImagesButton:hover {
            background-color: #0056b3;
        }

        #generateImagesButton:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
    <?php include './header.php'; ?>

    <br><br><br>
    <div class="container">
        <h2>Ver Carnet del Estudiante</h2>
        <form action="" method="get">
            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo isset($_GET['carnet']) ? htmlspecialchars($_GET['carnet']) : ''; ?>" required />
            <label for="ciclo_escolar">Ciclo Escolar Inscrito</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo isset($_GET['ciclo_escolar']) ? htmlspecialchars($_GET['ciclo_escolar']) : ''; ?>" required />
            <button type="submit" name="buscar">Buscar</button>
        </form>

        <?php
        include_once "../controlador/conexion_bd.php"; 

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) { 
            $carnet_input = isset($_GET["carnet"]) ? trim($_GET["carnet"]) : ''; 
            $ciclo_escolar_input = isset($_GET["ciclo_escolar"]) ? trim($_GET["ciclo_escolar"]) : ''; 

            if (!empty($carnet_input) && !empty($ciclo_escolar_input)) { 
                $consulta = "SELECT * FROM carnet_estudiante WHERE carnet LIKE CONCAT('%', ?, '%') AND fecha_creacion LIKE CONCAT('%', ?, '%')"; 
                $sentencia = $conn->prepare($consulta); 
                $sentencia->bind_param("ss", $carnet_input, $ciclo_escolar_input); 
                $sentencia->execute(); 
                $resultados = $sentencia->get_result(); 

                if ($resultados->num_rows > 0) { 
                    $resultados_array = $resultados->fetch_assoc(); 
        ?>
                    <div class="cards-container">
                        <div class="carnet-card" id="carnetAnverso">
                            <div class="carnet-header">
                                <img src="../img/log_colegio.png" alt="logo"> <span class="school-name">Colegio Privado Mixto</span> <span class="school-name"><span>SUPERACIÓN</span></span>
                            </div>
                            <div class="carnet-content">
                                <div class="info-container">
                                    <p class="info red-text"><strong>CARNET N°:</strong> <?php echo htmlspecialchars($resultados_array['carnet']); ?></p>
                                    <p class="info blue-text"><strong>CARRERA:</strong> <?php echo htmlspecialchars($resultados_array['grado'] . " - " . $resultados_array['nivel']); ?></p>
                                    <p class="info blue-text"><strong>NOMBRE:</strong> <?php echo htmlspecialchars($resultados_array['nombre_completo']); ?></p>
                                </div>
                                <div class="photo-and-seal-wrapper" style="position: relative; width: 2.5cm; height: 2.5cm; margin-right: 10px; flex-shrink: 0;">
                                    <img src="<?php echo htmlspecialchars($resultados_array['foto_estudiante']); ?>" alt="Foto Estudiante" 
                                         style="display: block; width: 100%; height: 100%; object-fit: cover; border-radius: 5px;">
                                    <img src="../img/Sello_Solo.png" alt="Sello Institucional"
                                         style="position: absolute;
                                                bottom: -20px; /* Sello en esquina inferior derecha */
                                                left: -40px;
                                                width: 1.5cm; /* Tamaño del sello - ajustar si es necesario */
                                                height: 1.5cm; /* Puede ser 'auto' si la imagen del sello tiene proporciones fijas */
                                                object-fit: contain; /* Para que el sello se vea completo */
                                                z-index: 10; /* Encima de la foto */">
                                    </div>
                                </div>
                            <p class="info" style="text-align: center;"><strong>Es válido hasta la fecha indicada al reverso del mismo</strong></p>
                        </div>
                        <div class="carnet-card" id="carnetReverso">
                            <p class="info" style="text-align: center;">
                                <strong>Vence el </strong> <?php
                                    if (!empty($resultados_array['fecha_vencimiento'])) { 
                                        $fecha = DateTime::createFromFormat('Y-m-d', $resultados_array['fecha_vencimiento']); 
                                        echo htmlspecialchars($fecha ? $fecha->format('d/m/Y') : 'Fecha inválida'); 
                                    } else {
                                        echo 'No especificada';
                                    }
                                ?>
                            </p>

                            <p class="info" style="text-align: center; font-size: 8px; line-height:1.2;">Suplicamos a las autoridades civiles y militares guardar al portador las consideraciones correspondientes que la ley establece, según los artículos 5° y 6° de la Constitución de Guatemala.</p>
                            <p style="text-align: center;"><strong>Atentamente</strong></p>
                            <p class="info" style="text-align:center; margin-top: 5px; margin-bottom: 0;">
                                <img src="../img/Firma_Solo.png" alt="Firma Autorizada" style="width: 100px; height:auto;">
                                </p>
                            <p class="info" style="text-align: center; font-size: 8px; line-height:1.2;">Este carnet consta de dos portadas, en una de ellas se encuentra la fotografía del alumno con el sello de la institución.</p>
                        </div>
                    </div>
                    <br><br>
                    <div style="text-align: center;">
                        <button type="button" id="generateImagesButton">Generar Imágenes para Imprimir</button>
                    </div>

        <?php
                } else {
                    echo "<p>No se encontraron resultados para el carnet: <strong>" . htmlspecialchars($carnet_input) . "</strong> en el ciclo escolar/fecha: <strong>" . htmlspecialchars($ciclo_escolar_input) . "</strong>.</p>"; 
                }
                $sentencia->close(); 
            } else {
                echo "<p>Por favor, ingrese el número de carnet y el ciclo escolar para buscar.</p>"; 
            }
        }
        $conn->close(); 
        ?>
    </div>

    <script>
        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        const generateButton = document.getElementById('generateImagesButton');
        if (generateButton) {
            generateButton.addEventListener('click', async function() {
                this.disabled = true;
                this.textContent = 'Generando imágenes...';

                const anversoElement = document.getElementById('carnetAnverso');
                const reversoElement = document.getElementById('carnetReverso');

                const targetWidthPx = 1004;
                
                async function captureCard(element, fileName) {
                    if (!element) {
                        alert(`Elemento no encontrado para: ${fileName}`);
                        return;
                    }
                    try {
                        const elementOnScreenWidth = element.offsetWidth;
                        if (elementOnScreenWidth === 0) {
                            throw new Error("El elemento del carnet no es visible o no tiene ancho.");
                        }
                        const scale = targetWidthPx / elementOnScreenWidth;

                        const options = {
                            scale: scale,
                            useCORS: true, 
                            allowTaint: true, 
                            backgroundColor: '#FFFFFF', 
                            imageTimeout: 30000, 
                            logging: false 
                        };

                        const canvas = await html2canvas(element, options);

                        canvas.toBlob(function(blob) {
                            if (blob) {
                                const link = document.createElement('a');
                                link.download = fileName;
                                link.href = URL.createObjectURL(blob);
                                document.body.appendChild(link); 
                                link.click();
                                document.body.removeChild(link);
                                URL.revokeObjectURL(link.href); 
                            } else {
                                throw new Error("No se pudo generar el blob para " + fileName);
                            }
                        }, 'image/png', 1.0); 

                    } catch (error) {
                        console.error(`Error al capturar ${fileName}:`, error);
                        alert(`Error al generar la imagen para ${fileName}: ${error.message}`);
                        throw error; 
                    }
                }

                try {
                    if (anversoElement) {
                        await captureCard(anversoElement, 'carnet_anverso_300dpi.png');
                    } else {
                        alert("No se encontró el anverso del carnet para capturar.");
                    }

                    await delay(500);

                    if (reversoElement) {
                        await captureCard(reversoElement, 'carnet_reverso_300dpi.png');
                    } else {
                        alert("No se encontró el reverso del carnet para capturar.");
                    }

                } catch (error) {
                    console.log("Proceso de generación de imágenes finalizado con errores.");
                } finally {
                    generateButton.disabled = false;
                    generateButton.textContent = 'Generar Imágenes para Imprimir';
                }
            });
        }
    </script>

</body>
</html>