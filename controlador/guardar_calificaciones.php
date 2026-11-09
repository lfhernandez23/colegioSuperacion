<?php require_once __DIR__ . '/auth.php'; colegio_requerir_sesion(); ?>
<?php
include_once "../controlador/conexion_bd.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_estudiante"]) && isset($_POST["ciclo_escolar"])) {
    $id_estudiante = intval($_POST["id_estudiante"]);
    $ciclo_escolar = htmlspecialchars($_POST["ciclo_escolar"]);
    $id_curso = $_POST["id_curso"] ?? [];
    $bimestre_1 = $_POST["bimestre_1"] ?? [];
    $bimestre_2 = $_POST["bimestre_2"] ?? [];
    $bimestre_3 = $_POST["bimestre_3"] ?? [];
    $bimestre_4 = $_POST["bimestre_4"] ?? [];

    foreach ($id_curso as $index => $curso_id) {
        $curso_id = intval($curso_id);
        $b1 = isset($bimestre_1[$index]) ? floatval($bimestre_1[$index]) : NULL;
        $b2 = isset($bimestre_2[$index]) ? floatval($bimestre_2[$index]) : NULL;
        $b3 = isset($bimestre_3[$index]) ? floatval($bimestre_3[$index]) : NULL;
        $b4 = isset($bimestre_4[$index]) ? floatval($bimestre_4[$index]) : NULL;

        $sql = "INSERT INTO calificaciones (id_estudiante, id_curso, ciclo_escolar, bimestre_1, bimestre_2, bimestre_3, bimestre_4) 
                VALUES (?, ?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    bimestre_1 = VALUES(bimestre_1), 
                    bimestre_2 = VALUES(bimestre_2), 
                    bimestre_3 = VALUES(bimestre_3), 
                    bimestre_4 = VALUES(bimestre_4)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssss", $id_estudiante, $curso_id, $ciclo_escolar, $b1, $b2, $b3, $b4);
        
        if (!$stmt->execute()) {
            echo "<p style='color:red; text-align:center;'>Error al guardar calificaciones: " . $stmt->error . "</p>";
        }
    }

    $mensaje = "Calificaciones Guardadas de Forma Exitosa";
    echo "<script>alert('$mensaje'); window.history.go(-2);</script>";  // Retroceder dos páginas
} else {
    echo "<p style='color:red; text-align:center;'>Error en el envío de datos.</p>";
}
?>
