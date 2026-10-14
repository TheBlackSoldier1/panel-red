<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <title>Panel de red</title>
</head>

<body>
    <h1>Dispositivos de mi red</h1>

    <p>Consulta qué direcciones IP responden en tu red local.</p>
    <main>
        <section>
            <h2>Mi red local</h2>
            <p>Red: 192.168.2.0/24</p>
            <p>Direcciones comprobadas: {{ $totalIp }}</p>
            <p>Responden: {{ $totalResponden }}</p>
            <p>Sin respuesta: {{ $totalSinRespuesta }}</p>
            <p>Errores: {{ $totalErrores }}</p>
            {{-- El formulario queda antes de la cuadrícula. --}}
            {{-- Permite elegir el último número de las direcciones a comprobar. --}}
            <form method="GET" action="{{ url('/') }}">
                <label for="inicio">Desde 192.168.2.</label>
                <input
                    type="number"
                    id="inicio"
                    name="inicio"
                    min="1"
                    max="254"
                    value="129"
                    required>

                <label for="fin">Hasta 192.168.2.</label>
                <input
                    type="number"
                    id="fin"
                    name="fin"
                    min="1"
                    max="254"
                    value="134"
                    required>

                <button type="submit" class="boton-comprobar">
                    Comprobar rango
                </button>
            </form>

            {{-- La cuadrícula contiene solamente las casillas de IPs. --}}
            <div class="cuadricula">
                @foreach ($direccionesIp as $direccionIp)
                <div @class([ 'direccion' , 'responde'=> $direccionIp['estado'] === 'Responde',
                    'sin-respuesta' => $direccionIp['estado'] === 'Sin respuesta',
                    'error' => $direccionIp['estado'] === 'Error al comprobar',
                    ])>
                    <strong>{{ $direccionIp['ip'] }}</strong>
                    <p>{{ $direccionIp['estado'] }}</p>
                </div>
                @endforeach
            </div>
        </section>
    </main>
</body>

</html>