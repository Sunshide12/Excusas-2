<?php
include_once '../../php/conexion.php';

session_start();

// Verifica si la sesión está iniciada y tiene rol asignado
if (!isset($_SESSION['rol'])) {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
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
                    <!-- Datos de ejemplo para llenar la tabla -->
                    <tr data-curso="Curso 1">
                        <td>1</td>
                        <td>2023-01-01</td>
                        <td>2023-01-02</td>
                        <td>Médica</td>                        
                        <td>321321312</td>
                        <td>Carlos Humberto</td>
                        <td>Curso 1</td>
                        <td>Ingeniería en Sistemas</td>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label" for="approvalRadio">
                              Aprobada
                            </label>
                            </div>
                        </td>
                    </tr>

                    <tr data-curso="Curso 2">
                        <td>2</td>
                        <td>2023-01-01</td>
                        <td>2023-01-02</td>
                        <td>Médica</td>
                        <td>123456789</td>
                        <td>Juan Pérez</td>
                        <td>Curso 2</td>
                        <td>Ingeniería en Sistemas</td>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label" for="approvalRadio">
                            Aprobada
                              </label>
                            </div>
                        </td>
                    </tr>

                    <tr data-curso="Curso 3">
                        <td>3</td>
                        <td>2023-01-01</td>
                        <td>2023-01-02</td>
                        <td>Médica</td>
                        <td>123456789</td>
                        <td>rodrigo mendez</td>
                        <td>Curso 3</td>
                        <td>Ingeniería en Sistemas</td>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label" for="approvalRadio">
                          Aprobada
                            </label>
                            </div>
                        </td>
                    </tr>

                    <tr data-curso="Curso 4">
                        <td>4</td>
                        <td>2023-01-01</td>
                        <td>2023-01-02</td>
                        <td>Médica</td>
                        <td>123456789</td>
                        <td>mariana rodriguez</td>
                        <td>Curso 4</td>
                        <td>Ingeniería en Sistemas</td>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label" for="approvalRadio">
                        Aprobada
                          </label>
                        </td>
                    </tr>

                    <tr data-curso="Curso 5">
                        <td>5</td>
                        <td>2023-01-01</td>
                        <td>2023-01-02</td>
                        <td>Médica</td>
                        <td>123456789</td>
                        <td>daniel martinez</td>
                        <td>Curso 5</td>
                        <td>Ingeniería en Sistemas</td>
                        <td>
                            <div class="form-check">
                                <label class="form-check-label" for="approvalRadio">
                      Aprobada
                        </label>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

            <a href=".." class="btn btn-secondary">Volver al inicio</a>
            </div>


        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script>
            
            function filtrarExcusas() {
                var selectedCurso = document.getElementById('selectCourse').value;
                var table = document.getElementById('excuseTable');
                var rows = table.getElementsByTagName('tr');

                for (var i = 1; i < rows.length; i++) {
                    var rowCurso = rows[i].getAttribute('data-curso');
                    if (selectedCurso === 'Todos' || selectedCurso === rowCurso) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }

        </script>
</body>

</html>