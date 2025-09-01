/**
 * ARCHIVO PRINCIPAL DE JAVASCRIPT - MÓDULO DE ESTUDIANTES
 * 
 * Este archivo maneja toda la funcionalidad dinámica del panel de estudiantes:
 * - Carga de contenido dinámico según la sección seleccionada
 * - Gestión del formulario de registro de excusas
 * - Validaciones de fechas y archivos
 * - Comunicación con APIs del servidor
 * - Manejo de cursos y materias del estudiante
 */

// Función principal para cargar contenido dinámico según la sección seleccionada
function cargarContenido(seccion) {
  const contenidoDiv = document.getElementById("contenido-dinamico");

  // Objeto que contiene el HTML de cada sección del sistema
  const contenidos = {
    // Sección de inicio con información general del estudiante
    inicio: `
            <h2>CONSULTAS ESTUDIANTILES</h2>
            <p><strong>En el panel izquierdo de este espacio usted podrá encontrar el acceso a toda la información académica que le corresponde, contenido académico, tabulado de notas, notas y horario del semestre actual.</strong></p>
            <p><strong>A partir del 1 de Junio si usted se encuentra a paz y salvo con las dependencias de Contabilidad, Registro y control, Biblioteca y ha realizado la evaluación docente podrá descargar el comprobante de paz y salvo.</strong></p>
            <p><strong>Nota: Si al momento de ingresar a la institución usted tenía tarjeta de identidad por favor digítela en la casilla que se encuentra en la parte inferior izquierda antes de empezar a consultar.</strong></p>
            <img src="../../Images/estudiantes/img8.png" alt="Imagen pequeña" width="175" height="150">
        `,
    
    // Sección de registro de excusas con formulario completo
    registroExcusas: `
            <h2>REGISTRO DE EXCUSAS</h2>
            <div class="materias-table">
                <h3>Asignaturas Matriculadas</h3>
                <p>*Para habilitar el formulario de registro debe seleccionar almenos una materia</p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cod</th>
                            <th>Asignatura</th>
                            <th>Cr</th>
                            <th>Docente</th>
                            <th>Aula</th>
                            <th>Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cursos-estudiante">
                        <tr><td colspan="6">Cargando cursos...</td></tr>
                    </tbody>

                </table>
            </div>

            <div class="excusa-form" id="excusaForm" style="display: none;">
                <form id="formExcusa">
                    <div>
                    <label><strong>Todos los Campos Son obligatorios *</strong></label>
                    <br></br>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha de la Excusa: *</label>
                        <input type="date" id="fecha" name="fecha" required>
                        <span id="mensajeFecha" style="color: red; font-size: 0.9em;"></span>
                    </div>

                    
                    <div class="form-group">
                        <label for="tipoExcusa">Tipo de Excusa: *</label>
                        <select id="tipoExcusa" name="tipoExcusa" required onchange="mostrarCampoOtro()">
                            <option value="">Seleccione el tipo de excusa</option>
                            <option value="1">Por Salud</option>
                            <option value="2">Laboral</option>
                            <option value="3">Otro</option>
                        </select>
                    </div>

                    <div class="form-group" id="otroTipoContainer" style="display: none;">
                        <label for="otroTipo">Especifique el tipo de excusa: *</label>
                        <input type="text" id="otroTipo" name="otroTipo">
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo de la Excusa: *</label>
                        <textarea id="motivo" name="motivo" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="archivo">Adjuntar Soporte: *</label>
                        <input type="file" id="archivo" name="archivo" accept=".pdf,.zip,.jpeg,.jpg,.png" required>
                        <br>
                        <small>El archivo no puede superar los 10MB. (Solo se aceptan archivos en formato .pdf,.zip,.jpeg,.jpg,.png)</small>

                    </div>
                    
                    <button type="submit" class="btn-submit">Registrar Excusa</button>
                </form>
            </div>
        `,
  };

  // Actualizar el contenido del div principal con la sección seleccionada
  contenidoDiv.innerHTML = contenidos[seccion] || contenidos.inicio;

  // Si se selecciona la sección de registro de excusas, inicializar funcionalidades específicas
  if (seccion === "registroExcusas") {
    cargarCursosEstudiante(); // Cargar la lista de cursos del estudiante

    // Configurar validación de fecha para verificar que no exceda 5 días hábiles
    setTimeout(() => {
      const fechaInput = document.getElementById("fecha");
      const mensajeFecha = document.getElementById("mensajeFecha");

      if (fechaInput) {
        fechaInput.addEventListener("change", function () {
          const fechaSeleccionada = new Date(this.value);
          const hoy = new Date();

          // Normalizar fechas para comparación (sin horas)
          fechaSeleccionada.setHours(0, 0, 0, 0);
          hoy.setHours(0, 0, 0, 0);

          let fechaTemp = new Date(fechaSeleccionada);
          let diasHabiles = 0;

          // Calcular días hábiles entre la fecha seleccionada y hoy
          while (fechaTemp < hoy) {
            const dia = fechaTemp.getDay();
            if (dia !== 0 && dia !== 6) diasHabiles++; // Excluir sábados (6) y domingos (0)
            fechaTemp.setDate(fechaTemp.getDate() + 1);
          }

          // Mostrar advertencia si excede 5 días hábiles
          if (diasHabiles > 5) {
            this.style.border = "2px solid red";
            mensajeFecha.textContent =
              "⚠️ Ha seleccionado una fecha con más de 5 días hábiles de antigüedad. La excusa podría no ser aprobada.";
          } else {
            this.style.border = "";
            mensajeFecha.textContent = "";
          }
        });
      }
    }, 0);

    // Mostrar modal de advertencia sobre el registro de excusas
    $("#modalAdvertenciaExcusa").modal("show");

    // Configurar event listeners para los checkboxes de materias
    document.querySelectorAll('input[name="materia"]').forEach((checkbox) => {
      checkbox.addEventListener("change", function () {
        const excusaForm = document.getElementById("excusaForm");
        // Mostrar/ocultar formulario según si hay materias seleccionadas
        excusaForm.style.display = this.checked ? "block" : "none";
      });
    });

    // Función global para mostrar/ocultar el campo "otro tipo" de excusa
    window.mostrarCampoOtro = function () {
      const tipoExcusa = document.getElementById("tipoExcusa");
      const otroTipoContainer = document.getElementById("otroTipoContainer");
      const otroTipo = document.getElementById("otroTipo");

      if (tipoExcusa.value === "3") { // Si selecciona "Otro"
        otroTipoContainer.style.display = "block";
        otroTipo.required = true;
      } else {
        otroTipoContainer.style.display = "none";
        otroTipo.required = false;
      }
    };

    // Función para limpiar el formulario y resetear el estado
    function limpiarFormulario() {
      // Limpiar todos los campos del formulario
      document.getElementById("formExcusa").reset();

      // Ocultar el formulario de excusas
      document.getElementById("excusaForm").style.display = "none";

      // Deseleccionar todos los checkboxes de materias
      document.querySelectorAll('input[name="materia"]').forEach((checkbox) => {
        checkbox.checked = false;
      });

      // Ocultar el campo "otro tipo" si estaba visible
      document.getElementById("otroTipoContainer").style.display = "none";
    }

    // Event listener para el envío del formulario de excusas
    document
      .getElementById("formExcusa")
      .addEventListener("submit", async function (e) {
        e.preventDefault();

        const boton = this.querySelector('button[type="submit"]');
        const checkboxes = document.querySelectorAll(
          'input[name="materia"]:checked'
        );
        const archivoInput = document.getElementById("archivo");
        const archivo = archivoInput.files[0];
        
        // Validación del tamaño del archivo (máximo 10MB)
        if (archivo.size > 10 * 1024 * 1024) {
          alert("El archivo no puede superar los 10MB.");
          return;
        }

        // Validación del tipo de archivo
        const extension = archivo.name.split(".").pop().toLowerCase();
        if (
          extension !== "pdf" &&
          extension !== "jpg" &&
          extension !== "jpeg" &&
          extension !== "png" &&
          extension !== "zip"
        ) {
          alert(
            "❌ Solo se permiten archivos en formato pdf, zip, jpeg, jpg, png. Por favor seleccione un archivo válido."
          );

          // Limpiar el input de archivo para forzar una nueva selección
          archivoInput.value = "";

          return;
        }

        // Validaciones adicionales
        if (checkboxes.length === 0) {
          alert("Debes seleccionar al menos una materia.");
          return;
        }

        if (!archivo) {
          alert("Debes adjuntar un archivo de soporte.");
          return;
        }

        // Deshabilitar botón y cambiar texto durante el envío
        boton.disabled = true;
        boton.innerText = "Enviando...";

        try {
          // PASO 1: Subir archivo a Dropbox
          const fileData = new FormData();
          fileData.append("file", archivo);

          const uploadResponse = await fetch("../../php/uploadFiles.php", {
            method: "POST",
            body: fileData,
          });

          const uploadResult = await uploadResponse.json();

          if (!uploadResult.success || !uploadResult.url) {
            alert(
              "Error al subir el archivo: " +
                (uploadResult.message || "Sin mensaje.")
            );
            boton.disabled = false;
            boton.innerText = "Registrar Excusa";
            return;
          }

          const enlaceDropbox = uploadResult.url;

          // PASO 2: Recoger datos del formulario
          const fecha = document.getElementById("fecha").value;
          const tipoExcusa = document.getElementById("tipoExcusa").value;
          const otroTipo = document.getElementById("otroTipo").value;
          const motivo = document.getElementById("motivo").value;

          const resultados = [];

          // PASO 3: Enviar una excusa por cada materia seleccionada
          for (const checkbox of checkboxes) {
            const idCurso = checkbox.value;
            const datos = new FormData();

            // Preparar datos para cada excusa
            datos.append("id_curs_asig_es", idCurso);
            datos.append("fecha_falta_excu", fecha);
            datos.append("tipo_excu", tipoExcusa);
            datos.append("otro_tipo_excu", otroTipo);
            datos.append("descripcion_excu", motivo);
            datos.append("soporte_excu", enlaceDropbox);

            // Enviar excusa al servidor
            const response = await fetch(
              "../../php/registrar_excusa_estudiante.php",
              {
                method: "POST",
                body: datos,
              }
            );

            const result = await response.json();
            resultados.push("Excusa registrada correctamente.");
          }

          // Mostrar mensaje de éxito
          if (resultados.length > 0) {
            alert(
              "Excusa/s registrada/s correctamente."
            );
          } else {
            alert("No se recibieron respuestas del servidor.");
          }
          
          // Limpiar formulario después del envío exitoso
          limpiarFormulario();
          
        } catch (error) {
          console.error("Error general:", error);
          alert(
            "Ocurrió un error inesperado. Revisa la consola para más detalles."
          );
        } finally {
          // Restaurar estado del botón
          boton.disabled = false;
          boton.innerText = "Registrar Excusa";
        }
      });
  }

  // Función para cargar y mostrar los cursos del estudiante en la tabla
  function cargarCursosEstudiante() {
    const tablaBody = document.querySelector(".materias-table tbody");
    tablaBody.innerHTML = "";

    // Verificar que existan cursos para mostrar
    if (!Array.isArray(cursosEstudiante) || cursosEstudiante.length === 0) {
      tablaBody.innerHTML = `<tr><td colspan="6">No se encontraron cursos</td></tr>`;
      return;
    }

    // Crear filas de la tabla para cada curso
    cursosEstudiante.forEach((curso) => {
      const fila = document.createElement("tr");
      fila.innerHTML = `
                    <td>${curso.id_curs_asig_es}</td>
                    <td>${curso.curso}</td>
                    <td>${curso.creditos}</td>
                    <td>${curso.docente}</td>
                    <td>${curso.aula}</td>
                    <td><input type="checkbox" name="materia" value="${curso.id_curs_asig_es}"></td>
                `;
      tablaBody.appendChild(fila);
    });

    // Reagregar event listeners a los checkboxes después de crear la tabla
    document.querySelectorAll('input[name="materia"]').forEach((checkbox) => {
      checkbox.addEventListener("change", function () {
        const excusaForm = document.getElementById("excusaForm");
        excusaForm.style.display = this.checked ? "block" : "none";
      });
    });
  }
}

// Event listener principal que se ejecuta cuando el documento está completamente cargado
document.addEventListener("DOMContentLoaded", function () {
  // Cargar contenido inicial (sección de inicio)
  cargarContenido("inicio");

  // Agregar event listeners a todos los enlaces del menú de navegación
  document.querySelectorAll(".navbar a").forEach((enlace) => {
    enlace.addEventListener("click", function (e) {
      e.preventDefault(); // Prevenir navegación tradicional
      const seccion = this.getAttribute("data-seccion");
      if (seccion) {
        cargarContenido(seccion); // Cargar contenido de la sección seleccionada
      }
    });
  });
});
