<?php
include_once '../../php/conexion.php';
//header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: index.html");
    exit;
}

$rol = $_SESSION['rol'];
$mostrarExcusas = ($rol === "Directivo" || $rol === "Director de Unidad");
$mostrarValidacion = ($rol === "Director de Unidad");

$data = [];
$cursos = [];

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
        cae.id_curs_asig_es,
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
    WHERE exc.estado_excu = 3;
");


        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtCursos = $conn->prepare("
        SELECT DISTINCT cae.curso
            FROM excusas AS exc
            INNER JOIN t_v_exc_asig_mat_est AS cae 
            ON exc.num_doc_estudiante = cae.est_codigo_unico 
            AND exc.id_curs_asig_es = cae.id_curs_asig_es");
        $stmtCursos->execute();
        $cursos = $stmtCursos->fetchAll(PDO::FETCH_COLUMN);
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
    <link rel="icon" type="image/x-icon" href="/Images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/Images/favicon.ico">
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
                <select class="form-select" name="id_curs_asig_es" id="id_curs_asig_es" required disabled>
                    <option value="">Seleccione un curso</option>
                    <?php foreach ($asignaturas_estudiante as $asig): ?>
                        <!--trim para limpiar espacios invisibles al importar de bd-->
                        <option value="<?= trim($asig[' id_curs_asig_es ']) ?>">
                            <?= trim($asig[' nombre_asignatura ']) . ' (' . trim($asig[' letra_grupo ']) . ') -' . trim($asig[' jornada ']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>


                <div>
                    <br>
                    <label for="fecha">Fecha de la Excusa:</label>
                    <input type="date" id="fecha" name="fecha" required>
                    <br><br>
                </div>

                <label>Tipo de Excusa:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="excuseDocument" value="1" onclick="mostrarInput('otroInput')">
                    <label class="form-check-label" for="excuseDocument">Por Salud</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="excuseDocument" value="2" onclick="mostrarInput('otroInput')">
                    <label class="form-check-label" for="excuseDocument">Laboral</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="excuseDocument" value="3" onclick="mostrarInput('otroInput')">
                    <label class="form-check-label" for="excuseDocument">Otro</label>
                </div>
                <div id="otroInput" style="display: none;">
                    <label for="otroExplanation">Explique cuál:</label>
                    <input type="text" id="otroExplanation" name="otroExplanation" class="form-control" required>
                </div>
                <br>

                <label for="excuseReason">Motivo de la excusa:</label>
                <small>Si la excusa es por más de un día, indicar aqui la cantidad.</small>
                <input type="text" id="excuseReason" name="excuseReason" class="form-control" required>
                <br>

                <div>
                    <label for="soporteExcusa">Subir soporte de la Excusa: </label>
                    <input type="file" name="file" id="fileInput" class="form-control" accept=".pdf,.zip,.jpg,.png" required>
                    <small>El archivo no puede superar los 10MB. (Solo se aceptan archivos en formato .pdf,.zip,.jpg,.png).</small>
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
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= htmlspecialchars($curso) ?>"><?= htmlspecialchars($curso) ?></option>
                    <?php endforeach; ?>
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
                                <td><a href="<?= htmlspecialchars($dat['soporte_excu']) ?>" target="_blank">Ver soporte</a></td>
                                <td><?= htmlspecialchars($dat['id_estudiante']) ?></td>
                                <td><?= htmlspecialchars($dat['nombre_estudiante']) ?></td>
                                <td><?= htmlspecialchars($dat['descripcion_excu']) ?></td>
                                <td><?= htmlspecialchars($dat['curso']) ?></td>
                                <td><?= htmlspecialchars($dat['id_curs_asig_es']) ?></td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="approvalRadio_<?= $dat['id_excusa'] ?>" value="1"
                                            <?= $dat['estado_excu'] == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="approvalRadio_<?= $dat['id_excusa'] ?>">Aprobar</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="approvalRadio_<?= $dat['id_excusa'] ?>" value="2"
                                            <?= $dat['estado_excu'] == 2 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="approvalRadio_<?= $dat['id_excusa'] ?>">Denegar</label>
                                    </div>
                                </td>


                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

                <button class="btn btn-primary" onclick="guardarCambios()">Guardar</button><br><br><br>
                <a href="./principal.php" class="btn btn-secondary">Volver al inicio</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cambiosSeleccionados = [];

function guardarCambios() {
        const radiosSeleccionados = document.querySelectorAll('input[type="radio"]:checked');
        cambiosSeleccionados = [];

        radiosSeleccionados.forEach(radio => {
            const name = radio.name;
            const id_excusa = name.split('_')[1];
            const estado = radio.value;

            const justificacion = prompt(`Ingrese la justificación para la excusa #${id_excusa} (puede dejarlo vacío):`) || '';

            cambiosSeleccionados.push({ id_excusa, estado, justificacion });
        });

        if (cambiosSeleccionados.length === 0) {
            alert('No hay cambios seleccionados.');
            return;
        }

        enviarCambios();
    }

    function enviarCambios() {
        fetch('../../php/actualizar_estado_excusa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cambios: cambiosSeleccionados })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Cambios guardados y correos enviados.');
                location.reload();
            } else {
                alert('Error al guardar: ' + data.mensaje);
            }
        })
        .catch(err => {
            console.error('Error al enviar los datos:', err);
            alert('Error al procesar la solicitud');
        });
    }



        //obtener cursos existentes de la vista
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
            const curso = document.getElementById('id_curs_asig_es').value.trim();
            const fecha = document.getElementById('fecha').value.trim();
            const motivo = document.getElementById('excuseReason').value.trim();
            const tipoExcusa = document.querySelector('input[name="excuseDocument"]:checked');
            const otroTipo = document.getElementById('otroExplanation').value.trim();
            const archivo = document.querySelector('input[type="file"]').files[0];

            if (!studentid || !curso || !fecha || !motivo || !tipoExcusa || !archivo) {
                alert("Complete todos los campos");
                return;
            }

            const fileData = new FormData();
            fileData.append('file', archivo);

            fetch('../../php/uploadFiles.php', {
                    method: 'POST',
                    body: fileData
                })
                .then(res => res.json())
                .then(uploadResp => {

                    if (!uploadResp.success || !uploadResp.url) {
                        throw new Error('Error al subir archivo: ' + uploadResp.mensaje);
                    }

                    const formData = new FormData();
                    formData.append('num_doc_estudiante', studentid);
                    formData.append('id_curs_asig_es', curso);
                    formData.append('fecha_falta_excu', fecha);
                    formData.append('tipo_excu', tipoExcusa.value);
                    formData.append('otro_tipo_excu', otroTipo);
                    formData.append('descripcion_excu', motivo);
                    formData.append('soporte_excu', uploadResp.url);

                    return fetch('../../php/registrar_excusa_docente.php', {
                        method: 'POST',
                        body: formData
                    });
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error HTTP al registrar excusa: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Excusa registrada correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + data.mensaje);
                    }
                })

        }

        

        function mostrarInput(id) {
            const input = document.getElementById(id);
            const selectedValue = document.querySelector('input[name="excuseDocument"]:checked').value;

            if (selectedValue === "3") { // Otro
                input.style.display = "block";
                document.getElementById('otroExplanation').required = true;
            } else {
                input.style.display = "none";
                document.getElementById('otroExplanation').required = false;
            }
        }


        //async para obtener cursos actuales de estudiante

        document.getElementById('studentId').addEventListener('blur', function() {
            const studentId = this.value.trim();
            const courseSelect = document.getElementById('id_curs_asig_es');

            if (studentId === "") {
                courseSelect.innerHTML = '<option value="">Ingrese la cédula</option>';
                courseSelect.disabled = true;
                return;
            }

            const formData = new FormData();
            formData.append('num_doc_estudiante', studentId);

            fetch('../../php/obtener_cursos_estudiantes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta recibida:", data);
                    if (data.success && data.cursos.length > 0) {
                        courseSelect.innerHTML = '<option value="">Seleccione un curso</option>';
                        data.cursos.forEach(curso => {
                            const option = document.createElement('option');
                            option.value = curso.id_curs_asig_es; // valor real que se guardará
                            option.textContent = curso.curso; // nombre visible
                            courseSelect.appendChild(option);
                        });
                        courseSelect.disabled = false;
                    } else {
                        courseSelect.innerHTML = '<option value="">No se encontraron cursos</option>';
                        courseSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    courseSelect.innerHTML = '<option value="">Error al cargar cursos</option>';
                    courseSelect.disabled = true;
                });
        });

        

    </script>
</body>

<div class="modal fade" id="modalJustificacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar nota adicional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="textoJustificacion" class="form-control" rows="4" placeholder="Escriba una nota para el docente (opcional)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarCambios()">Enviar</button>
            </div>
        </div>
    </div>
</div>


</html>