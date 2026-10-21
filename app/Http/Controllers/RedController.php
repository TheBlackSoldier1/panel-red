<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RedController extends Controller
{
    // Comprueba las direcciones de la lista y muestra sus resultados.
    public function mostrarDashboard(Request $solicitud)
    {
        // Lee los campos enviados; usa estos valores si no se recibieron.
        $inicio = $solicitud->query('inicio', 129);
        $fin = $solicitud->query('fin', 134);
        // Revisa los valores recibidos antes de utilizarlos.
        $validacion = Validator::make(
            [
                'inicio' => $inicio,
                'fin' => $fin,
            ],
            [
                'inicio' => ['bail', 'required', 'integer', 'between:1,254'],
                'fin' => ['bail', 'required', 'integer', 'between:1,254', 'gte:inicio'],
            ],
            [
                'required' => 'Debes completar el campo :attribute.',
                'integer' => 'El campo :attribute debe ser un número entero.',
                'between' => 'El campo :attribute debe estar entre 1 y 254.',
                'gte' => 'El final debe ser mayor o igual al inicio.',
            ]
        );
        // Si los datos no son válidos, vuelve al dashboard con los errores.
        if ($validacion->fails()) {
            return redirect('/')
                ->withErrors($validacion)
                ->withInput();
        }
        // Obtiene los datos que pasaron la validación.
        $datosValidados = $validacion->validated();

        // Convierte los valores recibidos en números enteros.
        $inicio = (int) $datosValidados['inicio'];
        $fin = (int) $datosValidados['fin'];

        // Cuenta las direcciones incluyendo ambos extremos.
        $cantidadIp = $fin - $inicio + 1;
        $maximoIp = 6;

        // Evita comprobar demasiadas direcciones en una sola petición.
        if ($cantidadIp > $maximoIp) {
            return redirect('/')
                ->withErrors([
                    'rango' => 'Comprueba como máximo 6 direcciones por vez.',
                ])
                ->withInput();
        }
        // Parte común de las direcciones de nuestra red.
        $prefijoRed = '192.168.115.';

        // Prepara la lista de direcciones que vamos a comprobar.
        $listaIp = [];

        // Genera las direcciones del rango elegido y validado.
        for ($numeroEquipo = $inicio; $numeroEquipo <= $fin; $numeroEquipo++) {
            $listaIp[] = $prefijoRed . $numeroEquipo;
        }

        // Aquí guardaremos cada IP junto con su estado.
        $direccionesIp = [];

        // Ejecuta una comprobación por IP y guarda ambos resultados.
        foreach ($listaIp as $direccionIp) {
            $resultadoComprobacion = $this->comprobarIp($direccionIp);

            $direccionesIp[] = [
                'ip' => $direccionIp,
                'estado' => $resultadoComprobacion['estado'],
                'detalle' => $resultadoComprobacion['detalle'],
            ];
        }
        // Cuenta los resultados obtenidos.
        $totalIp = count($direccionesIp);
        // Comienza el contador en cero.
        $totalResponden = 0;
        $totalSinRespuesta = 0;
        $totalErrores = 0;

        // Cuenta los resultados sin ejecutar nuevos pings.
        foreach ($direccionesIp as $resultadoIp) {
            if ($resultadoIp['estado'] === 'Responde') {
                $totalResponden++;
            } elseif ($resultadoIp['estado'] === 'Sin respuesta') {
                $totalSinRespuesta++;
            } else {
                $totalErrores++;
            }
        }
        return view('dashboard', [
            'direccionesIp' => $direccionesIp,
            'totalIp' => $totalIp,
            'totalResponden' => $totalResponden,
            'totalSinRespuesta' => $totalSinRespuesta,
            'totalErrores' => $totalErrores,
        ]);
    }
    // Devuelve el estado y el detalle de la comprobación.
    private function comprobarIp(string $direccionIp): array
    {
        try {
            // Ejecuta un ping con un paquete.
            $resultadoPing = Process::timeout(5)->run([
                'ping',
                '-n',
                '1',
                '-w',
                '1000',
                $direccionIp,
            ]);

            // Convierte la salida de Windows al formato que utiliza nuestra página.
            $salidaPing = iconv('CP850', 'UTF-8', $resultadoPing->output());
            $salidaError = iconv('CP850', 'UTF-8', $resultadoPing->errorOutput());

            if ($salidaError !== '') {
                return [
                    'estado' => 'Error al comprobar',
                    'detalle' => $salidaError,
                ];
            }

            // Parte de "Sin respuesta" y cambia si encuentra una respuesta.
            $estadoConexion = 'Sin respuesta';

            if (stripos($salidaPing, 'TTL=') !== false) {
                $estadoConexion = 'Responde';
            }

            return [
                'estado' => $estadoConexion,
                'detalle' => $salidaPing,
            ];
        } catch (\Exception $error) {
            report($error);

            return [
                'estado' => 'Error al comprobar',
                'detalle' => 'No se pudo completar la comprobación.',
            ];
        }
    }
}
