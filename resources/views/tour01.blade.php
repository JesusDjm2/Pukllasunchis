<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tour 360° de Prueba</title>
    <style>
        html,
        body {
            margin: 0;
            height: 100%;
            overflow: hidden;
        }

        #pano {
            width: 100%;
            height: 100%;
            background: black;
            position: relative;
        }

        .controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: row;
            gap: 15px;
            background: rgba(0, 0, 0, 0.6);
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            flex-wrap: wrap;
            justify-content: center;
        }

        .controls button {
            padding: 12px 18px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.2s;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 180px;
        }

        .controls button:hover {
            background: #830e27;
            color: white;
            transform: scale(1.05);
        }

        .hotspot-video iframe {
            width: 300px;
            height: 180px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
        }

        .hotspot-navegacion button {
            background: rgba(129, 23, 32, 0.7);
            color: white;
            border: none;
            padding: 8px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.4);
            width: 90px;
            text-align: center;
        }

        .cerrar-video {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 680px) {
            .controls {
                gap: 10px;
                padding: 12px 15px;
            }

            .controls button {
                padding: 14px 20px;
                font-size: 16px;
                width: 220px;
            }

            .hotspot-navegacion button {
                padding: 12px 45px;
                font-size: 16px;
                width: auto;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div id="pano"></div>

    <div class="controls">
        <button onclick="cambiarEscena('escena1')">Escena 1</button>
        <button onclick="cambiarEscena('escena2')">Escena 2</button>
    </div>

    <!-- Popup de video -->
    <div id="popupVideo" onclick="cerrarPopup()"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; justify-content:center; align-items:center;">
        <div style="position:relative; max-width:90%; max-height:90%;">
            <button onclick="cerrarPopup()" class="cerrar-video">✕</button>
            <iframe width="800" height="500" src="https://www.youtube.com/embed/M7PWxqWWuyY" frameborder="0"
                allowfullscreen style="border-radius:10px;"></iframe>
        </div>
    </div>

    <script>
        function mostrarPopup() {
            document.getElementById('popupVideo').style.display = 'flex';
        }

        function cerrarPopup() {
            const popup = document.getElementById('popupVideo');
            const iframe = popup.querySelector('iframe');

            iframe.src = "";
            iframe.remove();

            // Recrea el iframe con el mismo video (¡reseteado!)
            const nuevoIframe = document.createElement('iframe');
            nuevoIframe.width = "1000";
            nuevoIframe.height = "500";
            nuevoIframe.src = "https://www.youtube.com/embed/M7PWxqWWuyY";
            nuevoIframe.frameBorder = "0";
            nuevoIframe.allowFullscreen = true;
            nuevoIframe.style.borderRadius = "10px";

            // Reinsertar el nuevo iframe
            popup.querySelector('div').appendChild(nuevoIframe);

            // Oculta el popup
            popup.style.display = 'none';
        }
    </script>

    <script src="{{ asset('tour/marzipano.js') }}"></script>

    <script>
        const viewer = new Marzipano.Viewer(document.getElementById('pano'));
        // Definir escenas
        const escenas = {
            escena1: {
                nombre: "Entrada",
                imagen: "/tour/prueba4.jpg",
                hotspots: [{
                    yaw: 4.4,
                    pitch: 0.0,
                    destino: 'escena2',
                    texto: 'Entrar oficina Radio'
                }]
            },
            escena2: {
                nombre: "Salón",
                imagen: "/tour/prueba5.jpg",
                hotspots: [{
                    yaw: 2.73,
                    pitch: 0.1,
                    destino: 'escena1',
                    texto: 'Ir al patio'
                }]
            },
            escena3: {
                nombre: "Patio",
                imagen: "/tour/prueba3.jpg",
                hotspots: [{
                    yaw: 1.2,
                    pitch: 0.0,
                    destino: 'escena1',
                    texto: 'Volver a entrada'
                }]
            }
        };
        const escenaMap = {};

        for (const key in escenas) {
            const data = escenas[key];

            const source = Marzipano.ImageUrlSource.fromString(data.imagen);
            const geometry = new Marzipano.EquirectGeometry([{
                width: 4000
            }]);
            const limiter = Marzipano.RectilinearView.limit.traditional(1024, 100 * Math.PI / 180);
            const view = new Marzipano.RectilinearView(null, limiter);

            const scene = viewer.createScene({
                source,
                geometry,
                view,
                pinFirstLevel: true
            });

            escenaMap[key] = scene;

            // 👇 AQUI se insertan los hotspots dentro del recorrido 360°
            if (data.hotspots) {
                data.hotspots.forEach(hs => {
                    const el = document.createElement('div');
                    el.classList.add('hotspot-navegacion');
                    el.innerHTML = `<button>${hs.texto}</button>`;
                    el.querySelector('button').onclick = () => cambiarEscena(hs.destino);

                    scene.hotspotContainer().createHotspot(el, {
                        yaw: hs.yaw,
                        pitch: hs.pitch
                    });
                });
            }

            if (key === 'escena2') {
                const btnPopup = document.createElement('div');
                btnPopup.classList.add('hotspot-navegacion');
                btnPopup.innerHTML = `<button>🎬 Ver Video</button>`;
                btnPopup.querySelector('button').onclick = () => mostrarPopup();

                scene.hotspotContainer().createHotspot(btnPopup, {
                    yaw: 4,
                    pitch: 0.0
                });
            }
        }

        // Mostrar primera escena
        escenaMap['escena1'].switchTo();

        // Función para cambiar de escena
        function cambiarEscena(nombre) {
            escenaMap[nombre].switchTo();
            escenaActual = nombre;
        }

        // Función para insertar video como hotspot
        let escenaActual = 'escena1';

        function agregarVideo() {
            const escena = escenaMap[escenaActual];
            const contenedor = document.createElement('div');
            contenedor.classList.add('hotspot-video');
            contenedor.innerHTML = `
      <iframe src="https://www.youtube.com/embed/M7PWxqWWuyY"
        frameborder="0" allowfullscreen></iframe>
    `;

            escena.hotspotContainer().createHotspot(contenedor, {
                yaw: 1.2,
                pitch: 0.1
            });
        }


        if (data.hotspots) {
            data.hotspots.forEach(hs => {
                const el = document.createElement('div');
                el.classList.add('hotspot-navegacion');
                el.innerHTML = `<button>${hs.texto}</button>`;
                el.querySelector('button').onclick = () => cambiarEscena(hs.destino);

                scene.hotspotContainer().createHotspot(el, {
                    yaw: hs.yaw,
                    pitch: hs.pitch
                });
            });
        }
    </script>


    <script>
        // Variables para autogiro
        let autoGiroActivo = false;
        let tiempoInactividad = 1000; // 4 segundos
        let temporizadorInactividad;
        let intervaloAutoGiro;
        let velocidadGiro = 0.001; // Ajusta la velocidad del giro

        // Función para activar el giro automático
        function iniciarAutoGiro() {
            if (autoGiroActivo) return;
            autoGiroActivo = true;

            intervaloAutoGiro = setInterval(() => {
                const escena = escenaMap[escenaActual];
                const view = escena.view();
                const yawActual = view.yaw();
                view.setYaw(yawActual + velocidadGiro);
            }, 30);
        }

        // Función para detener el giro automático
        function detenerAutoGiro() {
            autoGiroActivo = false;
            clearInterval(intervaloAutoGiro);
        }

        // Función para reiniciar el temporizador
        function reiniciarTemporizador() {
            detenerAutoGiro(); // detener si está girando
            clearTimeout(temporizadorInactividad);
            temporizadorInactividad = setTimeout(iniciarAutoGiro, tiempoInactividad);
        }

        // Escuchar eventos de interacción del usuario
        ['mousemove', 'mousedown', 'touchstart', 'keydown'].forEach(evento => {
            document.addEventListener(evento, reiniciarTemporizador);
        });

        // Iniciar el temporizador al cargar
        window.onload = () => {
            reiniciarTemporizador();
        };
    </script>

</body>

</html>
