# Panel de red

Dashboard local desarrollado en PHP con Laravel para comprobar si las direcciones IPv4 de un rango responden a un ping. Presenta las comprobaciones en una cuadrícula con colores, contadores y el detalle de la salida del comando.

Proyecto de aprendizaje realizado paso a paso, con nombres y comentarios en español. Este repositorio conserva el código y la documentación para revisar su evolución.

## Versiones documentadas

Los nombres V1 y V2 identifican etapas de este proyecto; no son etiquetas de lanzamiento de Git.

| Aspecto | V1: primer avance publicado | V2: avance actual |
|---|---|---|
| Referencia | Commit [`200963e`](https://github.com/TheBlackSoldier1/panel-red/commit/200963e) | Commit que incorpora esta documentación y los cambios actuales |
| Direcciones | Rango fijo `192.168.2.129–134` | Prefijo fijo `192.168.115.` y extremos elegidos desde el formulario |
| Formulario | Visible, pero sus valores no modificaban las comprobaciones | Lee y valida `inicio` y `fin` |
| Límite por petición | Seis IPs por el rango fijo | Máximo explícito de seis IPs |
| Presentación | Cuadrícula, colores y cuatro contadores | Conserva lo anterior y añade salida detallada desplegable |
| Datos inválidos | Sin validación del rango enviado | Mensajes en español y conservación de los valores escritos |
| Texto de Windows | Se utilizaba para clasificar el estado | Se convierte de CP850 a UTF-8 para mostrar las tildes |
| Dispositivos guardados | No disponible | Modelo y migración preparados; registro desde la interfaz pendiente |

El [historial de cambios](CHANGELOG.md) explica los cambios y sus motivos.

## Qué funciona actualmente

- Comprobación real mediante `ping` de Windows, con un paquete y espera de respuesta de 1000 ms.
- Rango de uno a seis equipos, con extremos enteros entre 1 y 254 y final mayor o igual al inicio.
- Estados: **Responde** (verde), **Sin respuesta** (gris) y **Error al comprobar** (amarillo).
- Total comprobado y contadores de cada estado.
- Detalle desplegable de la ejecución ya realizada; abrirlo no ejecuta otro ping.
- Modelo `Dispositivo` y definición de tabla con nombre e IP única.

**Sin respuesta no demuestra que un equipo esté desconectado.** Puede bloquear ICMP o no responder dentro del tiempo configurado. Los estados reflejan la última petición; no se actualizan automáticamente.

Registrar dispositivos desde un formulario, listarlos y comprobar una IP guardada siguen pendientes. La existencia del modelo y la tabla no significa que esas pantallas estén implementadas.

## Tecnologías y requisitos

- Windows: los argumentos de `ping` y la conversión CP850 están adaptados a este entorno.
- PHP compatible con `composer.json` (`^8.3`; entorno probado con PHP 8.5.10).
- Laravel según `composer.lock` (entorno trabajado: 13.36.0), Composer y Git.
- Extensiones PHP requeridas por Composer, más `iconv`, PDO y SQLite para esta configuración.
- Navegador y conectividad con la red que se desea comprobar.

No se requiere un servidor MySQL. El proyecto usa SQLite. El dashboard actual carga `public/css/dashboard.css` directamente y no necesita ejecutar npm para esa pantalla.

## Instalación en Windows con PowerShell

```powershell
git clone https://github.com/TheBlackSoldier1/panel-red.git
cd panel-red
composer install
Copy-Item .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan serve --no-reload
```

Estos pasos son para una copia nueva. En una instalación existente conserva su `.env`, su clave y su base de datos; no repitas el copiado de `.env` ni regeneres la clave como parte de una actualización normal.

La plantilla utiliza `DB_CONNECTION=sqlite`, sesiones y caché en base de datos. `migrate` crea las tablas necesarias. Si existen errores de dependencias, revisa `composer check-platform-reqs`.

Abre [http://127.0.0.1:8000](http://127.0.0.1:8000). Mantén la terminal abierta. Detén el servidor con Ctrl+C.

En el entorno probado, iniciar con `--no-reload` corrigió IPs ausentes y caracteres de control en ciertas líneas de la salida de ping. Es un resultado observado, no una causa raíz demostrada. Con esta opción los cambios en `.env` requieren reiniciar el servidor; PHP, Blade y CSS pueden guardarse y comprobarse recargando el navegador.

## Uso

1. Ejecuta `ipconfig` para identificar la IPv4 y la máscara del adaptador activo. Las direcciones pueden cambiar al conectarte a otra red.
2. El código actual está preparado para una red `/24` con prefijo `192.168.115.`. Si tu red es otra, actualiza `$prefijoRed` en `RedController.php` y los textos de red y etiquetas de `dashboard.blade.php`. No coloques una IP completa en `$prefijoRed`: debe terminar en punto.
3. Escribe el último número de la primera y última dirección a revisar.
4. Pulsa **Comprobar rango** y espera a que terminen todas las comprobaciones secuenciales.
5. Consulta los colores, contadores y **Ver detalle del ping**.

Ejemplo del entorno probado: para comprobar `192.168.115.12`, introduce `12` en ambos campos. Es una dirección de ejemplo, no una garantía sobre la IP actual del lector.

| Inicio → Fin | Comportamiento |
|---|---|
| 12 → 12 | Comprueba una IP: `192.168.115.12` |
| 129 → 134 | Comprueba seis IPs |
| 129 → 135 | Muestra el límite de seis IPs |
| 134 → 129 | Muestra el error de rango invertido |

Al abrir `/` sin parámetros se comprueba el rango predeterminado `129–134`. Al rechazar un formulario se redirige a `/`: se ejecuta ese rango predeterminado mientras los campos conservan el intento inválido. Esa diferencia entre campos y resultados es una limitación pendiente de corregir.

## Organización del código

```text
app/Http/Controllers/RedController.php   Lee, valida y comprueba el rango
app/Models/Dispositivo.php              Modelo preparado para persistencia
routes/web.php                         GET / → mostrarDashboard
resources/views/dashboard.blade.php     Formulario, errores y resultados
public/css/dashboard.css               Cuadrícula, estados y detalle
database/migrations/                    Estructura de la base de datos
docs/                                  Requisitos, UML y verificación
```

`mostrarDashboard()` organiza el flujo. `comprobarIp()` devuelve un arreglo con `estado` y `detalle`. La detección de respuesta se basa en `TTL=`; los contadores trabajan sobre los resultados sin repetir pings.

## Base de datos

La migración `2026_10_21_164216_create_dispositivos_table` define `id`, `nombre` (100 caracteres), `direccion_ip` (45 caracteres, única) y las fechas de creación y modificación. El modelo permite asignación conjunta de `nombre` y `direccion_ip` mediante `$fillable`. Eso no valida el formato de una IP.

La migración fue reportada como `Ran` durante el desarrollo. En otro equipo se aplica con `php artisan migrate`; puede verificarse con `php artisan migrate:status`.

## Documentación para revisión

- [Requisitos y criterios de aceptación](docs/REQUISITOS.md).
- [Diagramas UML y estructura de datos](docs/UML.md).
- [Pruebas, evidencias y limitaciones](docs/VERIFICACION.md).
- [Evolución entre versiones](CHANGELOG.md).

## Respaldo y próximos pasos

Git conserva código, documentación y migraciones. `.env`, dependencias, registros y archivos SQLite locales quedan excluidos; el repositorio no respalda los dispositivos que se guarden posteriormente en una base de datos local.

Próximos pasos: completar registro y listado de dispositivos, validar IPs antes de guardarlas, comprobar dispositivos registrados y corregir la confusión de resultados tras una validación fallida.

Referencia visual: [SubnetScope](https://github.com/marcosblg/subnetscope). Este proyecto usa Laravel y añade comprobaciones ICMP; no reproduce todas las funciones de planificación de SubnetScope.
