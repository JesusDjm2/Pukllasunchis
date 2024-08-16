<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo de Pandero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h1>Pandero 2024 Pukllasunchis</h1>
                @php
                    $persona1 = 'David';
                    $persona2 = 'Raquel';
                    $persona3 = 'Sharon';
                    $persona4 = 'Javier';
                    $persona5 = 'Maritza';
                    $persona6 = 'Carlos';
                    $persona7 = 'Esposa de Carlos';
                    $persona8 = 'Hij@ 1 de Carlos';
                    $persona9 = 'Hij@ 2 de Carlos';
                    $persona10 = 'Persona 1';
                    $persona11 = 'Persona 2';
                    $persona12 = 'Persona 3';

                    $personas = [$persona1, $persona2, $persona3, $persona4, $persona5, $persona6, $persona7, $persona8, $persona9, $persona10, $persona11, $persona12];
                    shuffle($personas);
                @endphp

                <div class="row align-items-center justify-content-center">
                    <div class="col-lg-4">
                        <ul style="list-style: none" id="lista">
                            @php
                                $contador = 1;
                                foreach ($personas as $persona) {
                                    echo "<li>$contador: $persona</li>";
                                    $contador++;
                                }
                            @endphp
                        </ul>
                    </div>
                </div>

                <ul style="list-style: none; font-size: 20px" class="bg-secondary text-white" id="ganadores">
                </ul>
                <h2 id="tercerGanador" class="zoom-infinite"></h2>
                <h3 id="result" class="mt-3" style="background: rgb(87, 86, 86);padding:0.3em 0.5em;color:#fff">
                </h3>
                <button onclick="realizarSorteo()" class="btn btn-primary">Realizar Sorteo</button>
                <button onclick="nuevoSorteo()" class="btn btn-info">Nuevo Sorteo</button>
            </div>
        </div>
    </div>
    <style>
        .spinner {
            border: 8px solid rgba(0, 0, 0, 0.1);
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes zoomInfinite {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .zoom-infinite {
            animation: zoomInfinite 2s infinite;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }

        #lista-container {
            overflow: hidden;
            height: 400px;
        }

        #lista {
            list-style: none;
            margin: 0;
            padding: 0;
            transition: transform 1s ease-in-out;
        }

        #lista li {
            margin: 0;
            padding: 3px;
            border-bottom: 1px solid #ccc;
        }

        #lista.animating {
            transform: translateY(-100%);
        }
    </style>
    <script>
        var personasOriginales = @json($personas);
        var ganadores = [];

        function realizarSorteo() {
            if (personasOriginales.length > 0) {
                var ganador = personasOriginales.shift();
                document.getElementById("result").innerHTML = "<p>Realizando sorteo...</p>";

                setTimeout(function() {
                    ganadores.push(ganador);

                    document.getElementById("result").innerHTML = "<p>Ganador del sorteo es: " + ganador +
                        "!</p>";

                    actualizarListas();
                }, 3000);
            } else {
                document.getElementById("result").innerHTML = "<p>No quedan personas para sortear.</p>";
            }
        }

        function nuevoSorteo() {
            personasOriginales = @json($personas);
            ganadores = [];
            document.getElementById("result").innerHTML = "";
            actualizarListas();
        }

        function actualizarListas() {
            document.getElementById("lista").innerHTML = "";
            document.getElementById("ganadores").innerHTML = "";

            personasOriginales.forEach(function(persona, index) {
                var contador = index + 1;
                document.getElementById("lista").innerHTML += "<li>" + contador + ": " + persona + "</li>";
            });

            ganadores.forEach(function(ganador, index) {
                var contador = index + 1;

                if (index < 2) {
                    // Mostrar los primeros dos ganadores en el contenedor "ganadores"
                    document.getElementById("ganadores").innerHTML += "<li>Ganador " + contador + ": " + ganador +
                        "</li>";
                } else if (index === 2) {
                    // Mostrar el tercer ganador en el contenedor "tercerGanador"
                    document.getElementById("tercerGanador").innerHTML = "Se lleva el pandero: " + ganador + "!!!"
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>
