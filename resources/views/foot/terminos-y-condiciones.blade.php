@extends('layouts.home')
@section('metas')
    @php $titulo = 'Términos & Condiciones'; @endphp
    <title>{{ $titulo }} - EESPP Pukllasunchis </title>
    <meta name="description"
        content="Términos y condiciones de uso del sitio web de la Escuela de Educación Superior Pedagógica Pukllasunchis.">
    <meta name="keywords" content="{{ $titulo }}, Escuela Pukllasuchis, EESP PUkllasunchis">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/legal.css') }}?v={{ @filemtime(public_path('css/legal.css')) }}">
@endsection
@section('contenido')
    @php
        $secciones = [
            ['id' => 'propiedad-intelectual', 'titulo' => 'Derechos de propiedad intelectual', 'icono' => 'fa-lightbulb'],
            ['id' => 'derechos-autor', 'titulo' => 'Derechos de autor', 'icono' => 'fa-copyright'],
            ['id' => 'signos-distintivos', 'titulo' => 'Signos distintivos y patentes', 'icono' => 'fa-stamp'],
            ['id' => 'usuarios-registrados', 'titulo' => 'Obligaciones de los usuarios registrados', 'icono' => 'fa-user-lock'],
            ['id' => 'usuarios-general', 'titulo' => 'Obligaciones de los usuarios en general', 'icono' => 'fa-users'],
            ['id' => 'modificaciones', 'titulo' => 'Modificaciones a los Términos y Condiciones', 'icono' => 'fa-pen-to-square'],
            ['id' => 'responsabilidad', 'titulo' => 'Delimitación de responsabilidad', 'icono' => 'fa-scale-balanced'],
        ];
    @endphp

    <div class="bradcam_area tramites bradcam_overlay">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="bradcam_text">
                        <h3 class="tituloAnimado">{{ $titulo }}</h3>
                        <p class="slideUp"><a href="{{ route('index') }}">Inicio /</a> {{ $titulo }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="legal-progreso" aria-hidden="true"><span id="legalProgreso"></span></div>

    <section class="legal">
        <div class="container">
            {{-- Introducción --}}
            <header class="legal-intro">
                <div class="legal-intro__icono"><i class="fa-solid fa-file-contract"></i></div>
                <div class="legal-intro__texto">
                    <span class="legal-etiqueta">Condiciones de uso del sitio web</span>
                    <h2>{{ $titulo }}</h2>
                    <p>
                        El acceso a este sitio web y páginas relacionadas implica que aceptas expresamente los términos y
                        condiciones generales descritos a continuación. En caso de no aceptarlos, te solicitamos no acceder ni
                        manipular la información de los servicios ofrecidos por este sitio pues podrías estar haciendo un uso
                        indebido del mismo.
                    </p>
                    <p>
                        Este sitio web está dirigido a nuestros estudiantes, docentes, colaboradores; así como a terceros no
                        vinculados con la Asociación Pukllasunchis.
                    </p>
                    <ul class="legal-meta">
                        <li><i class="fa-regular fa-rectangle-list"></i> {{ count($secciones) }} secciones</li>
                        <li><i class="fa-regular fa-clock"></i> Lectura de 4 min aprox.</li>
                        <li><i class="fa-solid fa-landmark"></i> Asociación Pukllasunchis</li>
                    </ul>
                </div>
                <div class="legal-intro__acciones">
                    <button type="button" class="legal-btn" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </button>
                    <a href="{{ route('politica') }}" class="legal-btn legal-btn--suave">
                        <i class="fa-solid fa-shield-halved"></i> Política de privacidad
                    </a>
                </div>
            </header>

            <div class="row g-4 g-xl-5">
                {{-- Índice --}}
                <aside class="col-lg-4 d-none d-lg-block">
                    <nav class="legal-indice" aria-label="Contenido">
                        <p class="legal-indice__titulo">Contenido</p>
                        <ol>
                            @foreach ($secciones as $i => $s)
                                <li>
                                    <a href="#{{ $s['id'] }}" data-legal-link="{{ $s['id'] }}">
                                        <span class="legal-indice__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        {{ $s['titulo'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>

                {{-- Secciones --}}
                <div class="col-lg-8">
                    @php $n = 0; @endphp

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            Salvo se indique de manera distinta en este sitio web o en sus páginas relacionadas, la Asociación
                            Pukllasunchis es propietaria, o de otra manera titular, de los derechos de propiedad intelectual que se
                            muestran en esta página. Queda prohibido el uso de cualquier derecho de propiedad intelectual sin contar
                            con el consentimiento previo, expreso y por escrito de la Asociación Pukllasunchis.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            Este sitio se encuentra protegido por la normativa vigente sobre derechos de autor. Todos los derechos
                            involucrados, como por ejemplo su contenido y su diseño visual, son de titularidad de la Asociación
                            Pukllasunchis. Por ello, se encuentra estrictamente prohibido su empleo, modificación, reproducción,
                            distribución, transmisión o comercialización de los derechos involucrados sin el permiso previo, expreso
                            y por escrito; salvo los casos de derechos de cita, empleo para finalidades educativas y los demás usos
                            honrados reconocidos.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            A menos que se señale algo distinto en nuestro portal, los signos distintivos y patentes registrados de
                            acuerdo a la legislación nacional y supranacional son propiedad de la Asociación Pukllasunchis. Su uso
                            está prohibido sin el consentimiento previo, expreso y por escrito de la Asociación Pukllasunchis o de
                            su titular.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            Si te encuentras registrado en la EESP Pukllasunchis como alumno, docente, colaborador o cualquier otra
                            manera, ya eres un usuario registrado. El acceso a los servicios a través de tu nombre de usuario y
                            contraseña es de tu plena y exclusiva responsabilidad, por ello debes mantenerlos en estricta
                            confidencialidad.
                        </p>
                        <div class="legal-aviso">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <p>
                                Si presumes que algún tercero estaría utilizando tu nombre de usuario y contraseña,
                                notifica de inmediato esta circunstancia a la Mesa de Ayuda
                                (<a href="mailto:eespp@pukllasunchis.org">eespp@pukllasunchis.org</a>) para prevenir
                                cualquier acceso no autorizado con tu identidad.
                            </p>
                        </div>
                        <p>
                            Toda transacción realizada con tu nombre de usuario y contraseña será vigilada para evitar malos usos
                            de los servicios que se les brinda. No debes usar tu nombre de usuario y contraseña para ningún
                            propósito ilegal o reñido contra la moral o las buenas costumbres.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            El acceso de los servicios web es de carácter público. Como usuario estás obligado a utilizar los
                            servicios y contenidos que te proporciona este sitio web conforme a las leyes de la República del Perú y
                            a los principios de buena fe, y usos generalmente aceptados y a no contravenir con su actuación a través
                            de la web. Mediante el uso de nuestros servicios, a través de este sitio web, declaras bajo juramento
                            que la información que ingresas es verdadera y exacta.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            Nos reservamos el derecho de efectuar en cualquier momento modificaciones o actualizaciones a nuestros
                            términos y condiciones de uso para la atención de novedades legislativas, políticas internas o nuevos
                            requerimientos para la prestación u ofrecimiento de nuestros servicios o productos. Estas modificaciones
                            estarán disponibles al público a través de nuestras páginas de internet.
                        </p>
                    </article>

                    @php $s = $secciones[$n++]; @endphp
                    <article class="legal-seccion" id="{{ $s['id'] }}">
                        <div class="legal-seccion__cabecera">
                            <span class="legal-seccion__num">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3><i class="fa-solid {{ $s['icono'] }}"></i> {{ $s['titulo'] }}</h3>
                        </div>
                        <p>
                            Nuestro compromiso contigo es mantener actualizado este sitio web de manera permanente. Sin embargo,
                            podemos incurrir en imprecisiones u omisiones. En cualquier de estos casos, la Asociación Pukllasunchis
                            no asume responsabilidad por cualquier daño, perjuicio directo, indirecto, incidental, consecuente o
                            especial que se derive del uso de la información contenida en este sitio web o en las páginas web que
                            aquí se refieran. No podemos garantizar un servicio libre e ininterrumpido de este sitio web, pero te
                            manifestamos que es nuestra voluntad realizar nuestros mayores esfuerzos para que sea así.
                        </p>
                    </article>

                    {{-- Contacto --}}
                    <div class="legal-contacto">
                        <div>
                            <h4>¿Tienes dudas sobre estos términos?</h4>
                            <p>Escríbenos y con gusto te orientamos.</p>
                        </div>
                        <div class="legal-contacto__acciones">
                            <a href="mailto:eespp@pukllasunchis.org" class="legal-btn">
                                <i class="fa-regular fa-envelope"></i> Mesa de Ayuda
                            </a>
                            <a href="https://wa.me/51984529158/?text=Buen%20día,%20tengo%20una%20consulta%20sobre%20los%20Términos%20y%20Condiciones."
                                target="_blank" rel="noopener" class="legal-btn legal-btn--suave">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>

                    {{-- <ul class="listasDecimal">
                        <li>Gestión académica.</li>
                        <li>Prestación u ofrecimiento de servicios de la EESP (educativos, culturales, empresariales,
                            deportivos, entre otros) o de terceros con los que hubiéramos suscrito un convenio, en cuyo caso
                            podemos compartir tu información, exclusivamente, para este fin.</li>
                        <li>Invitación a eventos académicos, culturales, deportivos y similares, organizados por la EESP y sus
                            respectivas unidades y centros.</li>
                        <li>Atención de consultas y reclamos.</li>
                        <li>Actualización de tus datos.</li>
                        <li>Cobro de deudas por los servicios educativos prestados.</li>
                        <li>Realización de encuestas.</li>
                        <li>Evaluación de solicitudes de crédito educativo, becas propias o de terceros, y otros beneficios
                            proporcionados por la EESP o por terceros con los que hubiéramos suscrito un convenio, en cuyo caso
                            podemos compartir tu información, exclusivamente, para este fin.</li>
                        <li>Certificación, acreditación y licenciamiento de la EESP frente a entidades nacionales e
                            internacionales.</li>
                        <li>Gestión de oportunidades laborales en la bolsa de trabajo administrada por la EESP, para lo cual
                            podremos brindar tus datos de contacto e información registrada en nuestra Bolsa de Trabajo, a
                            empresas o entidades interesadas en contar con tus servicios profesionales.</li>
                        <li>Atención de servicios de salud y evaluaciones, para lo cual podemos compartir tu información con
                            terceros con los que hubiéramos suscrito un convenio, exclusivamente, para este fin.</li>
                        <li>Obtención de grados académicos, títulos profesionales y su registro ante la SUNEDU.</li>
                        <li>Contacto con la red de egresados y graduados de la EESP Pukllasunchis.</li>
                        <li>Exposiciones y presentaciones de proyectos e investigaciones en ferias y eventos a nivel nacional e
                            internacional. </li>
                        <li>Reporte a instituciones públicas por mandato de la ley.</li>
                        <li>En general, para el cumplimiento de cualquier finalidad conexa con tu relación como estudiante o
                            egresado de la EESP.</li>
                    </ul> --}}
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Respaldo de legal.css para navegadores sin :has(): permite que el índice sea sticky
            if (CSS.supports('overflow-x', 'clip')) {
                document.documentElement.style.overflowX = 'clip';
                document.body.style.overflowX = 'clip';
            }

            // Barra de progreso de lectura
            var barra = document.getElementById('legalProgreso');
            var zona = document.querySelector('.legal');
            function actualizarProgreso() {
                var r = zona.getBoundingClientRect();
                var total = r.height - window.innerHeight;
                var avance = total > 0 ? Math.min(Math.max(-r.top / total, 0), 1) : 1;
                barra.style.width = (avance * 100) + '%';
                barra.parentNode.classList.toggle('is-visible', r.top < 0);
            }
            window.addEventListener('scroll', actualizarProgreso, { passive: true });
            actualizarProgreso();

            // Resalta en el índice la sección que se está leyendo
            if (!('IntersectionObserver' in window)) return;
            var enlaces = document.querySelectorAll('[data-legal-link]');
            var observer = new IntersectionObserver(function(entradas) {
                entradas.forEach(function(e) {
                    if (!e.isIntersecting) return;
                    enlaces.forEach(function(a) {
                        a.classList.toggle('is-activo', a.dataset.legalLink === e.target.id);
                    });
                });
            }, { rootMargin: '-35% 0px -55% 0px' });
            document.querySelectorAll('.legal-seccion').forEach(function(s) { observer.observe(s); });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
@endsection
