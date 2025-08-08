<?php
session_start();
if (!isset($_SESSION['rol'])) {
  header('Location: index.html');
  exit;
}

$rol = $_SESSION['rol'];
$mostrarBtnExcusas = ($rol === "Directivo" || $rol === "Director de Unidad");
$mostrarTbCursos = ($rol === "Docente" || $rol === "Director de Unidad");

?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestión de Cursos - COTECNOVA</title>
  <link rel="stylesheet" href="../../CSS/excusasCotecnova/principal.css" />
  <link rel="icon" type="image/x-icon" href="/Images/favicon.ico">
  <link rel="shortcut icon" type="image/x-icon" href="/Images/favicon.ico">
</head>

<body>
  <header>
    <div class="logo">COTECNOVA</div>
    <nav>
      <a href="#">Inicio</a>
      <a href="#">Gestión de Notas</a>
      <a href="#">Ayuda</a>
    </nav>
  </header>
  <div class="main">
    <div class="cards">
      <div class="card">HORARIO<br /><small>Consulte su horario</small></div>
      <div class="card">
        ADMINISTRATIVO<br /><button>Cambiar Clave</button>
      </div>
      <div class="card">
        ASESORÍAS<br /><button>Ir a Módulo de Asesorías</button>
      </div>
      <div class="card evaluacion">AUTOEVALUACIÓN<br />Realizar</div>
      <div class="card clock"><?php echo date('h:i'); ?><br /><?php echo date('D, M-d, Y'); ?></div>
      <?php if ($mostrarBtnExcusas): ?>
        <div class="cardExcusas">
          Registrar Excusas<br /><button onclick="window.location.href='./excusas.php'">Ir a Módulo de Registro de Excusas</button>
        </div>
      <?php endif; ?>
    </div>

    <h3>Gestión de Cursos</h3>
    <?php if ($mostrarTbCursos): ?>
      <table class="cursos-docente">
        <thead>
          <tr>
            <th>Curso</th>
            <th>Horario</th>
            <th>Aula</th>
            <th># Est</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>PROGRAMACION ORIENTADA A OBJETOS (N)</td>
            <td>VIERNES 06:30 pm - 08:00 pm</td>
            <td><span class="aula">LAB C</span></td>
            <td>5</td>
            <td>
              <select class="opcionesPP">
                <option>Opciones</option>
                <option>Listado</option>
                <option>Correos</option>
                <option>Excusas</option>
                <option>Notas</option>
                <option>Ver Acuerdo</option>
                <option>Descargar Acuerdo</option>
                <option>Contenido</option>
                <option>Temas reportados</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>PROGRAMACION ORIENTADA A OBJETOS (D)</td>
            <td>LUNES 10:30 am - 12:00 pm</td>
            <td><span class="aula">LAB C</span></td>
            <td>12</td>
            <td>
              <select class="opcionesPP">
                <option>Opciones</option>
                <option>Listado</option>
                <option>Correos</option>
                <option>Excusas</option>
                <option>Notas</option>
                <option>Ver Acuerdo</option>
                <option>Descargar Acuerdo</option>
                <option>Contenido</option>
                <option>Temas reportados</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>SOFTWARE GESTIÓN EMPRESARIAL (D)</td>
            <td>VIERNES 10:30 am - 12:00 pm</td>
            <td><span class="aula">LAB E</span></td>
            <td>22</td>
            <td>
              <select class="opcionesPP">
                <option>Opciones</option>
                <option>Listado</option>
                <option>Correos</option>
                <option>Excusas</option>
                <option>Notas</option>
                <option>Ver Acuerdo</option>
                <option>Descargar Acuerdo</option>
                <option>Contenido</option>
                <option>Temas reportados</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>INGENIERÍA DE SOFTWARE II (D)</td>
            <td>JUEVES 10:30 am - 12:00 pm</td>
            <td><span class="aula">LAB A</span></td>
            <td>12</td>
            <td>
              <select class="opcionesPP">
                <option>Opciones</option>
                <option>Listado</option>
                <option>Correos</option>
                <option>Excusas</option>
                <option>Notas</option>
                <option>Ver Acuerdo</option>
                <option>Descargar Acuerdo</option>
                <option>Contenido</option>
                <option>Temas reportados</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <script>
    document.querySelectorAll(".opcionesPP").forEach((select) => {
      select.addEventListener("change", function() {
        if (this.value === "Excusas") {
          window.location.href = "docentes.php";
          this.value = "Opciones";
        }
      });
    });
  </script>
</body>

</html>