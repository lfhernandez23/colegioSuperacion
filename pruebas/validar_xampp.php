<?php
/**
 * Diagnóstico de solo lectura para el proyecto colegioSuperacion.
 *
 * Ejecutar DESDE UNA TERMINAL, nunca desde el navegador:
 *   php pruebas/validar_xampp.php
 *
 * Requiere PHP CLI, extensión mysqli y la configuración local
 * de controlador/conexion_bd.php. No inserta, actualiza ni elimina datos.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso restringido. Ejecutar desde una terminal PHP CLI.\n");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require_once __DIR__ . '/../controlador/conexion_bd.php';
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('La conexión mysqli no está disponible.');
    }
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: No se pudo abrir la base de datos local. Verifique Apache/MySQL de XAMPP y controlador/conexion_bd.php.\n");
    exit(1);
}

echo "VALIDACIÓN XAMPP - COLEGIO SUPERACIÓN\n";
echo "Modo: solo lectura (no modifica registros)\n\n";

$failures = 0;
$issues = 0;

$required = [
    'registro_estudiantes' => ['id', 'carnet', 'codigo', 'nombre', 'apellido', 'jornada', 'nivel', 'grado', 'ciclo_escolar', 'fecha_inscripcion'],
    'control_pagos' => ['id_pago', 'serie_recibo', 'num_autorizacion', 'carnet', 'ciclo_escolar', 'tipo_pago', 'monto', 'fecha_pago', 'mes_pago'],
    'cursos' => ['id', 'jornada', 'nivel', 'grado', 'descripcion_nivel', 'curso'],
    'asignacion_cursos' => ['id', 'id_estudiante', 'carnet', 'id_curso', 'ciclo_escolar'],
    'calificaciones' => ['id', 'id_estudiante', 'id_curso', 'ciclo_escolar', 'bimestre_1', 'bimestre_2', 'bimestre_3', 'bimestre_4'],
    'carnet_estudiante' => ['id', 'carnet'],
    'seguridadcontrolacceso' => ['idpermiso', 'nombrepermiso'],
    'usuarios' => ['idusuario', 'nombreusuario', 'contrasena', 'correoelectronico'],
];

echo "1) TABLAS Y COLUMNAS\n";
try {
    $stmt = $conn->prepare(
        'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS ' .
        'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    foreach ($required as $table => $columns) {
        $stmt->bind_param('s', $table);
        $stmt->execute();
        $result = $stmt->get_result();
        $present = [];
        while ($row = $result->fetch_assoc()) {
            $present[] = $row['COLUMN_NAME'];
        }
        $missing = array_values(array_diff($columns, $present));
        if ($missing) {
            $failures++;
            echo "[ERROR] {$table}: faltan columnas " . implode(', ', $missing) . "\n";
        } else {
            echo "[OK] {$table}: columnas requeridas presentes\n";
        }
    }
    $stmt->close();
} catch (Throwable $e) {
    $failures++;
    echo "[ERROR] No se pudo comprobar la estructura de tablas.\n";
}

echo "\n2) CANTIDAD DE REGISTROS\n";
foreach (array_keys($required) as $table) {
    try {
        // El nombre viene de una lista fija en este archivo, nunca de datos externos.
        $result = $conn->query('SELECT COUNT(*) AS total FROM `' . $table . '`');
        $total = (int) $result->fetch_assoc()['total'];
        echo "[OK] {$table}: {$total}\n";
    } catch (Throwable $e) {
        $failures++;
        echo "[ERROR] {$table}: no se pudo contar.\n";
    }
}

echo "\n3) INTEGRIDAD Y COMPATIBILIDAD\n";
$checks = [
    'Inscripciones duplicadas (carnet + ciclo)' =>
        "SELECT COUNT(*) AS total FROM (SELECT carnet, ciclo_escolar FROM registro_estudiantes GROUP BY carnet, ciclo_escolar HAVING COUNT(*) > 1) AS duplicadas",
    'Pagos sin inscripción coincidente' =>
        "SELECT COUNT(*) AS total FROM control_pagos cp WHERE NOT EXISTS (SELECT 1 FROM registro_estudiantes re WHERE re.carnet = cp.carnet AND re.ciclo_escolar = cp.ciclo_escolar)",
    'Asignaciones sin estudiante (por ID)' =>
        "SELECT COUNT(*) AS total FROM asignacion_cursos ac WHERE NOT EXISTS (SELECT 1 FROM registro_estudiantes re WHERE re.id = ac.id_estudiante)",
    'Asignaciones sin curso' =>
        "SELECT COUNT(*) AS total FROM asignacion_cursos ac WHERE NOT EXISTS (SELECT 1 FROM cursos c WHERE c.id = ac.id_curso)",
    'Calificaciones sin estudiante' =>
        "SELECT COUNT(*) AS total FROM calificaciones cal WHERE NOT EXISTS (SELECT 1 FROM registro_estudiantes re WHERE re.id = cal.id_estudiante)",
    'Calificaciones sin curso' =>
        "SELECT COUNT(*) AS total FROM calificaciones cal WHERE NOT EXISTS (SELECT 1 FROM cursos c WHERE c.id = cal.id_curso)",
    'Calificaciones sin asignación correspondiente' =>
        "SELECT COUNT(*) AS total FROM calificaciones cal WHERE NOT EXISTS (SELECT 1 FROM asignacion_cursos ac WHERE ac.id_estudiante = cal.id_estudiante AND ac.id_curso = cal.id_curso AND ac.ciclo_escolar = cal.ciclo_escolar)",
    'Jornadas desconocidas en inscripciones' =>
        "SELECT COUNT(*) AS total FROM registro_estudiantes WHERE jornada NOT IN ('diario-matutino', 'diario-vespertino', 'fin-de-semana')",
    'Niveles desconocidos en inscripciones' =>
        "SELECT COUNT(*) AS total FROM registro_estudiantes WHERE nivel NOT IN ('preprimaria', 'primaria', 'basico', 'basicopormadurez', 'diversificado1', 'diversificado2', 'diversificado3', 'diversificado4')",
    'Grados desconocidos en inscripciones' =>
        "SELECT COUNT(*) AS total FROM registro_estudiantes WHERE grado NOT IN ('1grado','2grado','3grado','4grado','5grado','6grado')",
];

foreach ($checks as $label => $sql) {
    try {
        $result = $conn->query($sql);
        $value = (int) $result->fetch_assoc()['total'];
        if ($value > 0) {
            $issues++;
            echo "[REVISAR] {$label}: {$value}\n";
        } else {
            echo "[OK] {$label}: 0\n";
        }
    } catch (Throwable $e) {
        $failures++;
        echo "[ERROR] {$label}: consulta no ejecutada.\n";
    }
}

echo "\n4) CONSULTAS REPRESENTATIVAS (PREPARADAS)\n";
$samples = [
    "SELECT id, nombre, apellido, jornada, nivel, grado FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ? LIMIT 1",
    "SELECT id, curso FROM cursos WHERE jornada = ? AND nivel = ? AND grado = ? LIMIT 1",
    "SELECT cp.id_pago, cp.monto FROM control_pagos cp WHERE cp.carnet = ? AND cp.ciclo_escolar = ? LIMIT 1",
];
foreach ($samples as $index => $sql) {
    try {
        $stmt = $conn->prepare($sql);
        $a = '__prueba__';
        $b = '__prueba__';
        $c = '__prueba__';
        if ($index === 1) {
            $stmt->bind_param('sss', $a, $b, $c);
        } else {
            $stmt->bind_param('ss', $a, $b);
        }
        $stmt->execute();
        $stmt->get_result()->free();
        $stmt->close();
        echo "[OK] Consulta preparada " . ($index + 1) . "\n";
    } catch (Throwable $e) {
        $failures++;
        echo "[ERROR] Consulta preparada " . ($index + 1) . "\n";
    }
}

$conn->close();
echo "\nRESUMEN: {$failures} errores técnicos; {$issues} grupos de datos que requieren revisión.\n";
echo "No se han escrito datos. Las pruebas de interfaz y autenticación aún deben realizarse en el navegador.\n";
exit($failures === 0 ? 0 : 1);
