<?php
session_start();
require_once __DIR__ . '/../../php/conexion.php';

// Verifica sesión
if (empty($_SESSION['estudiante_id'])) {
    header('Location: index.html');
    exit;
}

$estudiante_id = $_SESSION['estudiante_id'];
$cursos = [];

try {
    $stmt = $conn->prepare("
        SELECT
            tvexc.id_curs_asig_es,
            tvexc.curso,
            tvexc.creditos,
             CONCAT(
                    COALESCE(tvexc.profe_nombre, ''), ' ',
                    COALESCE(tvexc.profe_snombre, ''), ' ',
                    COALESCE(tvexc.profe_apellido, ''), ' ',
                    COALESCE(tvexc.profe_sapellido, '')
                ) AS docente,
            tvexc.aula
        FROM t_v_exc_asig_mat_est AS tvexc
        INNER JOIN estudiantes AS est 
            ON est.num_doc_estudiante = tvexc.est_codigo_unico
        WHERE est.num_doc_estudiante = :estudiante_id
        ORDER BY tvexc.curso
    ");
    $stmt->bindParam(':estudiante_id', $estudiante_id);
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../CSS/estudiante/principal.css">
    <link rel="stylesheet" href="../../CSS/estudiante/excusas.css">
    <title>Inicio</title>
</head>

<body>
    <header>
        <div align="center" id="page_title" class="page_title">
            <meta><strong>SERVICIO DE CONSULTA ACADÉMICA EN LINEA</strong></meta>
        </div>
        <div class="student-container">
            <!-- Encabezado -->
            <div class="header">ESTUDIANTE</div>

            <!-- Contenido principal -->
            <div class="main-content">
                <!-- Logo institucional -->
                <div class="left-logo">
                    <img src="../../Images/escudovertical3.png" alt="Logo Cotecnova" class="logo-img">
                </div>

                <!-- Tabla de datos -->
                <div class="student-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Código Estudiante</th>
                                <th colspan="2">Nombres</th>
                                <th colspan="2">Apellidos</th>
                            </tr>
                            <tr>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                                <td> </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="program-header">PROGRAMA ACADÉMICO</td>
                            </tr>
                            <tr>
                                <td colspan="5">Tecnologia en Gestion de Sistemas de Informacion</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Foto de perfil -->
                <div class="photo-section">
                    <img src="../../Images/avatar.jpg" alt="Foto" class="photo-img">
                </div>
            </div>
        </div>
    </header>

    <div class="main-layout">
        <nav>
            <div class="navbar" id="izquierda">
                <ul>
                    <li>
                        <a href="#" data-seccion="notas">
                            <img src="../../Images/estudiantes/img1.png" alt="Notas">
                            <span>Notas<br>Acuerdos Clases</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="horario">
                            <img src="../../Images/estudiantes/img2.png" alt="Horario">
                            <span>Horario</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="programa">
                            <img src="../../Images/estudiantes/img3.png" alt="Programa">
                            <span>Programa<br>Académico</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="registroExcusas">
                            <img src="../../Images/estudiantes/img9.png" alt="Excusas">
                            <span>Registro de<br>excusas</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="encuestas">
                            <img src="../../Images/estudiantes/img4.png" alt="Encuestas">
                            <span>Encuestas de<br>Apreciación<br>OPIN@</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="tabulados">
                            <img src="../../Images/estudiantes/img5.png" alt="Tabulados">
                            <span>Tabulados</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="recibos">
                            <img src="../../Images/estudiantes/img6.png" alt="Recibos">
                            <span>Recibos de<br>pago</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" data-seccion="sqr">
                            <img src="../../Images/estudiantes/img7.png" alt="SQR">
                            <span>SQR<br>Cotecnova</span>
                        </a>
                    </li>
                </ul>
                <button class="btnDanger">Cerrar Sesión</button>
            </div>
        </nav>

        <article>
            <div id="contenido-dinamico" class="centered-content">
                <!-- El contenido se cargará dinámicamente aquí -->
            </div>
        </article>
    </div>
    <script src="./js/main.js"></script>
    <script>
        const cursosEstudiante = <?= json_encode($cursos, JSON_UNESCAPED_UNICODE); ?>;
    </script>


<!-- Modal de advertencia -->
<div class="modal fade" id="modalAdvertenciaExcusa" tabindex="-1" role="dialog" aria-labelledby="modalAdvertenciaExcusaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    
      <div class="modal-header" style="background-color: #0b4e11; color: white;">
        <h5 class="modal-title" id="modalAdvertenciaExcusaLabel">Advertencia Importante</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
        <p>Declaro que la información suministrada en esta excusa es <strong>veraz</strong> y corresponde a la realidad de los hechos.</p>
        <p>Entiendo que cualquier falsedad o alteración puede tener <strong>consecuencias académicas y disciplinarias</strong> según las políticas institucionales.</p>
        <p>Al continuar, acepto que la institución verifique la autenticidad de esta información.</p>
        <p>Las excusas registradas <strong>NO</strong> pueden ser modificadas, verifique la información antes de dar al botón "Registrar Excusa"</p>
        <p>Si su excusa es por más de un día debe indicar en el campo de fecha el primer día de inasistencia, y en el motivo indicar la cantidad de días por los que se excusa.</p>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar y Continuar</button>
      </div>
      
    </div>
  </div>
</div>



    <!-- Bootstrap JS (con jQuery y Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>