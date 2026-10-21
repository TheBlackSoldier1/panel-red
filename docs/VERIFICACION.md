# Verificación y evidencia de avances

Fecha del registro: 21 de octubre de 2026. Este documento distingue pruebas reportadas durante el desarrollo de comprobaciones que deben repetirse en una revisión. No es una ejecución automática nueva de toda la aplicación.

## Entorno y fuentes

- Windows, PHP 8.5.10, Laravel 13.36.0 y SQLite, según el entorno utilizado.
- Código revisado: controlador, rutas, vista, CSS, modelo y migración.
- Línea base V1: contenido real del commit `200963e`.
- Evidencia manual: resultados de terminal, capturas y confirmaciones del operador en la sesión de desarrollo.
- La dirección de la PC pasó de `192.168.2.161` a `192.168.115.12` al cambiar de red. Comprobar la dirección vigente con `ipconfig` antes de repetir ejemplos.

## Matriz de pruebas

| ID | Procedimiento | Resultado esperado | Evidencia / estado |
|---|---|---|---|
| PV-01 | Iniciar con `php artisan serve --no-reload` y abrir `/`. | Formulario, resumen y casillas. | Observado en capturas. |
| PV-02 | Enviar 129–134. | Seis IPs del prefijo configurado; total = 6. | Reportado durante el desarrollo. |
| PV-03 | Enviar inicio 134 y fin 129. | Mensaje de orden; conservar ambas entradas. | Mensaje observado en captura; conservación implementada y revisada. |
| PV-04 | Enviar valores vacíos, no enteros o fuera de 1–254 mediante URL. | Rechazo en servidor, además de los límites HTML. | Reglas revisadas; repetir cada variante, no se documenta una ejecución individual de todas. |
| PV-05 | Enviar 129–135. | Rechazo por superar seis IPs. | Operador confirmó el conjunto de pruebas del rango; repetir en revisión. |
| PV-06 | Enviar 12–12 en la red `192.168.115.0/24` con la PC en `.12`. | Una casilla verde, un paquete recibido, 0 % de pérdida y salida completa. | Captura final confirma resultado después de `--no-reload`. |
| PV-07 | Consultar una dirección de prueba que no responda. | Estado «Sin respuesta», sin afirmar desconexión. | Capturas mostraron tiempos agotados y cero paquetes recibidos. |
| PV-08 | Comparar suma de estados con total. | Responden + sin respuesta + errores = comprobadas. | Observados 6 = 1 + 5 + 0 y 1 = 1 + 0 + 0. |
| PV-09 | Simular un error de proceso de manera controlada. | Estado «Error al comprobar» y registro de excepción cuando corresponda. | No se considera probado por tener un `catch`; pendiente de prueba específica. |
| PV-10 | Ejecutar `php artisan migrate:status`. | Migración de dispositivos con estado `Ran`. | Salida de terminal compartida por el operador: lote 2, `Ran`. |
| PV-11 | Registrar nombre e IP válida; intentar duplicarla. | Guardado y rechazo del duplicado desde la interfaz. | Pendiente: no existe todavía el flujo de registro. |
| PV-12 | Listar y comprobar un dispositivo guardado. | Recuperar datos de SQLite y mostrar ping individual. | Pendiente: no existe todavía ese flujo. |

## Incidencias y decisiones

### Texto con tildes incorrectas

Síntoma: «Estad�sticas». Se verificó que convertir CP850 a UTF-8 mediante `iconv` mostraba las tildes del ping en el entorno probado. La conversión se hace antes de entregar el detalle a la vista.

### Dirección mal formada

Se escribió una IP completa en `$prefijoRed`; al unirla con el número del formulario apareció `192.168.115.12129`. Se corrigió el prefijo a `192.168.115.`. El registro debe distinguir una dirección mal formada de un equipo que no responde; esa clasificación requiere mejora.

### IP ausente y carácter de control en el detalle

El ping directo desde terminal mostraba la salida completa. En el servidor web faltaba la IP en dos líneas. Tras reiniciar con `php artisan serve --no-reload`, la captura mostró ambas IPs y estadísticas completas. La prueba relaciona la incidencia con el arranque y entorno del servidor; no demuestra su causa raíz ni garantiza el mismo resultado en todos los equipos.

### Resultado después de un rango inválido

La redirección a `/` ejecuta nuevamente los valores predeterminados 129–134. Se muestran errores y entradas anteriores, pero las casillas corresponden al rango predeterminado. Pendiente de corregir para no ejecutar comprobaciones ante un envío inválido y para identificar con claridad el rango de cada resultado.

## Revisión técnica antes de un commit

Desde PowerShell, dentro de `panel-red`:

```powershell
php -l app/Http/Controllers/RedController.php
php -l app/Models/Dispositivo.php
php -l database/migrations/2026_10_21_164216_create_dispositivos_table.php
php artisan route:list
php artisan migrate:status
git diff --check
git status --short
```

Estos son comandos para que el operador los ejecute; listarlos no equivale a afirmar que ya pasaron en el commit documental. `php -l` revisa sintaxis, no demuestra funcionamiento. `git diff --check` busca problemas de espacios, no valida PHP ni UML.

Los tests de ejemplo incluidos por Laravel no cubren de forma específica los requisitos de esta aplicación. Falta una suite de validación, procesos simulados y persistencia de dispositivos; no se declara cobertura completa.

## Revisión del supervisor

1. Leer README y CHANGELOG para comparar V1 con V2.
2. Revisar RF-01 a RF-10 como funciones implementadas y RF-11 a RF-13 como preparación o pendientes.
3. Repetir los casos relevantes en una copia local de la red autorizada.
4. Registrar fecha, entorno, commit completo, resultado y observaciones de la revisión.
5. Verificar que el commit incluya modelo y migración, pero no `.env`, registros ni base SQLite.

| Fecha | Commit completo | Revisor | Casos repetidos | Resultado / observaciones |
|---|---|---|---|---|
| Por completar | Por completar | Por completar | Por completar | Pendiente de revisión formal |
