# UML y estructura del sistema

Fecha: 21 de octubre de 2026. Los diagramas describen V2 salvo indicación explícita. Los ejemplos de direcciones pueden cambiar entre redes.

GitHub muestra los bloques Mermaid de clases y secuencia directamente en esta página. Los archivos PlantUML enlazados son fuentes editables: para obtener imágenes se necesita un renderizador compatible; no son imágenes ya exportadas.

## 1. Diagrama de clases — implementación actual

```mermaid
classDiagram
    class Controller
    class RedController {
        +mostrarDashboard(Request solicitud)
        -comprobarIp(string direccionIp) array
    }
    class Request {
        +query(clave, defecto)
    }
    class Validator {
        +make(datos, reglas, mensajes)
    }
    class Process {
        +timeout(segundos)
        +run(argumentos)
    }
    class Model
    class Dispositivo {
        #fillable : array
    }
    Controller <|-- RedController
    Model <|-- Dispositivo
    RedController ..> Request : lee parametros
    RedController ..> Validator : valida rango
    RedController ..> Process : ejecuta ping
```

`Controller`, `Model`, `Request`, `Validator` y `Process` son clases de Laravel. El diagrama representa las dependencias que utiliza el código, no todos los métodos del framework.

No se dibuja una dependencia de `RedController` hacia `Dispositivo`: todavía no existe. Los campos de la tabla se representan en el esquema de datos, porque son atributos persistidos de Eloquent, no propiedades PHP declaradas individualmente.

## 2. Diagrama de secuencia — rango válido

```mermaid
sequenceDiagram
    actor Operador
    participant Navegador
    participant Ruta as GET /
    participant Red as RedController
    participant Validador as Validator
    participant Comando as Process / ping.exe
    participant Vista as dashboard.blade.php
    Operador->>Navegador: Enviar inicio y fin
    Navegador->>Ruta: GET /?inicio=12&fin=12
    Ruta->>Red: mostrarDashboard(solicitud)
    Red->>Validador: Validar enteros, limites y orden
    Validador-->>Red: Datos validos
    Red->>Red: Convertir a enteros y comprobar maximo de seis
    Red->>Red: Generar lista de IPv4
    loop Una vez por cada IP
        Red->>Red: comprobarIp(direccionIp)
        Red->>Comando: ping -n 1 -w 1000 IP, timeout de 5 s
        Comando-->>Red: Salida normal y de errores, o excepcion
        Red->>Red: Convertir CP850 a UTF-8 y clasificar estado
    end
    Red->>Red: Contar resultados por estado
    Red->>Vista: IPs con estado, detalle y contadores
    Vista-->>Navegador: HTML con formulario y casillas
    Navegador-->>Operador: Mostrar resultados
    Operador->>Navegador: Abrir Ver detalle del ping
    Note over Navegador: Se despliega el texto recibido sin enviar otra peticion.
```

La captura de excepciones ocurre en `comprobarIp()`. Para abreviar, el recorrido muestra una respuesta general del proceso; los errores se convierten en un resultado con estado «Error al comprobar».

## 3. Diagrama de secuencia — validación fallida

```mermaid
sequenceDiagram
    actor Operador
    participant Navegador
    participant Red as RedController
    participant Sesion as Sesion Laravel
    participant Vista as dashboard.blade.php
    Operador->>Navegador: Enviar un rango invalido o mayor a seis
    Navegador->>Red: GET con inicio y fin
    Red->>Red: Detectar error de reglas o limite
    Red->>Sesion: Guardar errores y entradas mediante flash
    Red-->>Navegador: Redireccion a /
    Navegador->>Red: GET / sin parametros
    Red->>Red: Utilizar 129 y 134, validar y comprobar
    Red->>Vista: Resultados del rango predeterminado
    Vista->>Sesion: Leer errores y old()
    Vista-->>Navegador: Mensajes, entradas anteriores y resultados predeterminados
    Note over Operador,Vista: Comportamiento actual: puede confundir el rango escrito con el comprobado.
```

## 4. Casos de uso

Fuente UML: [casos-uso.puml](uml/casos-uso.puml).

| Caso | Actor | Estado |
|---|---|---|
| CU-01 Comprobar rango | Operador local | Implementado; incluye validación y ejecución de pings |
| CU-02 Consultar resumen | Operador local | Implementado |
| CU-03 Consultar detalle de ping | Operador local | Implementado |
| CU-04 Registrar dispositivo | Operador local | Futuro; modelo y tabla preparados |
| CU-05 Consultar y comprobar dispositivo guardado | Operador local | Futuro |

El supervisor revisa código y documentación en GitHub. No se representa como un rol con permisos dentro del dashboard porque ese control de usuarios no está implementado.

## 5. Actividad y despliegue

- [Actividad de comprobación](uml/actividad-comprobacion.puml): validación, límite, recorrido, clasificación y respuesta.
- [Despliegue local](uml/despliegue-local.puml): navegador, Laravel, ping de Windows y archivo SQLite. Distingue las consultas ICMP del almacenamiento preparado.

## 6. Estructura de datos — esquema ER, no UML

```mermaid
erDiagram
    DISPOSITIVOS {
        integer id PK
        varchar nombre "hasta 100 caracteres"
        varchar direccion_ip UK "hasta 45 caracteres"
        datetime created_at
        datetime updated_at
    }
```

Se muestra solo la entidad propia del proyecto. Laravel también incluye tablas auxiliares de usuarios, sesiones, caché y trabajos mediante sus migraciones iniciales. Actualmente las comprobaciones no se guardan en `dispositivos` ni en otra tabla propia.

## 7. Diseño futuro, separado de la implementación

El futuro registro necesitará una ruta POST, validación del nombre y de la IP, y una acción que utilice `Dispositivo` para persistir datos. También se necesitará una consulta para el listado y una acción de comprobación individual. No se asignan nombres de métodos como si ya existieran; se decidirán durante la siguiente etapa de implementación.
