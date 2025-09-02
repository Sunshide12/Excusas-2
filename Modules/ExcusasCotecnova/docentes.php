<?php
include_once '../../php/conexion.php';
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: index.html");
    exit;
}

$nombreDocente = $_SESSION['nombre_empleado'];
$primeraParte = explode(' ', $nombreDocente)[0];
$docenteLike = "%" . strtoupper($primeraParte) . "%";

$data = [];
$cursos = [];

try {
    // Cursos del docente
    $stmtCursos = $conn->prepare("
        SELECT DISTINCT cae.id_curs_asig_es, cae.curso
        FROM t_v_exc_asig_mat_est AS cae
        WHERE UPPER(CONCAT_WS(' ', cae.profe_nombre, cae.profe_snombre, cae.profe_apellido, cae.profe_sapellido)) LIKE :docente
        ORDER BY cae.curso
    ");
    $stmtCursos->bindParam(':docente', $docenteLike, PDO::PARAM_STR);
    $stmtCursos->execute();
    $cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

    // Excusas de esos cursos
    $stmt = $conn->prepare("
        SELECT 
            exc.id_excusa,
            exc.id_curs_asig_es,
            exc.fecha_falta_excu,
            exc.fecha_radicado_excu,
            tex.tipo_excu,
            est.num_doc_estudiante AS id_estudiante,
            est.nombre_estudiante AS nombre_estudiante,
            cae.curso,
            est.programa_estudiante AS programa,
            exc.estado_excu
        FROM excusas AS exc
        INNER JOIN estudiantes AS est 
            ON exc.num_doc_estudiante = est.num_doc_estudiante
        INNER JOIN t_v_exc_asig_mat_est AS cae
            ON exc.num_doc_estudiante = cae.est_codigo_unico
            AND exc.id_curs_asig_es = cae.id_curs_asig_es
        INNER JOIN tiposexcusas AS tex 
            ON exc.tipo_excu = tex.id_tipo_excu
        WHERE UPPER(CONCAT_WS(' ', cae.profe_nombre, cae.profe_snombre, cae.profe_apellido, cae.profe_sapellido)) LIKE :docente
        ORDER BY exc.fecha_radicado_excu DESC
    ");
    $stmt->bindParam(':docente', $docenteLike, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio Docente</title>
    <link rel="icon" type="image/x-icon" href="../../Images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="../../Images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1>Inicio Docente</h1>
        <img src="../../Images/cotecnovaLogo.png" alt="imagen" width="150" height="150">

        <div class="validate-form">
            <h2>Ver Historial</h2>

            <label for="selectCourseValidate">Seleccionar Curso:</label>
            <select id="selectCourseValidate" class="form-select" onchange="filtrarExcusas()">
                <option value="Todos">Todos</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= (int)$curso['id_curs_asig_es'] ?>"><?= htmlspecialchars($curso['curso']) ?></option>
                <?php endforeach; ?>
            </select>

            <br>
            <table id="excuseTable" class="table table-striped">
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
                        <tr data-idcurso="<?= (int)$dat['id_curs_asig_es'] ?>">
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
                                    case 1:
                                        echo 'Aprobada';
                                        break;
                                    case 2:
                                        echo 'Denegada';
                                        break;
                                    case 3:
                                        echo 'Pendiente';
                                        break;
                                    default:
                                        echo 'Desconocido';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="./principal.php" class="btn btn-secondary">Volver al inicio</a>
        </div>
    </div>

    <script>
        function filtrarExcusas() {
            const selected = document.getElementById('selectCourseValidate').value;
            document.querySelectorAll('#excuseTable tbody tr').forEach(row => {
                const idCurso = row.getAttribute('data-idcurso');
                row.style.display = (selected === 'Todos' || selected === idCurso) ? '' : 'none';
            });
        }
    </script>
</body>

</html>