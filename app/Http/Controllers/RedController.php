<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Process;

class RedController extends Controller
{
    // Comprueba las direcciones de la lista y muestra sus resultados.
    public function mostrarDashboard()
    {
        // Parte común de las direcciones de nuestra red.
        $prefijoRed = '192.168.2.';

        // Prepara la lista de direcciones que vamos a comprobar.
        $listaIp = [];

        // Genera las direcciones desde 192.168.2.129 hasta 192.168.2.134.
        for ($numeroEquipo = 129; $numeroEquipo <= 134; $numeroEquipo++) {
            $listaIp[] = $prefijoRed . $numeroEquipo;
        }

        // Aquí guardaremos cada IP junto con su estado.
        $direccionesIp = [];

        // Comprueba una dirección por cada vuelta.
        foreach ($listaIp as $direccionIp) {
            $direccionesIp[] = [
                'ip' => $direccionIp,
                'estado' => $this->comprobarIp($direccionIp),
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
    // Comprueba si una dirección IP responde a un ping en Windows.
    private function comprobarIp(string $direccionIp): string
    {
        try {
            // Envía un paquete y espera hasta 1000 milisegundos.
            $resultadoPing = Process::timeout(5)->run([
                'ping',
                '-n',
                '1',
                '-w',
                '1000',
                $direccionIp,
            ]);

            // Si el comando informa un error, lo mostramos por separado.
            if ($resultadoPing->errorOutput() !== '') {
                return 'Error al comprobar';
            }

            // Obtiene el texto que produjo el comando.
            $salidaPing = $resultadoPing->output();

            // En Windows, una respuesta de ping incluye el campo TTL.
            if (stripos($salidaPing, 'TTL=') !== false) {
                return 'Responde';
            }

            return 'Sin respuesta';
        } catch (\Exception $error) {
            // Guarda el problema en el registro de Laravel.
            report($error);

            return 'Error al comprobar';
        }
    }
}
