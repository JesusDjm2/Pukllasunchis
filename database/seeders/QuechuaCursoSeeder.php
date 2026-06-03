<?php

namespace Database\Seeders;

use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CeUnidad;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Database\Seeder;

class QuechuaCursoSeeder extends Seeder
{
    private const PLAYLIST = 'PLBwAfIqaoPZDbShd4mJdixVghgcPuk412';

    public function run(): void
    {
        if (CursoEspecial::where('nombre', 'like', '%Quechua%')->exists()) {
            $this->command->info('El curso de Quechua ya existe. Seeder omitido.');
            return;
        }

        $curso = CursoEspecial::create([
            'nombre'      => 'Idioma Quechua — Qhichwa Simi',
            'descripcion' => 'Aprende el Quechua Cusco-Collao (Qusqu-Qullaw Simi) de manera progresiva y asincrónica. Desde los saludos básicos hasta conversaciones cotidianas, con videos auténticos, lectura comentada y ejercicios interactivos.',
            'activo'      => true,
            'orden'       => 1,
        ]);

        $this->basicoNivel($curso);
        $this->intermedioNivel($curso);
        $this->avanzadoNivel($curso);

        $this->command->info('✅ Curso de Quechua creado con éxito.');
    }

    // ══════════════════════════════════════════════════════════════
    //  NIVEL 1 — BÁSICO
    // ══════════════════════════════════════════════════════════════
    private function basicoNivel(CursoEspecial $curso): void
    {
        $nivel = CeNivel::create([
            'curso_especial_id' => $curso->id,
            'nombre' => 'Nivel Básico — Ñawpaq Yachay',
            'orden'  => 1,
        ]);

        $this->unidad1_Saludos($nivel);
        $this->unidad2_Numeros($nivel);
        $this->unidad3_Familia($nivel);
        $this->unidad4_Tiempo($nivel);
        $this->unidad5_Alimentos($nivel);
    }

    // ── Unidad 1: Saludos ────────────────────────────────────────
    private function unidad1_Saludos(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 1: Napayukuy — Saludos y Presentaciones',
            'descripcion' => 'Aprende a saludar, presentarte y despedirte en Quechua Cusco-Collao.',
            'orden'       => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Introducción al Quechua Cusco-Collao',
            'tipo'            => 'video',
            'archivo_url'     => $this->yt('4ive9yAJwBE'),
            'contenido_texto' => "📌 ANTES DE VER EL VIDEO:\n\nEl Quechua Cusco-Collao (también llamado Qusqu-Qullaw o Quechua Sureño) es la variante más hablada del Perú y la que mayor presencia tiene en la región Cusco, Puno y Apurímac.\n\nEsta serie de videos sigue la metodología comunicativa: primero escuchas, luego repites, luego produces.",
            'duracion_min'    => 20,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Saludos esenciales — Napayukuykunan',
            'tipo'            => 'texto',
            'contenido_texto' => "# Saludos en Quechua Cusco-Collao\n\n## Saludos por hora del día\n\n| Quechua | Español |\n|---|---|\n| Sumaq pacha | Buen tiempo / Buenos días (general) |\n| Sumaq punchaw | Buenos días |\n| Sumaq chisi | Buenas tardes |\n| Sumaq tuta | Buenas noches |\n\n## Preguntar cómo estás\n\n| Quechua | Español |\n|---|---|\n| Imaynallanmi kachkanki? | ¿Cómo estás? (informal) |\n| Allillanchu? | ¿Estás bien? |\n| Imaynallan kashanki? | ¿Cómo te va? |\n\n## Responder\n\n| Quechua | Español |\n|---|---|\n| Allinllanam kachkani | Estoy bien |\n| Walillanmi | Muy bien |\n| Pisitam | Regular / Más o menos |\n| Mana allinchu | No estoy bien |\n\n## Despedidas\n\n| Quechua | Español |\n|---|---|\n| Tupananchiskama | Hasta que nos volvamos a ver |\n| Pakarinkamaqa | Hasta mañana |\n| Ratukama | Hasta luego (en un rato) |\n| Rimaykullayki | Chau / Un saludo (al despedirse) |\n\n---\n\n## 🔊 Pronunciación clave\n- **q** = sonido gutural profundo (como el árabe, desde la garganta)\n- **ch'** = ch con explosión de aire (ch aspirada)\n- **k'** = k con explosión de aire\n- **ll** = pronunciación andina (como \"ly\" suave o \"ll\" española clásica)",
            'duracion_min'    => 10,
            'orden'           => 2,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 3: Presentarse — Sutiyta Willakuy',
            'tipo'            => 'texto',
            'contenido_texto' => "# Cómo presentarte en Quechua\n\n## Decir tu nombre\n\n| Quechua | Español |\n|---|---|\n| Sutiymi ... | Mi nombre es... |\n| Sutiy ... | Mi nombre es... (también) |\n| Imatam sutiyki? | ¿Cómo te llamas? |\n| Imastatam sutiykichik? | ¿Cómo se llaman ustedes? |\n\n## De dónde eres\n\n| Quechua | Español |\n|---|---|\n| Maymantam kanki? | ¿De dónde eres? |\n| Qusqumantam kani | Soy de Cusco |\n| Punumantam kani | Soy de Puno |\n| Limamantam kani | Soy de Lima |\n\n## Tu edad\n\n| Quechua | Español |\n|---|---|\n| Hayk'ataql watayuqmi kanki? | ¿Cuántos años tienes? |\n| Iskay chunka watayuqmi kani | Tengo 20 años |\n| Kimsa chunka watayuqmi kani | Tengo 30 años |\n\n## Diálogo modelo\n\n```\nA: Allillanchu?\nB: Allinllanam, anitam. Qantri?\nA: Walillanmi. Sutiymi Carlos. Imatam sutiyki?\nB: Sutiymi María. Maymantam kanki?\nA: Limamantam kani. Qantri?\nB: Noqaqa Qusqumantam kani.\nA: Aha! Tupananchiskama.\nB: Tupananchiskama.\n```\n\n**Traducción:**\nA: ¿Estás bien?\nB: Estoy bien, gracias. ¿Y tú?\nA: Muy bien. Me llamo Carlos. ¿Cómo te llamas?\nB: Me llamo María. ¿De dónde eres?\nA: Soy de Lima. ¿Y tú?\nB: Yo soy de Cusco.\nA: ¡Ah! Hasta la vista.\nB: Hasta la vista.",
            'duracion_min'    => 12,
            'orden'           => 3,
        ]);

        // Ejercicios unidad 1
        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "Buenos días" en Quechua Cusco-Collao?',
            'opciones'          => ['Sumaq tuta', 'Sumaq punchaw', 'Tupananchiskama', 'Rimaykullayki'],
            'respuesta_correcta'=> 'Sumaq punchaw',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa "Tupananchiskama"?',
            'opciones'          => ['Buenos días', 'Muchas gracias', 'Hasta que nos volvamos a ver', '¿Cómo estás?'],
            'respuesta_correcta'=> 'Hasta que nos volvamos a ver',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => 'Si alguien te pregunta "Imatam sutiyki?", ¿qué te está preguntando?',
            'opciones'          => ['¿De dónde eres?', '¿Cuántos años tienes?', '¿Cómo te llamas?', '¿Estás bien?'],
            'respuesta_correcta'=> '¿Cómo te llamas?',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'Completa: Para decir "Estoy bien" en quechua se dice "Allinllanam ___________".',
            'opciones'          => null,
            'respuesta_correcta'=> 'kachkani',
            'puntaje_max'       => 2,
            'orden'             => 4,
        ]);
    }

    // ── Unidad 2: Números ────────────────────────────────────────
    private function unidad2_Numeros(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 2: Yupay — Los Números',
            'descripcion' => 'Aprende los números del 1 al 100 y su uso en contextos cotidianos.',
            'orden'       => 2,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Números del 1 al 10 — Hukninmanta Chunkakama',
            'tipo'            => 'texto',
            'contenido_texto' => "# Los Números en Quechua\n\n## Del 1 al 10\n\n| # | Quechua | Pronunciación |\n|---|---|---|\n| 1 | Huk | [juk] |\n| 2 | Iskay | [is-kay] |\n| 3 | Kimsa | [kim-sa] |\n| 4 | Tawa | [ta-wa] |\n| 5 | Pichqa | [pich-qa] |\n| 6 | Suqta | [suq-ta] |\n| 7 | Qanchis | [qan-chis] |\n| 8 | Pusaq | [pu-saq] |\n| 9 | Isqun | [is-qun] |\n| 10 | Chunka | [chun-ka] |\n\n## Del 11 al 20\n\nLa lógica es: **Chunka** + número + **-niyuq**\n\n| # | Quechua |\n|---|---|\n| 11 | Chunka hukniyuq |\n| 12 | Chunka iskayniyuq |\n| 13 | Chunka kimsayuq |\n| 14 | Chunka tawayuq |\n| 15 | Chunka pichqayuq |\n| 16 | Chunka suqtayuq |\n| 17 | Chunka qanchisyuq |\n| 18 | Chunka pusaqniyuq |\n| 19 | Chunka isqunniyuq |\n| 20 | Iskay chunka |\n\n## Decenas\n\n| # | Quechua |\n|---|---|\n| 20 | Iskay chunka |\n| 30 | Kimsa chunka |\n| 40 | Tawa chunka |\n| 50 | Pichqa chunka |\n| 60 | Suqta chunka |\n| 70 | Qanchis chunka |\n| 80 | Pusaq chunka |\n| 90 | Isqun chunka |\n| 100 | Pachak |\n| 1000 | Waranqa |\n\n## 💡 Dato curioso\nEl sistema numérico quechua es **vigesimal** en algunos dialectos (base 20), pero el quechua moderno usa base 10, al igual que el español.",
            'duracion_min'    => 15,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Los números en contexto — práctica oral',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(2),
            'contenido_texto' => "📌 En este video practicarás los números con ejercicios de conteo y situaciones cotidianas como:\n- Decir tu edad\n- Contar objetos\n- Dar tu número de teléfono\n- Mencionar precios en el mercado\n\n💡 Pausa el video y repite en voz alta cada número.",
            'duracion_min'    => 20,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "7" en Quechua?',
            'opciones'          => ['Suqta', 'Qanchis', 'Pusaq', 'Isqun'],
            'respuesta_correcta'=> 'Qanchis',
            'puntaje_max'       => 1,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué número representa "Iskay chunka pichqayuq"?',
            'opciones'          => ['20', '25', '52', '15'],
            'respuesta_correcta'=> '25',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'El número 100 en quechua se dice "___________".',
            'opciones'          => null,
            'respuesta_correcta'=> 'Pachak',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => 'Si tienes 30 años, ¿cómo lo dices en quechua?',
            'opciones'          => ['Kimsa chunka watayuqmi kani', 'Tawa chunka watayuqmi kani', 'Chunka kimsa watayuqmi kani', 'Kimsa watayuqmi kani'],
            'respuesta_correcta'=> 'Kimsa chunka watayuqmi kani',
            'puntaje_max'       => 2,
            'orden'             => 4,
        ]);
    }

    // ── Unidad 3: Familia ────────────────────────────────────────
    private function unidad3_Familia(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 3: Ayllu — La Familia',
            'descripcion' => 'Vocabulario completo para hablar de tu familia en Quechua. Incluye términos únicos que no existen en español.',
            'orden'       => 3,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Miembros de la familia — Ayllumanta',
            'tipo'            => 'texto',
            'contenido_texto' => "# La Familia en Quechua — Ayllu\n\n## Familia nuclear\n\n| Quechua | Español | Nota |\n|---|---|---|\n| Tayta | Padre / Papá | |\n| Mama | Madre / Mamá | |\n| Wawa | Bebé / Hijo/a pequeño/a | |\n| Churin | Hijo (dicho por el padre) | |\n| Ususi | Hija (dicho por el padre) | |\n| Wawan | Hijo/a (dicho por la madre) | |\n\n## Hermanos/as — el quechua es más preciso que el español\n\n> ⚠️ En quechua, el término que uses **depende de tu propio género**, no del género del hermano/a.\n\n| Si eres **hombre** | Quechua | Español |\n|---|---|---|\n| Hermano (de hombre a hombre) | Wawqi | Brother |\n| Hermana (de hombre a mujer) | Pana | Sister |\n\n| Si eres **mujer** | Quechua | Español |\n|---|---|---|\n| Hermana (de mujer a mujer) | Ñaña | Sister |\n| Hermano (de mujer a hombre) | Tura | Brother |\n\n## Abuelos y más\n\n| Quechua | Español |\n|---|---|\n| Awki | Abuelo |\n| Awki mama / Hatun mama | Abuela |\n| Tullqa | Yerno / Cuñado |\n| Qhachun | Nuera / Cuñada |\n| Llumchu | Nieto |\n\n## Expresiones con familia\n\n| Quechua | Español |\n|---|---|\n| Aylluykim pi kachkan? | ¿Quiénes son tu familia? |\n| Iskay wawqiymi kan | Tengo dos hermanos |\n| Huk panallaymi kan | Solo tengo una hermana |\n| Manam wawqiyuqchu kani | No tengo hermanos |\n\n## 🧠 Para recordar\nLa palabra **Ayllu** no solo significa familia nuclear. En la cosmovisión andina, el ayllu es la comunidad entera: familia extendida, vecinos, territorio y pachamama incluidos.",
            'duracion_min'    => 15,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Mi familia en quechua — práctica con video',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(3),
            'contenido_texto' => "📌 En este video aprenderás a describir a tu familia completa. Presta atención especial a:\n\n- Los términos que cambian según el género del hablante (wawqi/pana vs. ñaña/tura)\n- Cómo usar el sufijo posesivo **-y** (mi) y **-yki** (tu)\n\n💬 Ejemplos:\n- Taytay = mi padre\n- Taytayki = tu padre\n- Mamay = mi madre\n- Mamayki = tu madre",
            'duracion_min'    => 18,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => 'Un hombre quechuahablante llama a su hermana "Pana". Si fuera una mujer hablando de su hermana, ¿qué diría?',
            'opciones'          => ['Wawqi', 'Pana', 'Ñaña', 'Tura'],
            'respuesta_correcta'=> 'Ñaña',
            'puntaje_max'       => 3,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "abuelo" en Quechua Cusco-Collao?',
            'opciones'          => ['Tayta', 'Awki', 'Wawqi', 'Llumchu'],
            'respuesta_correcta'=> 'Awki',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'La palabra quechua que engloba familia, comunidad y territorio es "___________".',
            'opciones'          => null,
            'respuesta_correcta'=> 'Ayllu',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);
    }

    // ── Unidad 4: El tiempo ──────────────────────────────────────
    private function unidad4_Tiempo(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 4: Punchaw — El Tiempo y los Días',
            'descripcion' => 'Días de la semana, meses del año, horas y expresiones temporales.',
            'orden'       => 4,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Días de la semana — Simana Punchawkuna',
            'tipo'            => 'texto',
            'contenido_texto' => "# El Tiempo en Quechua\n\n## Días de la semana\n\nLos días en quechua tienen nombres poéticos basados en astros y fenómenos naturales:\n\n| # | Quechua | Significado literal | Español |\n|---|---|---|---|\n| 1 | Intichaw | Día del Sol | Domingo |\n| 2 | Killachaw | Día de la Luna | Lunes |\n| 3 | Atichaw | Día de Marte (el guerrero) | Martes |\n| 4 | Qoyllurchaw | Día de las Estrellas | Miércoles |\n| 5 | Illapachaw | Día del Rayo | Jueves |\n| 6 | Chaskachaw | Día del Lucero | Viernes |\n| 7 | K'uychichaw | Día del Arcoíris | Sábado |\n\n## Los meses\n\n| Quechua | Español | Significado |\n|---|---|---|\n| Qhapaq Raymi Killa | Enero | Mes de la fiesta del Inca |\n| Pawqar Waray Killa | Febrero | Mes del florecimiento |\n| Pacha Puquy Killa | Marzo | Mes de maduración de la tierra |\n| Ayriwa Killa | Abril | Mes del maíz tierno |\n| Aymuray Killa | Mayo | Mes de la cosecha |\n| Inti Raymi Killa | Junio | Mes de la Fiesta del Sol |\n| Anta Situwa Killa | Julio | Mes de la purificación |\n| Qhapaq Situwa Killa | Agosto | Mes de la gran purificación |\n| Qoya Raymi Killa | Septiembre | Mes de la Reina (luna) |\n| Uma Raymi Killa | Octubre | Mes de la cabeza / inicio |\n| Ayamarq'a Killa | Noviembre | Mes de los difuntos |\n| Kapaq Raymi Killa | Diciembre | Mes de la gran fiesta |\n\n## Expresiones de tiempo\n\n| Quechua | Español |\n|---|---|\n| Kunan | Hoy |\n| Qayna | Ayer |\n| Paqarin | Mañana |\n| Saptu | Esta semana |\n| Killa | Luna / Mes |\n| Wata | Año |\n| Ñawpaq | Antes / Antiguo |\n| Qhipa | Después / Futuro |\n| Tutamanta | Por la mañana |\n| Chisipi | Por la tarde |\n| Tuta | Noche |\n\n## Preguntar la hora\n\n| Quechua | Español |\n|---|---|\n| Hayk'aqtaq? | ¿A qué hora? |\n| Ima horastataq? | ¿Qué hora es? |\n| Huk horapin | A la una |\n| Iskay horapin | A las dos |\n| Chunka iskayniyuq horapin | A las 12 |",
            'duracion_min'    => 18,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: El tiempo en el mundo andino — Pacha',
            'tipo'            => 'texto',
            'contenido_texto' => "# Pacha — Tiempo y Espacio en la Cosmovisión Andina\n\n## La palabra PACHA\n\nEn quechua, **Pacha** significa tanto **tiempo** como **espacio**. No existe una separación entre ellos como en el pensamiento occidental.\n\n| Concepto | Quechua | Significado |\n|---|---|---|\n| Tierra | Pacha | También significa mundo, tiempo, momento |\n| Pasado | Ñawpaq pacha | El tiempo de adelante (porque el pasado es lo que se ve) |\n| Futuro | Qhipa pacha | El tiempo de atrás (porque no se puede ver) |\n| Presente | Kunan pacha | El tiempo de ahora |\n| Universo | Kay pacha | Este mundo / esta realidad |\n| Inframundo | Ukhu pacha | El mundo de abajo |\n| Mundo superior | Hanan pacha | El mundo de arriba |\n\n## 🌟 Dato filosófico\nEn la cosmovisión andina, el pasado está **adelante** porque ya fue vivido y puede verse. El futuro está **atrás** porque no se ha visto aún. Por eso, **Ñawpaq** significa tanto \"adelante\" como \"antiguo/pasado\".",
            'duracion_min'    => 10,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "Viernes" en Quechua? (Pista: viene del nombre del Lucero del Alba)',
            'opciones'          => ['Killachaw', 'Intichaw', 'Chaskachaw', 'K\'uychichaw'],
            'respuesta_correcta'=> 'Chaskachaw',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa la palabra "Pacha" en quechua?',
            'opciones'          => ['Solo "tiempo"', 'Solo "espacio/tierra"', 'Tiempo y espacio a la vez', 'Solo "mundo"'],
            'respuesta_correcta'=> 'Tiempo y espacio a la vez',
            'puntaje_max'       => 3,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "mañana" en Quechua?',
            'opciones'          => ['Kunan', 'Qayna', 'Paqarin', 'Tuta'],
            'respuesta_correcta'=> 'Paqarin',
            'puntaje_max'       => 1,
            'orden'             => 3,
        ]);
    }

    // ── Unidad 5: Alimentos ──────────────────────────────────────
    private function unidad5_Alimentos(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 5: Mikhunakuna — Los Alimentos',
            'descripcion' => 'Vocabulario de alimentos andinos, mercado y cocina quechua.',
            'orden'       => 5,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Alimentos andinos — Mikhunakuna',
            'tipo'            => 'texto',
            'contenido_texto' => "# Los Alimentos en Quechua\n\n## Tubérculos y granos (origen andino)\n\n| Quechua | Español | Nota |\n|---|---|---|\n| Papa | Papa / Patata | ¡La palabra \"papa\" viene del quechua! |\n| Ch'uño | Chuño (papa deshidratada) | Técnica milenaria de preservación |\n| Moraya | Papa blanca deshidratada | Variante del chuño |\n| Sara | Maíz / Choclo | |\n| Kinwa | Quinua | Declarada por ONU como superfood |\n| Qañiwa | Cañihua | Grano andino rico en proteínas |\n| Oca | Oca | Tubérculo dulce |\n| Ulluku | Olluco | Tubérculo colorido |\n| Maswa | Mashua | Tubérculo medicinal |\n| Maqa | Maca | Raíz energizante del altiplano |\n\n## Bebidas\n\n| Quechua | Español |\n|---|---|\n| Yaku | Agua |\n| Aswa / Chicha | Chicha de jora (maíz fermentado) |\n| Api | Mazamorra de maíz morado o chuño |\n| Mate | Infusión de hierbas |\n\n## Carnes y proteínas\n\n| Quechua | Español |\n|---|---|\n| Ukuku / Cuy | Cuy (conejillo de indias) |\n| Alpaka aycha | Carne de alpaca |\n| Llama aycha | Carne de llama |\n| Challwa | Pescado |\n| Aycha | Carne (en general) |\n\n## En el mercado — Qhatu\n\n| Quechua | Español |\n|---|---|\n| Qhatu | Mercado |\n| Rantiyta munani | Quiero comprar |\n| Hayk'am chayqa? | ¿Cuánto cuesta eso? |\n| Askham chayqa! | ¡Eso es mucho! |\n| Pisitaña! | ¡Un poco más barato! |\n\n## 🌽 Sabías que...\nLa papa, el tomate, el chocolate, la quinua y el maíz son palabras de origen quechua o náhuatl que el mundo entero usa hoy. ¡Los andinos dieron al mundo más del 60% de las plantas cultivadas actuales!",
            'duracion_min'    => 15,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: En el mercado andino — práctica con video',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(4),
            'contenido_texto' => "📌 En este video practicarás el vocabulario del mercado y los alimentos en situaciones reales.\n\nActividad:\n1. Escucha el diálogo de compraventa\n2. Identifica los alimentos mencionados\n3. Repite las frases de negociación de precios",
            'duracion_min'    => 22,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué es el "Ch\'uño"?',
            'opciones'          => ['Un tipo de maíz', 'Papa deshidratada y conservada por frío', 'Una bebida fermentada', 'Un grano andino'],
            'respuesta_correcta'=> 'Papa deshidratada y conservada por frío',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "Mercado" en Quechua?',
            'opciones'          => ['Yaku', 'Mikhuna', 'Qhatu', 'Sara'],
            'respuesta_correcta'=> 'Qhatu',
            'puntaje_max'       => 1,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'La palabra "___________" en quechua significa agua.',
            'opciones'          => null,
            'respuesta_correcta'=> 'Yaku',
            'puntaje_max'       => 1,
            'orden'             => 3,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  NIVEL 2 — INTERMEDIO
    // ══════════════════════════════════════════════════════════════
    private function intermedioNivel(CursoEspecial $curso): void
    {
        $nivel = CeNivel::create([
            'curso_especial_id' => $curso->id,
            'nombre' => 'Nivel Intermedio — Chawpi Yachay',
            'orden'  => 2,
        ]);

        $this->unidadI1_Colores($nivel);
        $this->unidadI2_Verbos($nivel);
        $this->unidadI3_Cuerpo($nivel);
        $this->unidadI4_Naturaleza($nivel);
    }

    private function unidadI1_Colores(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 1: Tullpakunan — Los Colores',
            'descripcion' => 'Aprende los colores en Quechua y cómo usarlos para describir objetos.',
            'orden'       => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Los colores — Tullpakunan',
            'tipo'            => 'texto',
            'contenido_texto' => "# Los Colores en Quechua\n\n## Colores básicos\n\n| Quechua | Español | Curiosidad |\n|---|---|---|\n| Puka | Rojo | Color de la sangre, la vida |\n| Q'illu | Amarillo | Color del sol, del maíz |\n| Anqas | Azul | Color del cielo, del lago Titicaca |\n| Q'umir | Verde | Color de los cultivos, esperanza |\n| Yuraq | Blanco | Pureza, nieve de los Apus |\n| Yana | Negro | Noche, tierra fértil |\n| Oqe | Gris | Ceniza, neblina andina |\n| Ch'umpi | Marrón / Café | Color de la tierra, del cuy |\n| Kulli | Morado | Color real, de la chicha morada |\n| Wayra | Rosado | (literalmente: viento) |\n\n## Cómo usar los colores — sufijo adjetival\n\nEn quechua, el color va **antes** del sustantivo:\n\n| Quechua | Español |\n|---|---|\n| Puka punchaw | Día rojo (atardecer) |\n| Yuraq urqu | Montaña blanca (nevada) |\n| Q'umir allpa | Tierra verde |\n| Yana qaqa | Roca negra |\n\n## Describir objetos\n\n| Quechua | Español |\n|---|---|\n| Kay q'illu | Esto es amarillo |\n| Chay puka | Eso es rojo |\n| Imataq tullpan? | ¿De qué color es? |\n| Pukam | Es rojo |\n\n## 🌈 Los 7 colores del arcoíris en quechua\nEl arcoíris se llama **K'uychi** y tiene gran importancia espiritual en la cosmovisión andina. Sus colores representan la unión de los tres mundos: Hanan Pacha, Kay Pacha y Ukhu Pacha.",
            'duracion_min'    => 12,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Los colores con video — práctica oral',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(5),
            'contenido_texto' => "📌 En este video practicarás los colores en contexto. Actividad recomendada:\n\n1. Mira objetos a tu alrededor\n2. Intenta nombrarlos en quechua usando el color: [color] + [objeto]\n3. Ej: Q'umir silla (silla verde), Yana mesa (mesa negra)",
            'duracion_min'    => 18,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "blanco" en Quechua?',
            'opciones'          => ['Yana', 'Oqe', 'Yuraq', 'Puka'],
            'respuesta_correcta'=> 'Yuraq',
            'puntaje_max'       => 1,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa "K\'uychi"?',
            'opciones'          => ['Nube', 'Arcoíris', 'Relámpago', 'Lluvia'],
            'respuesta_correcta'=> 'Arcoíris',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => '"Montaña blanca (nevada)" en quechua se dice "Yuraq ___________".',
            'opciones'          => null,
            'respuesta_correcta'=> 'urqu',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);
    }

    private function unidadI2_Verbos(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 2: Ruwaykuna — Verbos Esenciales',
            'descripcion' => 'Los 20 verbos más usados en quechua cotidiano con conjugación en presente.',
            'orden'       => 2,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Verbos esenciales y conjugación en presente',
            'tipo'            => 'texto',
            'contenido_texto' => "# Verbos en Quechua — Ruwakuna\n\n## Los 20 verbos más usados\n\n| Infinitivo (raíz) | Español | Ejemplo |\n|---|---|---|\n| Kachkay | Estar / Ser | Kachkani — Estoy / Soy |\n| Ripuy | Ir | Ripuchkani — Estoy yendo |\n| Hamuy | Venir | Hamuchkani — Estoy viniendo |\n| Mikuy | Comer | Mikuchkani — Estoy comiendo |\n| Upyay | Beber | Upyachkani — Estoy bebiendo |\n| Puñuy | Dormir | Puñuchkani — Estoy durmiendo |\n| Rikhuy | Ver | Rikuchkani — Estoy viendo |\n| Uyariy | Escuchar | Uyarichkani — Estoy escuchando |\n| Rimay | Hablar | Rimachkani — Estoy hablando |\n| Qillqay | Escribir | Qillqachkani — Estoy escribiendo |\n| Yachay | Saber / Aprender | Yachachkani — Estoy aprendiendo |\n| Munay | Querer / Amar | Munani — Te quiero |\n| Maskay | Buscar | Maskachkani — Estoy buscando |\n| Rantiy | Comprar | Rantichkani — Estoy comprando |\n| Quy | Dar | Qurani — Di |\n| Apay | Llevar | Apachkani — Estoy llevando |\n| Tiyay | Sentarse / Vivir | Tiyachkani — Estoy sentado |\n| Sayay | Pararse | Sayachkani — Estoy parado |\n| Pukllay | Jugar | Pukllaychkani — Estoy jugando |\n| Llamk'ay | Trabajar | Llamk'achkani — Estoy trabajando |\n\n## Conjugación en presente — Sufijos personales\n\n| Persona | Sufijo | Ejemplo (Rimay = hablar) |\n|---|---|---|\n| Yo | -ni | Rimani — Hablo |\n| Tú | -nki | Rimanki — Hablas |\n| Él/Ella | -n | Riman — Habla |\n| Nosotros | -nchik | Rimanchik — Hablamos (incl.) |\n| Nosotros | -yku | Rimayku — Hablamos (excl.) |\n| Ustedes | -nkichik | Rimankichik — Hablan |\n| Ellos | -nku | Rimanku — Hablan |\n\n## Progresivo (-chka-)\nPara decir \"estoy haciendo algo\" se añade **-chka-** antes del sufijo personal:\n- Rima**chka**ni — Estoy hablando\n- Miku**chka**ni — Estoy comiendo\n- Yachaypi riku**chka**ni — Estoy viendo (aprendiendo)",
            'duracion_min'    => 20,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Verbos en práctica — video conversacional',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(6),
            'contenido_texto' => "📌 En este video escucharás conversaciones reales usando los verbos estudiados.\n\nEjercicio activo:\n- Cuando escuches un verbo, pausa el video\n- Identifica la persona gramatical (yo, tú, él...)\n- Anota el significado",
            'duracion_min'    => 25,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "Estoy comiendo" en Quechua?',
            'opciones'          => ['Mikuni', 'Mikuchkani', 'Mikurani', 'Mikunkichik'],
            'respuesta_correcta'=> 'Mikuchkani',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa el sufijo "-chka-" en los verbos quechuas?',
            'opciones'          => ['Pasado', 'Acción progresiva (estar + gerundio)', 'Futuro', 'Negación'],
            'respuesta_correcta'=> 'Acción progresiva (estar + gerundio)',
            'puntaje_max'       => 3,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => '"Estoy aprendiendo" en quechua se dice "Yacha___________ni".',
            'opciones'          => null,
            'respuesta_correcta'=> 'chka',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);
    }

    private function unidadI3_Cuerpo(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 3: Ukuku — El Cuerpo Humano',
            'descripcion' => 'Vocabulario del cuerpo humano y expresiones de salud.',
            'orden'       => 3,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Partes del cuerpo — Ukukukunan',
            'tipo'            => 'texto',
            'contenido_texto' => "# El Cuerpo Humano en Quechua\n\n## Cabeza y cara\n\n| Quechua | Español |\n|---|---|\n| Uma | Cabeza |\n| Ñawi | Ojo / Cara |\n| Rinri | Oreja |\n| Senqa | Nariz |\n| Simi | Boca / Idioma |\n| Kirun | Diente |\n| Qallu | Lengua |\n| Muqu | Mentón |\n| Aksu uma | Cabello |\n\n## Tronco\n\n| Quechua | Español |\n|---|---|\n| Kunka | Cuello / Garganta |\n| Qhasqo | Pecho |\n| Wasa | Espalda |\n| Siki | Cadera / Nalgas |\n| Pukyu | Ombligo |\n| Sunqu | Corazón |\n\n## Extremidades\n\n| Quechua | Español |\n|---|---|\n| Makki / Maki | Mano / Brazo |\n| Chaki | Pie / Pierna |\n| Qunqur | Rodilla |\n| Muqu | Codo |\n| Rurun | Dedo |\n\n## Expresiones de salud\n\n| Quechua | Español |\n|---|---|\n| Umaymi nanawan | Me duele la cabeza |\n| Simi nanawan | Me duele la garganta |\n| Onqosqam kachkani | Estoy enfermo/a |\n| Hampiy | Medicina / Curar |\n| Hampiq | Médico / Curandero |\n| Qonoy | Dar calor / Fiebre |\n| Chiriyay | Tener frío |\n\n## 💊 La medicina andina\n**Hampiq** es tanto el médico occidental como el curandero tradicional. En la cosmovisión andina, la salud es un equilibrio entre cuerpo, espíritu, comunidad y naturaleza.",
            'duracion_min'    => 15,
            'orden'           => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa la palabra "Simi" en Quechua?',
            'opciones'          => ['Nariz', 'Oído', 'Boca / Idioma', 'Ojo'],
            'respuesta_correcta'=> 'Boca / Idioma',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cómo se dice "Me duele la cabeza" en Quechua?',
            'opciones'          => ['Chakiymi nanawan', 'Umaymi nanawan', 'Rinriymi nanawan', 'Sunkuymi nanawan'],
            'respuesta_correcta'=> 'Umaymi nanawan',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);
    }

    private function unidadI4_Naturaleza(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 4: Pachamama — La Naturaleza Andina',
            'descripcion' => 'El vocabulario de la naturaleza en quechua y su relación con la cosmovisión andina.',
            'orden'       => 4,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: La Naturaleza — Pachamamakunan',
            'tipo'            => 'texto',
            'contenido_texto' => "# La Naturaleza en Quechua — Pachamamakunan\n\n## Elementos naturales\n\n| Quechua | Español |\n|---|---|\n| Pachamama | Madre Tierra |\n| Inti | Sol |\n| Killa | Luna |\n| Quyllur | Estrella |\n| Para | Lluvia |\n| Riti | Nieve / Granizo |\n| Wayra | Viento |\n| Nina | Fuego |\n| Yaku | Agua |\n| Allpa | Tierra / Suelo |\n| Urqu / Apu | Montaña / Montaña sagrada |\n| Qucha | Lago / Mar |\n| Mayu | Río |\n| Sach'a | Árbol / Bosque |\n| Pasto | Pasto / Hierba |\n| Rumi | Piedra / Roca |\n| Illapa | Rayo / Trueno |\n| K'uychi | Arcoíris |\n\n## Los Apus\n**Apu** es la montaña sagrada. En la cosmovisión andina, cada Apu es una deidad tutelar que protege a las comunidades. Los principales Apus del Cusco son:\n- **Ausangate** (6,384 m) — el más poderoso del Cusco\n- **Salkantay** (6,271 m) — el salvaje / indomable\n- **Pitusiray** y **Sawasiray** — los gemelos enamorados\n\n## El agua sagrada\n| Quechua | Español |\n|---|---|\n| Yaku | Agua |\n| Puyus | Nubes |\n| Puquyu | Manantial / Ojo de agua |\n| Unuqu | Inundación |\n| Wayqo | Quebrada / Barranco |\n\n## 🌍 Concepto de Sumaq Kawsay\n**Sumaq Kawsay** (Buen Vivir) es el principio filosófico andino que propone vivir en armonía con la naturaleza, la comunidad y uno mismo. Ha sido incorporado a las Constituciones de Ecuador (2008) y Bolivia (2009).",
            'duracion_min'    => 18,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Canto y poesía en quechua — Harawi',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(7),
            'contenido_texto' => "📌 El **Harawi** es el género poético y musical más antiguo del mundo andino. A través del canto quechua aprenderás vocabulario de naturaleza de forma natural y memorable.\n\n💡 El oído aprende más rápido que la memoria. Escucha, repite e intenta entender las palabras una por una.",
            'duracion_min'    => 20,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué es un "Apu" en la cosmovisión andina?',
            'opciones'          => ['Un tipo de planta medicinal', 'Una montaña sagrada y deidad tutelar', 'Un instrumento musical', 'Un tipo de danza'],
            'respuesta_correcta'=> 'Una montaña sagrada y deidad tutelar',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa "Sumaq Kawsay"?',
            'opciones'          => ['Buen alimento', 'Buen Vivir', 'Buena tierra', 'Buen sol'],
            'respuesta_correcta'=> 'Buen Vivir',
            'puntaje_max'       => 2,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'El nombre del Sol en quechua es "___________".',
            'opciones'          => null,
            'respuesta_correcta'=> 'Inti',
            'puntaje_max'       => 1,
            'orden'             => 3,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    //  NIVEL 3 — AVANZADO
    // ══════════════════════════════════════════════════════════════
    private function avanzadoNivel(CursoEspecial $curso): void
    {
        $nivel = CeNivel::create([
            'curso_especial_id' => $curso->id,
            'nombre' => 'Nivel Avanzado — Hatun Yachay',
            'orden'  => 3,
        ]);

        $this->unidadA1_Conversacion($nivel);
        $this->unidadA2_Literatura($nivel);
        $this->unidadA3_Cosmovisión($nivel);
    }

    private function unidadA1_Conversacion(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 1: Rimaykuna — Conversación Fluida',
            'descripcion' => 'Diálogos completos, narración de experiencias y debate en quechua.',
            'orden'       => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: El pasado y el futuro — sufijos temporales',
            'tipo'            => 'texto',
            'contenido_texto' => "# Tiempos verbales en Quechua\n\n## Pasado (-rqa-)\n\nSe añade **-rqa-** entre la raíz y el sufijo personal:\n\n| Quechua | Español |\n|---|---|\n| Rimayrqani | Hablé |\n| Mikurqani | Comí |\n| Puñurqani | Dormí |\n| Hamuyrqani | Vine |\n| Rimarqanki | Hablaste |\n\n## Futuro (-nqa-)\n\nSe añade **-nqa-** para el futuro:\n\n| Quechua | Español |\n|---|---|\n| Rimanqani | Hablaré |\n| Mikunqani | Comeré |\n| Hamunqani | Vendré |\n| Puñunqani | Dormiré |\n\n## Pasado lejano / mítico (-sqa-)\n\nPara eventos que no se vivieron directamente (mitos, historias, leyendas):\n\n| Quechua | Español |\n|---|---|\n| Kaywarqasqa | Fue (según dicen) |\n| Rimasqasqa | Habló (en los tiempos antiguos) |\n\n💡 Este sufijo se llama **\"evidencial\"** — indica cómo el hablante obtuvo la información. El quechua tiene sufijos para distinguir si viste algo con tus propios ojos o si te lo contaron.\n\n## Evidenciales quechuas\n\n| Sufijo | Uso | Ejemplo |\n|---|---|---|\n| -mi / -m | Testigo directo (yo lo vi) | Paypim hamuran — Él vino (yo lo vi) |\n| -si / -s | Información de terceros | Paysi hamuran — Dicen que él vino |\n| -cha | Conjetura / duda | Paycha hamuran — Quizá él vino |",
            'duracion_min'    => 25,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: Conversación avanzada con video',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(8),
            'contenido_texto' => "📌 En este video escucharás conversaciones fluidas entre hablantes nativos de quechua cusqueño.\n\nReto: Identifica al menos 5 verbos conjugados y determina:\n1. ¿En qué tiempo están?\n2. ¿Qué sufijo evidencial usan?",
            'duracion_min'    => 30,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué sufijo se usa en quechua para narrar hechos que no se vivieron directamente (mitos, leyendas)?',
            'opciones'          => ['-rqa-', '-nqa-', '-sqa-', '-chka-'],
            'respuesta_correcta'=> '-sqa-',
            'puntaje_max'       => 3,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => 'En la frase "Paypim hamuran", el sufijo -mi indica que...',
            'opciones'          => ['El hablante oyó la noticia de otra persona', 'El hablante fue testigo directo del hecho', 'El hablante está haciendo una conjetura', 'La acción es en el futuro'],
            'respuesta_correcta'=> 'El hablante fue testigo directo del hecho',
            'puntaje_max'       => 3,
            'orden'             => 2,
        ]);
    }

    private function unidadA2_Literatura(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 2: Qillqay — Literatura y Poesía Quechua',
            'descripcion' => 'Los grandes textos literarios del mundo quechua: desde el Ollantay hasta la poesía contemporánea.',
            'orden'       => 2,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: El Ollantay — drama clásico quechua',
            'tipo'            => 'texto',
            'contenido_texto' => "# Literatura Quechua — Qillqay\n\n## El Ollantay\n\nEl **Ollantay** es el drama más importante de la literatura quechua. Narra el amor imposible entre Ollantay, un guerrero de origen humilde, y Cusi Qoyllur (Estrella Alegre), hija del Inca.\n\n### Fragmento famoso:\n```\nOllantay:\nCusi Qoyllurmi sunquypi\nTikariykushan sumaqta,\nSunquypi suyaykushan.\n\n(Mi estrella alegre\nflorece hermosamente en mi corazón,\nespera en mi corazón.)\n```\n\n## Poetas quechuas contemporáneos\n\n**Dida Aguirre** (Huancavelica, 1953):\n```\nRuraqmi kani,\nKawsaypi kawsaq,\nPachamamapin kawsayniy.\n\n(Soy creadora,\nviviente de la vida,\nmi existencia está en la Pachamama.)\n```\n\n**José María Arguedas** — Himno al Apu Inti:\n```\nInti taytay, qoñiwan\npacha chawpipi kawsachiwayku.\n\n(Sol padre, con tu calor\nhaz que vivamos en el centro del mundo.)\n```\n\n## 📚 Para leer más\n- **Manuscrito de Huarochirí** (1608) — mitología andina en quechua\n- **Nueva Corónica y Buen Gobierno** de Guamán Poma de Ayala\n- **Poesía quechua** compilada por Jesús Lara",
            'duracion_min'    => 20,
            'orden'           => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa el nombre "Cusi Qoyllur"?',
            'opciones'          => ['Luna alegre', 'Estrella alegre', 'Sol alegre', 'Tierra alegre'],
            'respuesta_correcta'=> 'Estrella alegre',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);
    }

    private function unidadA3_Cosmovisión(CeNivel $nivel): void
    {
        $u = CeUnidad::create([
            'ce_nivel_id' => $nivel->id,
            'nombre'      => 'Unidad 3: Yuyaykuna — Cosmovisión Andina',
            'descripcion' => 'El pensamiento filosófico andino expresado a través del idioma quechua.',
            'orden'       => 3,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 1: Los tres mundos — Kinsa Pacha',
            'tipo'            => 'texto',
            'contenido_texto' => "# La Cosmovisión Andina en Quechua\n\n## Los Tres Mundos — Kinsa Pacha\n\n| Mundo | Quechua | Seres |\n|---|---|---|\n| Mundo de arriba | Hanan Pacha | Sol (Inti), Luna (Killa), Estrellas, dioses |\n| Este mundo | Kay Pacha | Seres humanos, animales, plantas |\n| Mundo de abajo | Ukhu Pacha | Ancestros, semillas, gestación |\n\n## Animales sagrados — Chura\n\n| Animal | Quechua | Mundo que representa |\n|---|---|---|\n| Cóndor | Kuntur | Hanan Pacha (cielo) |\n| Puma | Puma | Kay Pacha (tierra) |\n| Serpiente | Amaru | Ukhu Pacha (inframundo) |\n\nEstos tres animales forman la **Chakana** (Cruz Andina) ☩ que es el símbolo más importante de la cosmovisión andina.\n\n## Principios filosóficos andinos\n\n| Quechua | Principio |\n|---|---|\n| Ayni | Reciprocidad — dar y recibir en equilibrio |\n| Minka | Trabajo comunitario (del que viene \"Mink'arikuy\") |\n| Sumaq Kawsay | Buen Vivir — armonía total |\n| Yanantin | Complementariedad de opuestos |\n| Masintin | Igualdad de semejantes |\n\n## La Chakana — Cruz Andina\nLa **Chakana** (de chaka = puente + hana = arriba) es la constelación de la Cruz del Sur. Representa:\n- Los 4 puntos cardinales\n- Los 3 mundos\n- Los 4 elementos\n- El calendario agrícola\n- La jerarquía social inca",
            'duracion_min'    => 25,
            'orden'           => 1,
        ]);

        CeLeccion::create([
            'ce_unidad_id'    => $u->id,
            'nombre'          => 'Lección 2: El Inti Raymi — Fiesta del Sol',
            'tipo'            => 'video',
            'archivo_url'     => $this->ytIndex(9),
            'contenido_texto' => "📌 El **Inti Raymi** (Fiesta del Sol) se celebra cada 24 de junio en el solsticio de invierno austral. Es la festividad más importante del mundo andino y se realiza en la fortaleza de Sacsayhuamán, Cusco.\n\nEscucha los discursos en quechua puro y trata de identificar:\n- Palabras del vocabulario que ya conoces\n- Los sufijos evidenciales\n- Expresiones rituales",
            'duracion_min'    => 30,
            'orden'           => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Cuál es el animal que representa el "Ukhu Pacha" (mundo de abajo) en la cosmovisión andina?',
            'opciones'          => ['El cóndor (Kuntur)', 'El puma (Puma)', 'La serpiente (Amaru)', 'El llama'],
            'respuesta_correcta'=> 'La serpiente (Amaru)',
            'puntaje_max'       => 2,
            'orden'             => 1,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'multiple',
            'pregunta'          => '¿Qué significa "Ayni" en el pensamiento andino?',
            'opciones'          => ['Trabajo forzado', 'Reciprocidad — dar y recibir en equilibrio', 'Tributo al Estado', 'Fiesta comunal'],
            'respuesta_correcta'=> 'Reciprocidad — dar y recibir en equilibrio',
            'puntaje_max'       => 3,
            'orden'             => 2,
        ]);

        CeEjercicio::create([
            'ce_unidad_id'      => $u->id,
            'tipo'              => 'completar',
            'pregunta'          => 'La "Chakana" es la representación de la constelación de la Cruz del ___________ en la cosmovisión andina.',
            'opciones'          => null,
            'respuesta_correcta'=> 'Sur',
            'puntaje_max'       => 2,
            'orden'             => 3,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function yt(string $videoId): string
    {
        return "https://www.youtube.com/embed/{$videoId}?list=" . self::PLAYLIST . '&rel=0';
    }

    private function ytIndex(int $index): string
    {
        return 'https://www.youtube.com/embed/videoseries?list=' . self::PLAYLIST . "&index={$index}&rel=0";
    }
}
