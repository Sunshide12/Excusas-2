<?php
/**
 * PANEL PRINCIPAL DE ESTUDIANTES
 * 
 * Este archivo es el dashboard principal del estudiante después del login exitoso.
 * Funcionalidades:
 * - Verificación de sesión activa
 * - Carga de cursos matriculados del estudiante
 * - Interfaz de navegación entre diferentes módulos académicos
 * - Sistema de menú lateral con acceso a todas las funcionalidades
 */

// Iniciar sesión para mantener el estado del usuario
session_start();

// Incluir archivo de conexión a la base de datos
require_once __DIR__ . '/../../php/conexion.php';

// Verificar que el estudiante esté autenticado
if (empty($_SESSION['estudiante_id'])) {
    header('Location: index.html'); // Redirigir al login si no hay sesión
    exit;
}

// Obtener ID del estudiante de la sesión
$estudiante_id = $_SESSION['estudiante_id'];
$cursos = [];

try {
    // Consulta SQL para obtener los cursos matriculados del estudiante
    $stmt = $conn->prepare("
        SELECT
            tvexc.id_curs_asig_es,      -- ID único del curso-asignatura-estudiante
            tvexc.curso,                -- Nombre de la asignatura
            tvexc.creditos,             -- Número de créditos de la materia
            CONCAT(                      -- Concatenar nombres completos del docente
                COALESCE(tvexc.profe_nombre, ''), ' ',
                COALESCE(tvexc.profe_snombre, ''), ' ',
                COALESCE(tvexc.profe_apellido, ''), ' ',
                COALESCE(tvexc.profe_sapellido, '')
            ) AS docente,
            tvexc.aula                  -- Aula donde se dicta la clase
        FROM t_v_exc_asig_mat_est AS tvexc  -- Vista que relaciona excusas, asignaturas, matrículas y estudiantes
        INNER JOIN estudiantes AS est        -- Unir con tabla de estudiantes
            ON est.num_doc_estudiante = tvexc.est_codigo_unico
        WHERE est.num_doc_estudiante = :estudiante_id  -- Filtrar por el estudiante logueado
        ORDER BY tvexc.curso              -- Ordenar alfabéticamente por nombre de curso
    ");
    
    // Ejecutar consulta con el ID del estudiante
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
    <!-- Configuración básica del documento HTML -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Importación de Bootstrap CSS para el diseño responsivo -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Favicon y hojas de estilos personalizadas -->
    <link rel="icon" type="image/x-icon" href="/Images/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/Images/favicon.ico">
    <link rel="stylesheet" href="../../CSS/estudiante/principal.css">
    <link rel="stylesheet" href="../../CSS/estudiante/excusas.css">
    
    <title>Inicio</title>
</head>

<body>
    <!-- Encabezado principal de la página -->
    <header>
        <!-- Título principal del sistema -->
        <div align="center" id="page_title" class="page_title">
            <meta><strong>SERVICIO DE CONSULTA ACADÉMICA EN LINEA</strong></meta>
        </div>
        
        <!-- Contenedor principal del estudiante -->
        <div class="student-container">
            <!-- Encabezado identificando al usuario -->
            <div class="header">ESTUDIANTE</div>

            <!-- Contenido principal del encabezado -->
            <div class="main-content">
                <!-- Logo institucional izquierdo -->
                <div class="left-logo">
                    <img src="../../Images/escudovertical3.png" alt="Logo Cotecnova" class="logo-img">
                </div>

                <!-- Tabla de información del estudiante -->
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

                <!-- Sección de foto de perfil del estudiante -->
                <div class="photo-section">
                    <img src="../../Images/avatar.jpg" alt="Foto" class="photo-img">
                </div>
            </div>
        </div>
    </header>

    <!-- Layout principal con navegación y contenido -->
    <div class="main-layout">
        <!-- Barra de navegación lateral izquierda -->
        <nav>
            <div class="navbar" id="izquierda">
                <ul>
                    <!-- Enlace a consulta de notas y acuerdos de clase -->
                    <li>
                        <a href="#" data-seccion="notas">
                            <img src="../../Images/estudiantes/img1.png" alt="Notas">
                            <span>Notas<br>Acuerdos Clases</span>
                        </a>
                    </li>
                    
                    <!-- Enlace al horario de clases -->
                    <li>
                        <a href="#" data-seccion="horario">
                            <img src="../../Images/estudiantes/img2.png" alt="Horario">
                            <span>Horario</span>
                        </a>
                    </li>
                    
                    <!-- Enlace al programa académico -->
                    <li>
                        <a href="#" data-seccion="programa">
                            <img src="../../Images/estudiantes/img3.png" alt="Programa">
                            <span>Programa<br>Académico</span>
                        </a>
                    </li>
                    
                    <!-- Enlace al registro de excusas (funcionalidad principal) -->
                    <li>
                        <a href="#" data-seccion="registroExcusas">
                            <img src="../../Images/estudiantes/img9.png" alt="Excusas">
                            <span>Registro de<br>excusas</span>
                        </a>
                    </li>
                    
                    <!-- Enlace a encuestas de apreciación docente -->
                    <li>
                        <a href="#" data-seccion="encuestas">
                            <img src="../../Images/estudiantes/img4.png" alt="Encuestas">
                            <span>Encuestas de<br>Apreciación<br>OPIN@</span>
                        </a>
                    </li>
                    
                    <!-- Enlace a tabulados de notas -->
                    <li>
                        <a href="#" data-seccion="tabulados">
                            <img src="../../Images/estudiantes/img5.png" alt="Tabulados">
                            <span>Tabulados</span>
                        </a>
                    </li>
                    
                    <!-- Enlace a recibos de pago -->
                    <li>
                        <a href="#" data-seccion="recibos">
                            <img src="../../Images/estudiantes/img6.png" alt="Recibos">
                            <span>Recibos de<br>pago</span>
                        </a>
                    </li>
                    
                    <!-- Enlace al sistema SQR Cotecnova -->
                    <li>
                        <a href="#" data-seccion="sqr">
                            <img src="../../Images/estudiantes/img7.png" alt="SQR">
                            <span>SQR<br>Cotecnova</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Botón para cerrar sesión -->
                <button class="btnDanger">Cerrar Sesión</button>
            </div>
        </nav>

        <!-- Área de contenido principal -->
        <article>
            <!-- Contenedor donde se carga dinámicamente el contenido según la sección seleccionada -->
            <div id="contenido-dinamico" class="centered-content">
                <!-- El contenido se cargará dinámicamente aquí mediante JavaScript -->
            </div>
        </article>
    </div>
    
    <!-- Script principal de funcionalidades -->
    <script src="./js/main.js"></script>
    
    <!-- Script que pasa los cursos del estudiante al JavaScript del frontend -->
    <script>
        // Variable global que contiene los cursos del estudiante para uso en JavaScript
        const cursosEstudiante = <?= json_encode($cursos, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <!-- Modal de advertencia sobre el registro de excusas -->
    <div class="modal fade" id="modalAdvertenciaExcusa" tabindex="-1" role="dialog" aria-labelledby="modalAdvertenciaExcusaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <!-- Encabezado del modal con estilo institucional -->
                <div class="modal-header" style="background-color: #0b4e11; color: white;">
                    <h5 class="modal-title" id="modalAdvertenciaExcusaLabel">Advertencia Importante</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Cuerpo del modal con información importante -->
                <div class="modal-body">
                    <p>Declaro que la información suministrada en esta excusa es <strong>veraz</strong> y corresponde a la realidad de los hechos.</p>
                    <p>Entiendo que cualquier falsedad o alteración puede tener <strong>consecuencias académicas y disciplinarias</strong> según las políticas institucionales.</p>
                    <p>Al continuar, acepto que la institución verifique la autenticidad de esta información.</p>
                    <p>Las excusas registradas <strong>NO</strong> pueden ser modificadas, verifique la información antes de dar al botón "Registrar Excusa"</p>
                    <p>Si su excusa es por más de un día debe indicar en el campo de fecha el primer día de inasistencia, y en el motivo indicar la cantidad de días por los que se excusa.</p>
                </div>

                <!-- Pie del modal con botón de aceptación -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Aceptar y Continuar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Importación de librerías JavaScript de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>