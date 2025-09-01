# Sistema de Gestión de Excusas - COTECNOVA

## Descripción General

El **Sistema de Gestión de Excusas de COTECNOVA** es un modulo web desarrollada en PHP que permite a estudiantes registrar excusas por inasistencias y a docentes/directores de unidad gestionar y validar estas solicitudes. El sistema implementa un flujo de trabajo completo desde el registro hasta la aprobación/rechazo de excusas.

## Arquitectura del Sistema

### Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework CSS**: Bootstrap 5.3.2
- **Almacenamiento de Archivos**: Dropbox API
- **Envío de Correos**: PHPMailer
- **Autenticación**: Sistema de sesiones PHP

### Estructura de Directorios

```
Excusas-2/
├── CSS/                          # Hojas de estilos
│   ├── estudiante/              # Estilos para módulo de estudiantes
│   └── ExcusasCotecnova/        # Estilos para módulo de docentes
├── Images/                       # Imágenes e iconos del sistema
├── Modules/                      # Módulos principales
│   ├── Estudiantes/             # Módulo de estudiantes
│   └── ExcusasCotecnova/        # Módulo de gestión de excusas
├── php/                         # APIs y lógica de backend
├── PHPMailer/                   # Librería para envío de correos
└── Terceros/                    # Dependencias externas (Dropbox)
```

## Funcionalidades del Sistema

### 1. Módulo de Estudiantes

#### Autenticación
- Login con número de documento y contraseña
- Validación de credenciales contra base de datos
- Creación de sesión segura

#### Panel Principal
- Dashboard con información académica del estudiante
- Menú de navegación lateral con acceso a diferentes módulos:
  - Notas y Acuerdos de Clase
  - Horario
  - Programa Académico
  - **Registro de Excusas** (funcionalidad principal)
  - Encuestas de Apreciación
  - Tabulados
  - Recibos de Pago
  - Sistema SQR

#### Registro de Excusas
- **Selección de Materias**: Lista de cursos matriculados con checkboxes
- **Formulario de Excusa**:
  - Fecha de la falta
  - Tipo de excusa (Salud, Laboral, Otro)
  - Campo adicional para especificar tipo "Otro"
  - Motivo detallado de la excusa
  - Adjuntar archivo de soporte (PDF, ZIP, JPG, PNG)
- **Validaciones**:
  - Máximo 5 días hábiles de antigüedad
  - Tamaño máximo de archivo: 10MB
  - Tipos de archivo permitidos
  - Campos obligatorios
- **Proceso de Envío**:
  1. Subida de archivo a Dropbox
  2. Generación de enlace compartido
  3. Registro en base de datos
  4. Envío de notificación por correo al director de unidad

### 2. Módulo de Docentes y Administrativos

#### Autenticación
- Login con número de documento y contraseña
- Verificación de rol y permisos
- Control de acceso basado en roles (RBAC)

#### Panel Principal
- Dashboard adaptativo según el rol del usuario
- Tarjetas de funcionalidades:
  - Horario
  - Funciones Administrativas
  - Asesorías
  - Autoevaluación
  - Reloj y fecha actual
  - **Registro de Excusas** (solo para roles autorizados)

#### Gestión de Cursos
- Tabla de cursos asignados al docente
- Información detallada: nombre, horario, aula, número de estudiantes
- Menú desplegable de opciones por curso
- Acceso directo al módulo de excusas

#### Gestión de Excusas

##### Para Directivos y Directores de Unidad:
- **Registro de Excusas para Estudiantes**:
  - Ingreso de cédula del estudiante
  - Carga dinámica de cursos del estudiante
  - Formulario completo de excusa
  - Subida de archivos de soporte

##### Para Directores de Unidad:
- **Validación y Aprobación de Excusas**:
  - Lista de excusas pendientes de validación
  - Filtrado por curso
  - Opciones de aprobación/rechazo
  - Campo para justificación
  - Actualización de estado en tiempo real

## Flujo de Trabajo del Sistema

### 1. Registro de Excusa por Estudiante
```
Estudiante → Login → Panel Principal → Registro de Excusas → 
Seleccionar Materias → Llenar Formulario → Subir Archivo → 
Enviar Excusa → Notificación por Correo
```

### 2. Validación por Director de Unidad
```
Director → Login → Panel Principal → Gestión de Excusas → 
Ver Excusas Pendientes → Revisar Soporte → 
Aprobar/Rechazar → Justificar Decisión → 
Actualizar Estado → Notificar Estudiante
```

### 3. Estados de las Excusas
- **Estado 1**: Aprobada
- **Estado 2**: Rechazada  
- **Estado 3**: Pendiente (estado inicial)

## Base de Datos

### Tablas Principales

#### `estudiantes`
- Información personal y académica de los estudiantes
- Credenciales de acceso al sistema

#### `empleados`
- Información de docentes y personal administrativo
- Roles y permisos del sistema

#### `excusas`
- Registro central de todas las excusas
- Relación con estudiantes, cursos y estados

#### `t_v_exc_asig_mat_est`
- Vista que relaciona excusas, asignaturas, matrículas y estudiantes
- Información de cursos y docentes asignados

#### `tiposexcusas`
- Catálogo de tipos de excusa disponibles
- Descripciones y categorías

#### `unidades`
- Unidades académicas de la institución
- Relación con directores de unidad

### Relaciones Clave
- Estudiante → Unidad → Director de Unidad
- Estudiante → Cursos Matriculados → Docentes
- Excusa → Estudiante → Curso → Estado

## APIs del Sistema

### 1. Autenticación
- **`login_estudiante_api.php`**: Login de estudiantes
- **`login_docente_api.php`**: Login de docentes y administrativos

### 2. Gestión de Excusas
- **`registrar_excusa_estudiante.php`**: Registro de excusas por estudiantes
- **`registrar_excusa_docente.php`**: Registro de excusas por docentes
- **`actualizar_estado_excusa.php`**: Aprobación/rechazo de excusas

### 3. Gestión de Archivos
- **`uploadFiles.php`**: Subida de archivos a Dropbox

### 4. Consultas
- **`obtener_cursos_estudiantes.php`**: Cursos de un estudiante específico
- **`obtener_cursos_docentes.php`**: Cursos asignados a un docente

## Seguridad del Sistema

### Autenticación
- Sistema de sesiones PHP
- Verificación de credenciales contra base de datos
- Protección contra acceso no autorizado

### Control de Acceso
- Control de acceso basado en roles (RBAC)
- Verificación de permisos por funcionalidad
- Redirección automática para usuarios no autenticados

### Validación de Datos
- Consultas preparadas para prevenir inyección SQL
- Validación de tipos de archivo
- Límites de tamaño de archivo
- Sanitización de entrada de usuario

### Almacenamiento Seguro
- Archivos almacenados en Dropbox (no en servidor local)
- Enlaces compartidos con acceso controlado
- Credenciales de API almacenadas de forma segura

## Configuración del Sistema

### Requisitos del Servidor
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, PDO_MySQL, cURL
- Memoria mínima: 128MB
- Espacio en disco: 1GB mínimo

### Configuración de Base de Datos
```php
// php/conexion.php
$host = 'localhost';
$dbname = 'v_exc_asig_mat_est';
$username = 'root';
$password = '';
```

### Configuración de Dropbox
- Crear aplicación en Dropbox Developer Console
- Obtener App Key, App Secret y Access Token
- Configurar archivo `Terceros/drp_app_info.json`

### Configuración de Correo
- Configurar credenciales SMTP en PHPMailer
- Servidor: smtp.gmail.com
- Puerto: 587
- Autenticación: TLS

## Instalación y Despliegue

### 1. Preparación del Entorno
```bash
# Clonar repositorio
git clone [URL_DEL_REPOSITORIO]
cd Excusas-2

# Instalar dependencias (si se usa Composer)
composer install
```

### 2. Configuración de Base de Datos
```sql
-- Crear base de datos
CREATE DATABASE v_exc_asig_mat_est;

-- Importar estructura y datos
mysql -u root -p v_exc_asig_mat_est < database_schema.sql
```

### 3. Configuración de Archivos
- Copiar `conexion.php.example` a `conexion.php`
- Configurar credenciales de base de datos
- Configurar credenciales de Dropbox
- Configurar credenciales de correo

### 4. Permisos de Archivos
```bash
# Establecer permisos correctos
chmod 755 -R /path/to/excusas-system
chmod 644 php/*.php
```

### 5. Configuración del Servidor Web
- Configurar virtual host para el proyecto
- Habilitar mod_rewrite si se usan URLs amigables
- Configurar límites de subida de archivos en PHP

## Mantenimiento y Operación

### Logs del Sistema
- Errores de PHP en `error_log`
- Errores de PHPMailer en logs del sistema
- Errores de base de datos en logs de MySQL

### Respaldos
- Respaldar base de datos regularmente
- Respaldar archivos de configuración
- Respaldar archivos subidos a Dropbox

### Monitoreo
- Verificar funcionamiento de APIs
- Monitorear uso de almacenamiento en Dropbox
- Revisar logs de errores regularmente

### Actualizaciones
- Mantener PHP y MySQL actualizados
- Actualizar dependencias de Composer
- Revisar actualizaciones de seguridad

## Características Técnicas

### Rendimiento
- Consultas SQL optimizadas con índices apropiados
- Paginación de resultados para listas grandes
- Caché de consultas frecuentes
- Compresión de archivos CSS y JavaScript

### Escalabilidad
- Arquitectura modular para fácil expansión
- Separación clara de responsabilidades
- APIs RESTful para integración futura
- Base de datos normalizada

### Compatibilidad
- Navegadores modernos (Chrome, Firefox, Safari, Edge)
- Diseño responsivo para dispositivos móviles
- Soporte para diferentes resoluciones de pantalla
- Compatibilidad con estándares web

## Casos de Uso

### Estudiante
1. **Acceso al Sistema**: Login con credenciales institucionales
2. **Consulta Académica**: Acceso a notas, horarios y programas
3. **Registro de Excusa**: Proceso completo de solicitud de excusa
4. **Seguimiento**: Consulta del estado de excusas enviadas

### Docente
1. **Gestión de Cursos**: Visualización de cursos asignados
2. **Acceso a Información**: Listados de estudiantes y notas
3. **Registro de Excusas**: Crear excusas para estudiantes cuando sea necesario

### Director de Unidad
1. **Validación de Excusas**: Revisar y aprobar/rechazar solicitudes
2. **Gestión Académica**: Supervisar excusas de la unidad
3. **Reportes**: Generar informes de excusas por curso

### Administrador del Sistema
1. **Gestión de Usuarios**: Crear y administrar cuentas
2. **Configuración**: Ajustar parámetros del sistema
3. **Mantenimiento**: Respaldo y actualización del sistema

## Solución de Problemas

### Problemas Comunes

#### Error de Conexión a Base de Datos
- Verificar credenciales en `conexion.php`
- Confirmar que MySQL esté ejecutándose
- Verificar permisos de usuario de base de datos

#### Error en Subida de Archivos
- Verificar configuración de Dropbox
- Confirmar límites de tamaño en PHP
- Revisar permisos de escritura

#### Error en Envío de Correos
- Verificar credenciales SMTP
- Confirmar configuración de PHPMailer
- Revisar logs de error del servidor

#### Problemas de Sesión
- Verificar configuración de cookies
- Confirmar que las sesiones estén habilitadas
- Revisar configuración del servidor web

### Debugging
- Habilitar `display_errors` en desarrollo
- Revisar logs de error del servidor
- Usar herramientas de desarrollo del navegador
- Verificar consola de JavaScript

## Roadmap y Mejoras Futuras

### Corto Plazo
- Implementar notificaciones push
- Mejorar interfaz móvil
- Agregar validaciones adicionales
- Implementar sistema de búsqueda

### Mediano Plazo
- API REST completa
- Sistema de reportes avanzados
- Integración con sistemas externos
- Aplicación móvil nativa

### Largo Plazo
- Inteligencia artificial para validación
- Sistema de workflow avanzado
- Análisis predictivo de excusas
- Integración con sistemas de gestión académica

## Contribución y Desarrollo

### Estándares de Código
- PSR-12 para PHP
- ESLint para JavaScript
- Comentarios detallados en español
- Documentación inline

### Proceso de Desarrollo
1. Crear rama para nueva funcionalidad
2. Implementar cambios con tests
3. Crear pull request
4. Revisión de código
5. Merge a rama principal

### Testing
- Tests unitarios para funciones PHP
- Tests de integración para APIs
- Tests de interfaz para funcionalidades críticas
- Validación de formularios

## Licencia y Derechos

Este sistema fue desarrollado para la **Corporación de Estudios Tecnológicos del Norte del Valle (COTECNOVA)**. Todos los derechos reservados.

## Contacto y Soporte

Para soporte técnico o consultas sobre el sistema:
- **Desarrollador**: Equipo de Desarrollo COTECNOVA
- **Email**: [email@cotecnova.edu.co]
- **Documentación**: [URL_DOCUMENTACION]

---

**Versión del Sistema**: 2.0  
**Última Actualización**: Diciembre 2024  
**Estado**: En Producción
