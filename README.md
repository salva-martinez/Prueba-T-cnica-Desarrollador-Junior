# Gestión de Reservas de Alojamiento

Aplicación web para gestionar reservas de alojamiento. Permite crear reservas, consultar sus detalles, filtrar el listado sin recargar la página y cambiar el estado de una reserva a confirmada o cancelada.

El proyecto está desarrollado con PHP, una API REST, MySQL/MariaDB, Twig y JavaScript. También incluye validaciones en el servidor, mensajes de error y un historial de eventos de las reservas.

## 1. Tecnologías utilizadas

- **PHP:** lógica de negocio, acceso a datos y endpoints de la API.
- **MySQL/MariaDB:** almacenamiento persistente de las reservas.
- **PDO:** conexión y ejecución de consultas SQL mediante parámetros.
- **Twig:** generación de las vistas HTML.
- **JavaScript:** filtrado dinámico, validación en el navegador y actualización de la interfaz.
- **HTML y CSS:** estructura, accesibilidad y diseño responsive.
- **Composer:** gestión de dependencias PHP.
- **Docker y Nginx:** despliegue de la aplicación en el servidor.

## 2. Cómo ejecutar el proyecto en local

### 2.1. Requisitos previos

Para ejecutar el proyecto en local se necesita:

- PHP con las extensiones necesarias para PDO y MySQL.
- Composer.
- MySQL o MariaDB.
- El archivo `reservas.sql`, incluido en el proyecto.

### 2.2. Preparar el proyecto

Situarse en la carpeta raíz del proyecto:

```bash
cd api_reservas
```

Instalar las dependencias de PHP:

```bash
composer install
```

### 2.3. Crear e importar la base de datos

El proyecto incluye el archivo `reservas.sql`, que contiene la estructura de la base de datos y los datos de ejemplo necesarios para probar la aplicación.

Crear la base de datos:

```sql
CREATE DATABASE Reservas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Desde la raíz del proyecto, importar el archivo SQL:

```bash
mysql -u root -p Reservas < reservas.sql
```

El comando solicitará la contraseña del usuario de MySQL.

Si el archivo `reservas.sql` ya contiene instrucciones para crear o seleccionar una base de datos, habrá que comprobarlas para evitar conflictos con la base de datos creada manualmente.

### 2.4. Configurar la conexión a la base de datos

Configurar los parámetros de conexión a MySQL/MariaDB en los archivos de conexión utilizados por el proyecto, como `dbConnetion.php` y `DbAccess.php`.

Hay que comprobar que los siguientes valores coinciden con la instalación local:

- Host de la base de datos.
- Puerto de conexión, normalmente `3306`.
- Nombre de la base de datos.
- Usuario.
- Contraseña.

Las credenciales deben mantenerse fuera del repositorio cuando corresponda y nunca deben publicarse contraseñas o tokens reales.

### 2.5. Configurar la API y el proxy

La aplicación web y la API funcionan mediante dos servidores de desarrollo independientes:

- **Servidor web:** puerto `8008`.
- **Servidor API:** puerto `8009`.

El proxy de la aplicación web debe apuntar a la dirección local de la API:

```text
http://localhost:8009/api
```

También es necesario configurar correctamente el token de autenticación utilizado por la API y por el proxy, de forma que ambos coincidan.

### 2.6. Arrancar la API

Desde la raíz del proyecto, abrir una terminal y ejecutar:

```bash
php -S localhost:8009
```

La API quedará disponible en el puerto `8009`.

### 2.7. Arrancar la aplicación web

Abrir una segunda terminal, desde la misma carpeta raíz, y ejecutar:

```bash
php -S localhost:8008 -t public
```

Acceder a la aplicación desde el navegador:

```text
http://localhost:8008/
```

Los dos servidores deben permanecer en ejecución mientras se utiliza la aplicación.

> Los servidores integrados de PHP están destinados al desarrollo local. Para producción se utiliza la configuración de despliegue correspondiente.

### 2.8. Comprobar el funcionamiento

Una vez iniciados los servidores, comprobar las operaciones principales:

1. Consultar el listado de reservas.
2. Filtrar por nombre del huésped, estado y fechas.
3. Crear una reserva.
4. Comprobar los mensajes de validación al introducir datos incorrectos.
5. Consultar el detalle de una reserva.
6. Confirmar una reserva pendiente.
7. Cancelar una reserva.
8. Verificar que los cambios se guardan correctamente en la base de datos.

## 3. Organización del proyecto

El proyecto separa la interfaz web, la API, los modelos, el acceso a datos y la lógica de validación.

| Directorio o archivo | Responsabilidad |
|---|---|
| `api/` | Endpoints de la API REST. |
| `public/` | Punto de entrada web y recursos públicos. |
| `public/js/` | JavaScript para las interacciones del navegador. |
| `public/css/` | Hojas de estilo. |
| `src/Models/` | Clases que representan las entidades del dominio. |
| `src/Controllers/` | Acceso a datos y operaciones relacionadas con las reservas. |
| `src/Services/` | Validaciones y lógica reutilizable. |
| `src/Views/` | Plantillas Twig para generar el HTML. |
| `vendor/` | Dependencias instaladas mediante Composer. |
| `reservas.sql` | Estructura de la base de datos y datos de ejemplo. |

Esta organización evita concentrar toda la lógica en un único archivo y facilita localizar errores, mantener el código y añadir nuevas funcionalidades.

## 4. Decisiones de diseño

### 4.1. Separación entre la web y la API

La aplicación web y la API tienen responsabilidades diferenciadas.

La web presenta la información, recoge los datos del usuario y gestiona las interacciones. La API recibe las peticiones, valida los datos y ejecuta las operaciones necesarias sobre la base de datos.

Esta separación permite reutilizar la API desde otras interfaces en el futuro, como una aplicación móvil o un panel de administración diferente.

### 4.2. Uso de clases y separación de responsabilidades

La clase `Reserva` representa los datos de una reserva. Las clases DAO centralizan las consultas y las operaciones de persistencia, evitando repetir consultas SQL por distintas partes del proyecto.

Por otro lado, `ReservaValidator` centraliza las reglas de validación del servidor para que puedan reutilizarse en las operaciones que las necesiten.

Esta separación hace que el código sea más fácil de comprender, probar y mantener.

### 4.3. Uso de Twig

Twig permite separar las plantillas HTML de la lógica PHP y reutilizar una plantilla base común.

Esto facilita mantener una estructura visual consistente y modificar la presentación sin tener que cambiar la lógica de negocio.

Además, el escape de salida ayuda a reducir el riesgo de inyección de HTML cuando se muestran datos proporcionados por los usuarios.

### 4.4. Validación en el navegador y en el servidor

La validación en JavaScript permite ofrecer mensajes inmediatos y mejorar la experiencia del usuario.

Sin embargo, no es suficiente por sí sola, ya que una petición puede enviarse directamente a la API sin utilizar el formulario web.

Por este motivo, las validaciones importantes también se realizan en el servidor, que es el responsable de aceptar o rechazar los datos.

### 4.5. Elección de MySQL/MariaDB

Se ha elegido una base de datos relacional porque las reservas contienen información estructurada y necesitan mantener reglas de integridad.

Entre sus ventajas para este proyecto destacan:

- Identificadores únicos para las reservas.
- Tipos de datos adecuados para fechas, estados e importes.
- Restricciones para evitar fechas de salida anteriores o iguales a las de entrada.
- Índices que facilitan las consultas habituales por estado y fecha.
- Soporte para transacciones cuando varias operaciones deban realizarse de forma consistente.

Para el alcance de la aplicación, MySQL/MariaDB ofrece una solución conocida, sencilla de mantener y suficiente para gestionar la información de las reservas.

## 5. Funcionalidades implementadas

La aplicación incluye las siguientes funcionalidades:

- Listado de reservas.
- Filtrado por nombre del huésped, estado y fechas sin recargar toda la página.
- Limpieza de los filtros.
- Creación de nuevas reservas.
- Validación de datos en el navegador y en el servidor.
- Visualización de errores y mensajes de resultado.
- Página de detalle de cada reserva.
- Confirmación de reservas pendientes.
- Cancelación de reservas con confirmación.
- Visualización del historial de eventos de una reserva.
- Comunicación entre la interfaz web y la API mediante un proxy.
- Persistencia de los cambios en la base de datos.

## 6. Qué mejoraría con más tiempo

Aunque la aplicación cubre las operaciones principales, hay varias mejoras que incorporaría en una siguiente versión.

### 6.1. Mejoras en las validaciones y reglas de negocio

- **Impedir importes iguales a cero:** actualmente el importe puede admitir el valor `0`. Añadiría una validación en el servidor para exigir que sea estrictamente mayor que cero y adaptaría también el formulario.
- Revisar que los límites de longitud de los campos coincidan entre formulario, validador y base de datos.
- Comprobar que las fechas sean válidas y que la fecha de salida sea posterior a la de entrada en todos los puntos de entrada.
- Centralizar las reglas que determinan qué cambios de estado están permitidos.
- Valorar la prevención de reservas con fechas solapadas para un mismo alojamiento, si así lo requieren las reglas del negocio.

### 6.2. Edición de reservas

Añadiría una funcionalidad que permitiera editar una reserva existente, actualizando los datos que correspondan, como el huésped, el alojamiento, las fechas, el importe y las notas.

La edición incluiría validaciones en el navegador y en el servidor, así como una actualización persistente en la base de datos.

También definiría qué campos pueden modificarse según el estado de la reserva y registraría las modificaciones relevantes en el historial de eventos.

### 6.3. Seguridad

- Gestionar las credenciales y los tokens mediante variables de entorno.
- Añadir autenticación y autorización si la aplicación va a ser utilizada por distintos usuarios con permisos diferentes.
- Revisar la protección frente a CSRF en las operaciones que utilicen sesiones o cookies.
- Evitar mostrar errores internos y trazas de PHP a los usuarios finales.
- Limitar las peticiones cuando sea necesario.
- Mantener las dependencias actualizadas.
- Revisar las políticas de acceso a la base de datos y utilizar un usuario con permisos mínimos.

### 6.4. Pruebas y mantenimiento

- Incorporar pruebas automatizadas para las validaciones, los endpoints y las operaciones de base de datos.
- Añadir paginación al listado para evitar cargar todas las reservas de una sola vez.
- Mejorar los registros de errores y eventos para facilitar el diagnóstico.
- Documentar los endpoints de la API, sus parámetros, respuestas y códigos HTTP.
- Automatizar las pruebas y comprobaciones de calidad antes de desplegar cambios.

### 6.5. Experiencia de usuario

- Permitir editar reservas desde el listado y desde su página de detalle.
- Mejorar los mensajes de confirmación y los estados de carga.
- Añadir ordenación por columnas y opciones de búsqueda adicionales.
- Mejorar la accesibilidad y comprobar el comportamiento de la interfaz en distintos tamaños de pantalla.
- Incorporar una gestión de usuarios si la aplicación pasa a ser utilizada por varios empleados.

## 7. Escalabilidad: ¿qué cambiaría con 1.000 reservas por hora?

Una carga de 1.000 reservas por hora equivale, de media, a unas 17 reservas por minuto o aproximadamente 0,28 reservas por segundo.

Esta cifra representa la media de creación de reservas, no necesariamente el total de peticiones. Las consultas de listado, filtros, detalles y cambios de estado también generan carga. Además, los picos de tráfico pueden ser muy superiores a la media.

No sustituiría toda la arquitectura de inmediato. Primero mediría el comportamiento real de la aplicación y optimizaría los recursos que fueran necesarios.

### 7.1. Base de datos

- Analizaría las consultas más frecuentes para localizar las que consumen más tiempo.
- Revisaría los índices utilizados para filtrar y ordenar los resultados.
- Evitaría consultas innecesarias y limitaría los resultados mediante paginación.
- Utilizaría transacciones para mantener la consistencia cuando una operación afecte a varias tablas.
- Configuraría copias de seguridad y procedimientos de recuperación.
- Controlaría las conexiones concurrentes y los tiempos de espera.

### 7.2. Servidor web y API

- Utilizaría Nginx o Apache con PHP-FPM en lugar de los servidores integrados de desarrollo de PHP.
- Ajustaría el número de procesos PHP y los recursos disponibles según las mediciones.
- Separaría los servicios y permitiría ejecutar varias instancias de la API si fuera necesario.
- Incorporaría un balanceador de carga si varias instancias atendieran peticiones simultáneamente.

### 7.3. Rendimiento y tareas secundarias

- Añadiría paginación y evitaría devolver campos que no se necesiten en cada petición.
- Incorporaría caché para información de lectura frecuente cuando fuera seguro hacerlo.
- Utilizaría una cola de trabajos para tareas secundarias, como el envío de correos o notificaciones, evitando que retrasen las operaciones principales.
- Añadiría métricas, registros centralizados y alertas para detectar errores, tiempos de respuesta elevados y saturación de recursos.

### 7.4. Integridad y concurrencia

Si varias peticiones intentaran modificar la misma reserva al mismo tiempo, sería necesario garantizar que el estado final fuese coherente.

Para ello utilizaría transacciones, actualizaciones controladas y, cuando fuera necesario, mecanismos de control de concurrencia.

También reforzaría las reglas para evitar estados incompatibles y posibles solapamientos de fechas si un alojamiento no puede tener varias reservas simultáneas.

### 7.5. Estrategia de crecimiento

La estrategia sería crecer de forma gradual:

1. Medir la carga mediante pruebas de rendimiento.
2. Identificar cuellos de botella en PHP, la API y la base de datos.
3. Optimizar las consultas y añadir índices cuando las mediciones lo justifiquen.
4. Ajustar los recursos del servidor y los procesos PHP.
5. Incorporar caché, colas o más instancias únicamente cuando exista una necesidad demostrada.

Para una media de 1.000 reservas por hora, PHP y MySQL/MariaDB bien configurados pueden ser un punto de partida razonable. La capacidad definitiva dependerá del número total de peticiones, los picos de tráfico, la complejidad de las consultas y los recursos disponibles.

## 8. Despliegue

La aplicación está desplegada en un servidor Ubuntu utilizando Docker y Nginx, con HTTPS habilitado.

### URLs de producción

- **Web principal:** https://salvamartinez.es
- **Gestión de reservas:** https://reservas.salvamartinez.es

### Arquitectura del despliegue

La configuración de producción separa los servicios principales:

- **Web:** interfaz de usuario y plantillas Twig.
- **API:** endpoints para consultar, crear y modificar reservas.
- **Base de datos:** almacenamiento persistente de la información.
- **Nginx:** servidor web y proxy inverso para dirigir las peticiones.
- **HTTPS:** comunicación cifrada mediante certificados TLS.

Los servicios se ejecutan mediante Docker y la configuración de producción es independiente de los comandos de desarrollo local.

Las credenciales, los tokens y otros datos sensibles se configuran en el entorno del servidor y no deben publicarse en el repositorio.

Para ejecutar el proyecto en local, consulta el apartado **2. Cómo ejecutar el proyecto en local**.

## 9. Conclusión

El proyecto permite gestionar el ciclo básico de una reserva mediante una interfaz web y una API separada. La organización por responsabilidades facilita su mantenimiento y proporciona una base para incorporar nuevas funcionalidades.

Las siguientes mejoras se centrarían en reforzar las reglas de negocio, añadir edición de reservas, mejorar la seguridad e incorporar pruebas automatizadas. En cuanto a la escalabilidad, la prioridad sería medir y optimizar el rendimiento antes de introducir complejidad adicional en la arquitectura.