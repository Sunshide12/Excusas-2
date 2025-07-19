// Función para cargar contenido dinámico
function cargarContenido(seccion) {
    const contenidoDiv = document.getElementById('contenido-dinamico');
    
    // Objeto con el contenido de cada sección
    const contenidos = {
        inicio: `
            <h2>CONSULTAS ESTUDIANTILES</h2>
            <p><strong>En el panel izquierdo de este espacio usted podrá encontrar el acceso a toda la información académica que le corresponde, contenido académico, tabulado de notas, notas y horario del semestre actual.</strong></p>
            <p><strong>A partir del 1 de Junio si usted se encuentra a paz y salvo con las dependencias de Contabilidad, Registro y control, Biblioteca y ha realizado la evaluación docente podrá descargar el comprobante de paz y salvo.</strong></p>
            <p><strong>Nota: Si al momento de ingresar a la institución usted tenía tarjeta de identidad por favor digítela en la casilla que se encuentra en la parte inferior izquierda antes de empezar a consultar.</strong></p>
            <img src="../../Images/estudiantes/img8.png" alt="Imagen pequeña" width="175" height="150">
        `,
        registroExcusas: `
            <h2>REGISTRO DE EXCUSAS</h2>
            <div class="materias-table">
                <h3>Asignaturas Matriculadas</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cod</th>
                            <th>J</th>
                            <th>Asignatura</th>
                            <th>Cr</th>
                            <th>Docente</th>
                            <th>Aula</th>
                            <th>Horario</th>
                            <th>Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>17723</td>
                            <td>D</td>
                            <td>ELECTIVA TECNOLOG. COMPLEMENTARIA</td>
                            <td>3</td>
                            <td>YURI MARCELA LLANO CASTAÑO</td>
                            <td>A14</td>
                            <td>MIERCOLES 08:45 am - 10:15 am</td>
                            <td><input type="checkbox" name="materia" value="17723"></td>
                        </tr>
                        <tr>
                            <td>17521</td>
                            <td>D</td>
                            <td>CIENCIA TECNOLOGIA Y SOCIEDAD</td>
                            <td>1</td>
                            <td>ADEL GUERRERO QUINTERO</td>
                            <td>A16</td>
                            <td>MIERCOLES 10:30 am - 12:00 pm</td>
                            <td><input type="checkbox" name="materia" value="17521"></td>
                        </tr>
                        <tr>
                            <td>17623</td>
                            <td>D</td>
                            <td>ANALISIS DE SISTEMAS DE INFORMACION</td>
                            <td>3</td>
                            <td>EDINSON JAIR MOSQUERA ANGEL</td>
                            <td>LAB C</td>
                            <td>MARTES 10:30 am - 12:00 pm</td>
                            <td><input type="checkbox" name="materia" value="17623"></td>
                        </tr>
                        <tr>
                            <td>17753</td>
                            <td>D</td>
                            <td>PROYECTO INTEGRADOR DE TECNOLOGIA</td>
                            <td>3</td>
                            <td>ARVEY BARAHONA GOMEZ</td>
                            <td>LAB B</td>
                            <td>MARTES 08:45 am - 10:15 am</td>
                            <td><input type="checkbox" name="materia" value="17753"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="excusa-form" id="excusaForm" style="display: none;">
                <form id="formExcusa">
                    <div class="form-group">
                        <label for="fecha">Fecha de la Excusa:</label>
                        <input type="date" id="fecha" name="fecha" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipoExcusa">Tipo de Excusa:</label>
                        <select id="tipoExcusa" name="tipoExcusa" required onchange="mostrarCampoOtro()">
                            <option value="">Seleccione el tipo de excusa</option>
                            <option value="1">Por Salud</option>
                            <option value="2">Laboral</option>
                            <option value="3">Otro</option>
                        </select>
                    </div>

                    <div class="form-group" id="otroTipoContainer" style="display: none;">
                        <label for="otroTipo">Especifique el tipo de excusa:</label>
                        <input type="text" id="otroTipo" name="otroTipo">
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo">Motivo de la Excusa:</label>
                        <textarea id="motivo" name="motivo" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="archivo">Adjuntar Soporte:</label>
                        <input type="file" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.jpg,.png" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">Registrar Excusa</button>
                </form>
            </div>
        `
    };

    // Actualizar el contenido
    contenidoDiv.innerHTML = contenidos[seccion] || contenidos.inicio;

    // Si estamos en la sección de excusas, agregar los event listeners necesarios
    if (seccion === 'registroExcusas') {
        // Event listener para los checkboxes de materias
        document.querySelectorAll('input[name="materia"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const excusaForm = document.getElementById('excusaForm');
                // Mostrar el formulario si al menos un checkbox está seleccionado
                excusaForm.style.display = this.checked ? 'block' : 'none';
            });
        });

        // Función para mostrar/ocultar el campo "otro tipo"
        window.mostrarCampoOtro = function() {
            const tipoExcusa = document.getElementById('tipoExcusa');
            const otroTipoContainer = document.getElementById('otroTipoContainer');
            const otroTipo = document.getElementById('otroTipo');
            
            if (tipoExcusa.value === '3') { // Changed from 'otro' to '3'
                otroTipoContainer.style.display = 'block';
                otroTipo.required = true;
            } else {
                otroTipoContainer.style.display = 'none';
                otroTipo.required = false;
            }
        };

        // Función para limpiar el formulario y deseleccionar checkboxes
        function limpiarFormulario() {
            // Limpiar el formulario
            document.getElementById('formExcusa').reset();
            
            // Ocultar el formulario
            document.getElementById('excusaForm').style.display = 'none';
            
            // Deseleccionar todos los checkboxes
            document.querySelectorAll('input[name="materia"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Ocultar el campo "otro tipo" si estaba visible
            document.getElementById('otroTipoContainer').style.display = 'none';
        }

        // Event listener para el formulario
        document.getElementById('formExcusa').addEventListener('submit', function(e) {
            e.preventDefault();
            // Obtener datos del formulario
            const form = e.target;
            const fecha = form.fecha.value;
            const tipoExcusa = form.tipoExcusa.value;
            const otroTipo = form.otroTipo ? form.otroTipo.value : '';
            const motivo = form.motivo.value;
            const archivo = form.archivo.files[0];
            // Obtener la materia seleccionada (solo una permitida)
            const materiaCheckbox = document.querySelector('input[name="materia"]:checked');
            if (!materiaCheckbox) {
                alert('Seleccione una materia');
                return;
            }
            const id_curs_asig_es = materiaCheckbox.value;

            // Construir FormData
            const formData = new FormData();
            formData.append('id_curs_asig_es', id_curs_asig_es);
            formData.append('fecha_falta_excu', fecha);
            formData.append('tipo_excu', tipoExcusa);
            formData.append('otro_tipo_excu', otroTipo);
            formData.append('descripcion_excu', motivo);
            formData.append('archivo', archivo);

            // Enviar AJAX al backend
            fetch('../../php/registrar_excusa_estudiante.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Excusa registrada correctamente');
                    limpiarFormulario();
                } else {
                    alert('Error: ' + data.mensaje);
                }
            })
            .catch(error => {
                alert('Error al registrar la excusa');
                console.error(error);
            });
        });
    }
}

// Agregar event listeners cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cargar contenido inicial
    cargarContenido('inicio');

    // Agregar event listeners a los enlaces del menú
    document.querySelectorAll('.navbar a').forEach(enlace => {
        enlace.addEventListener('click', function(e) {
            e.preventDefault();
            const seccion = this.getAttribute('data-seccion');
            if (seccion) {
                cargarContenido(seccion);
            }
        });
    });
}); 