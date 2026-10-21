# Requisitos del panel de red

Fecha del corte: 21 de octubre de 2026. Base: commit inicial `200963e` y archivos de trabajo de V2. Documento para seguimiento técnico y revisión del supervisor; no constituye una aprobación formal de aceptación.

## Objetivo y alcance

Comprobar desde una aplicación local si las IPv4 de un rango de la red configurada responden a un ping y presentar el resultado de forma comprensible. En una segunda etapa, conservar un inventario básico de dispositivos y comprobar sus direcciones guardadas.

La aplicación registra observaciones de ICMP; no certifica que una IP esté libre ni que un dispositivo esté desconectado. No asigna direcciones, no modifica el router y no implementa las funciones de VLAN, NAT o planificación de SubnetScope.

Actores: operador local que utiliza el dashboard y supervisor que revisa el repositorio. El supervisor no corresponde a un rol implementado en la aplicación; no hay pantallas propias ni autenticación de usuarios del dashboard.

## Estados de requisitos

- **Implementado:** existe código que realiza la función; la evidencia de prueba se indica por separado.
- **Preparado:** hay estructura parcial, sin un flujo completo de usuario.
- **Pendiente:** no está implementado en la interfaz o lógica actual.

## Requisitos funcionales

| ID | Requisito y criterio de aceptación | Estado | Implementación / prueba |
|---|---|---|---|
| RF-01 | Abrir el dashboard local mediante GET `/` y mostrar formulario y resumen. | Implementado | `routes/web.php`, vista; PV-01 |
| RF-02 | Recibir inicio y fin; sin parámetros utilizar 129 y 134. | Implementado | `Request::query`; PV-02 |
| RF-03 | Aceptar solo enteros entre 1 y 254, ambos presentes, con fin ≥ inicio. Rechazar entradas inválidas con mensajes. | Implementado | `Validator`, Blade; PV-03, PV-04 |
| RF-04 | Rechazar más de seis IPs en una petición, contando ambos extremos. | Implementado | `$fin - $inicio + 1`; PV-05 |
| RF-05 | Generar cada IP del rango válido con el prefijo configurado; inicio = fin produce una sola IP. | Implementado | `for`; PV-02, PV-06 |
| RF-06 | Ejecutar una comprobación por IP: un paquete, 1000 ms de espera y cinco segundos de límite del proceso. | Implementado | `Process::timeout(5)`; PV-06, PV-07 |
| RF-07 | Mostrar «Responde», «Sin respuesta» o «Error al comprobar», acompañados de texto y color. | Implementado, clasificación mejorable | `TTL=`, salida de errores y excepción; PV-06, PV-07, PV-09 |
| RF-08 | Mostrar total y contadores; su suma debe coincidir con el total. | Implementado | Recorrido de resultados; PV-08 |
| RF-09 | Mostrar la salida de la ejecución en un detalle desplegable, sin iniciar otro ping al abrirlo. | Implementado | `details` y `pre`; PV-06 |
| RF-10 | Conservar las entradas tras un error de validación para corregirlas. | Implementado | `withInput()` y `old()`; PV-03 |
| RF-11 | Definir almacenamiento de dispositivos con nombre, IP única, ID y fechas. | Preparado | Migración y modelo; PV-10 |
| RF-12 | Registrar un dispositivo desde un formulario con nombre obligatorio y una IP válida y no duplicada. | Pendiente | Sin controlador/ruta/formulario; PV-11 |
| RF-13 | Listar dispositivos persistidos y comprobar uno mediante una acción de la interfaz. | Pendiente | El controlador actual no consulta `Dispositivo`; PV-12 |

## Requisitos no funcionales

| ID | Requisito / condición | Estado y límite |
|---|---|---|
| RNF-01 | Ejecutarse en el computador del operador, con servidor en loopback. | Se usa `127.0.0.1:8000`; GitHub aloja código, no el dashboard activo. |
| RNF-02 | Mantener nombres y comentarios comprensibles en español. | Aplicado al código propio; Laravel conserva sus convenciones. |
| RNF-03 | Ajustar casillas y detalle a distintos anchos de pantalla. | CSS Grid y ajuste de texto; falta una matriz formal de dispositivos. |
| RNF-04 | Tratar el texto recibido del comando como texto escapado. | `{{ ... }}` en Blade; no se interpreta como HTML. |
| RNF-05 | Acotar el trabajo síncrono por petición. | Máximo seis pings, ejecutados secuencialmente. No existe garantía de tiempo total de carga. |
| RNF-06 | Permitir reconstruir tablas en otro equipo mediante migraciones. | Migración versionada. La base de datos local no se publica. |
| RNF-07 | Conservar historial y documentación revisable en GitHub. | V1 publicada; V2 se respaldará con el commit de este paquete. |
| RNF-08 | Mantener configuración local y dependencias fuera de Git. | `.gitignore` excluye `.env`, SQLite, registros, `vendor` y `node_modules`. |

## Reglas de negocio y restricciones

1. Los extremos se incluyen: 129–134 representa seis direcciones.
2. El prefijo debe terminar en punto y no incluir el último número del equipo.
3. El código actual se adapta manualmente a un prefijo IPv4 `/24`; no calcula redes a partir de máscaras arbitrarias.
4. Inicio y fin validan el último octeto; la configuración del prefijo sigue siendo responsabilidad del desarrollador.
5. Los resultados corresponden a una ejecución puntual. Abrir un desplegable no los actualiza.
6. «Sin respuesta» no equivale a «IP disponible» ni a «equipo desconectado».
7. Una IP guardada en el inventario no se asigna a una interfaz de red.
8. La columna admite 45 caracteres, pero eso no implica que la comprobación IPv6 esté implementada.
9. `$fillable` permite asignación de campos; no sustituye la validación del futuro formulario de dispositivos.

## Limitaciones que afectan la aceptación

- Los errores redirigen a `/`, que ejecuta el rango predeterminado; los campos pueden mostrar el intento inválido y las casillas otro rango.
- Al guardar una futura IP con DHCP, la asociación con el mismo equipo puede cambiar con el tiempo. No hay seguimiento por MAC.
- `ping` y la conversión CP850 son específicos del entorno Windows probado.
- El tratamiento actual detecta `TTL=` y errores de proceso, pero no clasifica todos los mensajes posibles de Windows.
- No existe persistencia de resultados ni historial de disponibilidad.
- Los casos pendientes RF-12/RF-13 no deben presentarse como terminados durante una revisión.
