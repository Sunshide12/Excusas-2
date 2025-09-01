<?php
/**
 * PANEL PRINCIPAL DE DOCENTES Y ADMINISTRATIVOS
 * 
 * Este archivo es el dashboard principal para docentes, directores de unidad y otros empleados.
 * Funcionalidades:
 * - Verificación de sesión activa y rol del usuario
 * - Control de acceso basado en roles (RBAC)
 * - Gestión de cursos asignados al docente
 * - Acceso a diferentes módulos según permisos
 * - Interfaz adaptativa según el rol del usuario
 */

// Iniciar sesión para mantener el estado del usuario
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['rol'])) {
  header('Location: index.html'); // Redirigir al login si no hay sesión
  exit;
}

// Obtener rol del usuario para control de acceso
$rol = $_SESSION['rol'];

// Variables de control para mostrar/ocultar elementos según el rol
$mostrarBtnExcusas = ($rol === "Directivo" || $rol === "Director de Unidad");  // Solo directivos pueden registrar excusas
$mostrarTbCursos = ($rol === "Docente" || $rol === "Director de Unidad");      // Solo docentes ven tabla de cursos

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <!-- Configuración básica del documento HTML -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Título de la página -->
  <title>Gestión de Cursos - COTECNOVA</title>
  
  <!-- Hojas de estilos personalizadas -->
  <link rel="stylesheet" href="../../CSS/excusasCotecnova/principal.css" />
  
  <!-- Favicon de la institución -->
  <link rel="icon" type="image/x-icon" href="/Images/favicon.ico">
  <link rel="shortcut icon" type="image/x-icon" href="/Images/favicon.ico">
</head>

<body>
  <!-- Encabezado principal de la página -->
  <header>
    <!-- Logo institucional -->
    <div class="logo">COTECNOVA</div>
    
    <!-- Navegación principal -->
    <nav>
      <a href="#">Inicio</a>
      <a href="#">Gestión de Notas</a>
      <a href="#">Ayuda</a>
    </nav>
  </header>
  
  <!-- Contenido principal del dashboard -->
  <div class="main">
    <!-- Panel de tarjetas de funcionalidades -->
    <div class="cards">
      <!-- Tarjeta de consulta de horario -->
      <div class="card">HORARIO<br /><small>Consulte su horario</small></div>
      
      <!-- Tarjeta de funciones administrativas -->
      <div class="card">
        ADMINISTRATIVO<br /><button>Cambiar Clave</button>
      </div>
      
      <!-- Tarjeta de acceso a asesorías -->
      <div class="card">
        ASESORÍAS<br /><button>Ir a Módulo de Asesorías</button>
      </div>
      
      <!-- Tarjeta de autoevaluación -->
      <div class="card evaluacion">AUTOEVALUACIÓN<br />Realizar</div>
      
      <!-- Tarjeta de reloj y fecha actual -->
      <div class="card clock"><?php echo date('h:i'); ?><br /><?php echo date('D, M-d, Y'); ?></div>
      
      <!-- Tarjeta de registro de excusas (solo para roles autorizados) -->
      <?php if ($mostrarBtnExcusas): ?>
        <div class="cardExcusas">
          Registrar Excusas<br />
          <button onclick="window.location.href='./excusas.php'">Ir a Módulo de Registro de Excusas</button>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sección de gestión de cursos -->
    <h3>Gestión de Cursos</h3>
    
    <!-- Tabla de cursos del docente (solo para roles autorizados) -->
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
          <!-- Curso 1: Programación Orientada a Objetos (Noche) -->
          <tr>
            <td>PROGRAMACION ORIENTADA A OBJETOS (N)</td>
            <td>VIERNES 06:30 pm - 08:00 pm</td>
            <td><span class="aula">LAB C</span></td>
            <td>5</td>
            <td>
              <!-- Menú desplegable de opciones para el curso -->
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
          
          <!-- Curso 2: Programación Orientada a Objetos (Día) -->
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
          
          <!-- Curso 3: Software Gestión Empresarial -->
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
          
          <!-- Curso 4: Ingeniería de Software II -->
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
  
  <!-- Script para manejar la selección de opciones en los cursos -->
  <script>
    // Event listener para todos los menús desplegables de opciones
    document.querySelectorAll(".opcionesPP").forEach((select) => {
      select.addEventListener("change", function() {
        // Si se selecciona "Excusas", redirigir al módulo de gestión de excusas
        if (this.value === "Excusas") {
          window.location.href = "docentes.php";
          this.value = "Opciones"; // Resetear selección
        }
      });
    });
  </script>
</body>

</html>