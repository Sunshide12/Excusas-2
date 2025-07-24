<?php
include_once '../../php/conexion.php';

session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: index.html");
    exit;
}

$data = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            exc.id_excusa,
            exc.fecha_falta_excu,
            exc.fecha_radicado_excu,
            tex.tipo_excu,
            exc.soporte_excu,
            est.num_doc_estudiante AS id_estudiante,
            est.nombre_estudiante AS nombre_estudiante,
            exc.descripcion_excu,
            cae.curso,
            est.programa_estudiante AS programa,
            exc.estado_excu
        FROM excusas AS exc
        INNER JOIN estudiantes AS est 
            ON exc.num_doc_estudiante = est.num_doc_estudiante
        INNER JOIN (
    SELECT DISTINCT id_curs_asig_es, curso, est_codigo_unico
    FROM t_v_exc_asig_mat_est
) AS cae 
    ON exc.num_doc_estudiante = cae.est_codigo_unico
    AND exc.id_curs_asig_es = cae.id_curs_asig_es

        INNER JOIN tiposexcusas AS tex 
            ON exc.tipo_excu = tex.id_tipo_excu
        WHERE exc.estado_excu IN (1, 2)
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../CSS/ExcusasCotecnova/docentes.css">
    <title>Inicio Docente</title>
</head>

<body>
    <div class="container">
        <div class="centered-content">
            <h1>Inicio Docente</h1>
        </div>

        <img src="../../Images/cotecnovaLogo.png" alt="imagen" width="150" height="150">
        <br>
        <!-- Módulo de Ver Historial -->
        <div class="validate-form">
            <br>
            <h2>Ver Historial</h2>
            <br>
            <label for="selectCourse">Seleccionar Curso:</label>
            <select id="selectCourse" name="selectCourse" class="form-select" onchange="filtrarExcusas()">
                <option value="Todos">Todos</option>
                <option value="Curso 1">Curso 1</option>
                <option value="Curso 2">Curso 2</option>
                <option value="Curso 3">Curso 3</option>
                <option value="Curso 4">Curso 4</option>
                <option value="Curso 5">Curso 5</option>
            </select>

            <!-- Tabla con historial de excusas -->
            <table id="excuseTable" class="table">
                <thead>
                    <tr>
                        <th>Número Excusa</th>
                        <th>Fecha</th>
                        <th>Fecha de Radicado</th>
                        <th>Tipo de Excusa</th>
                        <th>Número de Documento</th>
                        <th>Nombre del Estudiante</th>
                        <th>Curso</th>
                        <th>Programa</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $dat): ?>
                        <tr data-curso="<?= htmlspecialchars($dat['curso']) ?>">
                            <td><?= htmlspecialchars($dat['id_excusa']) ?></td>
                            <td><?= htmlspecialchars($dat['fecha_falta_excu']) ?></td>
                            <td><?= htmlspecialchars($dat['fecha_radicado_excu']) ?></td>
                            <td><?= htmlspecialchars($dat['tipo_excu']) ?></td>
                            <td><?= htmlspecialchars($dat['id_estudiante']) ?></td>
                            <td><?= htmlspecialchars($dat['nombre_estudiante']) ?></td>
                            <td><?= htmlspecialchars($dat['curso']) ?></td>
                            <td><?= htmlspecialchars($dat['programa']) ?></td>
                            <td>
                                <?php
                                    switch ($dat['estado_excu']) {
                                        case 1: echo 'Aprobada'; break;
                                        case 2: echo 'Denegada'; break;
                                        case 3: echo 'Pendiente'; break;
                                        default: echo 'Desconocido';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href=".." class="btn btn-secondary">Volver al inicio</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filtrarExcusas() {
            var selectedCurso = document.getElementById('selectCourse').value;
            var table = document.getElementById('excuseTable');
            var rows = table.getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var rowCurso = rows[i].getAttribute('data-curso');
                rows[i].style.display = (selectedCurso === 'Todos' || selectedCurso === rowCurso) ? '' : 'none';
            }
        }
    </script>
</body>

</html>