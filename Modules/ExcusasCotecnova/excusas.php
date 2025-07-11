<?php
include_once '../../php/conexion.php';

session_start();

// Verifica si la sesión está iniciada y tiene rol asignado
if (!isset($_SESSION['rol'])) {
    header("Location: index.html");
    exit;
}

$rol = $_SESSION['rol']; 
$mostrarExcusas = ($rol === "Directivo" || $rol === "Director de Unidad");
$mostrarValidacion = ($rol === "Director de Unidad");

// Cargar datos de la tabla si el usuario tiene permiso
$data = [];
if ($mostrarValidacion) {
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
                    est.programa_estudiante AS programa
                FROM excusas AS exc
                INNER JOIN estudiantes AS est 
                    ON exc.num_doc_estudiante = est.num_doc_estudiante
                INNER JOIN t_v_exc_asig_mat_est AS cae 
                    ON exc.num_doc_estudiante = cae.est_codigo_unico
                INNER JOIN tiposexcusas AS tex 
                    ON exc.tipo_excu = tex.id_tipo_excu;

        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al obtener los datos: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Director De Unidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../CSS/ExcusasCotecnova/excusas.css">
</head>

<body>
    <div class="container">
        <div class="centered-content">
            <h1>Inicio Director De Unidad</h1>
            <!--<p><strong>Rol actual:</strong> <?= htmlspecialchars($rol) ?></p>-->
        </div>

        <img src="../../Images/cotecnovaLogo.png" alt="imagen" width="150" height="150">

        <!-- Módulo de Registrar Excusas -->
        <?php if ($mostrarExcusas): ?>
        <div class="excuse-form">
            <h2>Registrar Excusas</h2>
            <label for="studentId">Cédula del Estudiante:</label>
            <input type="text" id="studentId" name="studentId" class="form-control" required>

            <label for="selectCourse">Seleccionar Curso:</label>
            <select id="selectCourse" name="selectCourse" class="form-select" onchange="filtrarExcusas()" required>
                <option value="Todos"></option>
                <option value="Curso 1">Curso 1</option>
                <option value="Curso 2">Curso 2</option>
                <option value="Curso 3">Curso 3</option>
                <option value="Curso 4">Curso 4</option>
                <option value="Curso 5">Curso 5</option>
            </select>

            <div>
              <br>
              <label for="fecha">Fecha de la Excusa:</label>
              <input type="date" id="fecha" name="fecha" required>
              <br><br>
            </div>

            <label>Documentos que justifican mi inasistencia:</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="excuseDocument" value="Excusa Médica EPS" onclick="mostrarInput('otroInput')">
                <label class="form-check-label" for="excuseDocument">Excusa Médica EPS</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="excuseDocument" value="Soporte laboral" onclick="mostrarInput('otroInput')">
                <label class="form-check-label" for="excuseDocument">Soporte laboral</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="excuseDocument" value="Otro" onclick="mostrarInput('otroInput')">
                <label class="form-check-label" for="excuseDocument">Otro</label>
            </div>
            <div id="otroInput" style="display: none;">
                <label for="otroExplanation">Explique cuál:</label>
                <input type="text" id="otroExplanation" name="otroExplanation" class="form-control" required>
            </div>
            <br>

            <label for="excuseReason">Motivo de la excusa:</label>
            <input type="text" id="excuseReason" name="excuseReason" class="form-control" required>

            <div>
                <label for="soporteExcusa">Subir soporte de la Excusa: </label>
                <input type="file" name="excuseDocument" class="form-control" required>
            </div>
            <br>

            <button class="btn btn-primary" onclick="registrarExcusa()">Registrar Excusa</button>
        </div>
        <?php endif; ?>

        <!-- Módulo de Validar y Ver Historial -->
        <?php if ($mostrarValidacion): ?>
        <div class="validate-form">
            <br><br>
            <h2>Validar y Ver Historial</h2>
            <label for="selectCourseValidate">Seleccionar Curso:</label>
            <select id="selectCourseValidate" name="selectCourseValidate" class="form-select" onchange="filtrarExcusas()">
                <option value="Todos">Todos</option>
                <option value="Curso 1">Curso 1</option>
                <option value="Curso 2">Curso 2</option>
                <option value="Curso 3">Curso 3</option>
                <option value="Curso 4">Curso 4</option>
                <option value="Curso 5">Curso 5</option>
            </select>
            <br>

            <table id="excuseTable" class="table">
                <thead>
                    <tr>
                        <th>Número Excusa</th>
                        <th>Fecha</th>
                        <th>Fecha de Radicado</th>
                        <th>Tipo de Excusa</th>
                        <th>Soporte</th>
                        <th>Número de Documento</th>
                        <th>Nombre del Estudiante</th>
                        <th>Observaciones</th>
                        <th>Curso</th>
                        <th>Programa</th>
                        <th>Aprobar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $dat): ?>
                    <tr data-curso="<?= htmlspecialchars($dat['curso']) ?>">
                        <td><?= htmlspecialchars($dat['id_excusa']) ?></td>
                        <td><?= htmlspecialchars($dat['fecha_falta_excu']) ?></td>
                        <td><?= htmlspecialchars($dat['fecha_radicado_excu']) ?></td>
                        <td><?= htmlspecialchars($dat['tipo_excu']) ?></td>
                        <td><a href="../../Images/soporte.png" target="_blank"><?= htmlspecialchars($dat['soporte_excu']) ?></a></td>
                        <td><?= htmlspecialchars($dat['id_estudiante']) ?></td>
                        <td><?= htmlspecialchars($dat['nombre_estudiante']) ?></td>
                        <td><?= htmlspecialchars($dat['descripcion_excu']) ?></td>
                        <td><?= htmlspecialchars($dat['curso']) ?></td>
                        <td><?= htmlspecialchars($dat['id_curs_asig_es']) ?></td>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="approvalRadio_<?= $dat['id_excusa'] ?>" value="Aprobar">
                                <label class="form-check-label" for="approvalRadio_<?= $dat['id_excusa'] ?>">Aprobar</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="approvalRadio_<?= $dat['id_excusa'] ?>" value="Denegar">
                                <label class="form-check-label" for="approvalRadio_<?= $dat['id_excusa'] ?>">Denegar</label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>

            <button class="btn btn-primary" onclick="guardarCambios()">Guardar</button><br><br><br>
            <a href=".." class="btn btn-secondary">Volver al inicio</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function guardarCambios() {
            alert("Cambios guardados correctamente");
            location.reload();
        }

        function filtrarExcusas() {
            var selectedCurso = document.getElementById('selectCourseValidate').value;
            var table = document.getElementById('excuseTable');
            var rows = table.getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var rowCurso = rows[i].getAttribute('data-curso');
                rows[i].style.display = (selectedCurso === 'Todos' || selectedCurso === rowCurso) ? '' : 'none';
            }
        }

        function registrarExcusa() {
            const studentid = document.getElementById('studentId').value.trim();
            const curso = document.getElementById('selectCourse').value.trim();
            const fecha = document.getElementById('fecha').value.trim();
            const motivo = document.getElementById('excuseReason').value.trim();
            if (studentid === "" || curso === "" || fecha === "" || motivo === "") {
                alert("Complete Todos los Campos");
            } else {
                alert("Excusa registrada correctamente");
                location.reload();
            }
        }

        function mostrarInput(id) {
            const input = document.getElementById(id);
            input.style.display = "block";
        }
    </script>
</body>
</html>
