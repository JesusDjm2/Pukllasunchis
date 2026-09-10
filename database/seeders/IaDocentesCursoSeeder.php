<?php

namespace Database\Seeders;

use App\Models\CursosEspeciales\CeEjercicio;
use App\Models\CursosEspeciales\CeLeccion;
use App\Models\CursosEspeciales\CeNivel;
use App\Models\CursosEspeciales\CeUnidad;
use App\Models\CursosEspeciales\CursoEspecial;
use Illuminate\Database\Seeder;

class IaDocentesCursoSeeder extends Seeder
{
    public function run(): void
    {
        if (CursoEspecial::where('nombre', 'Uso de Herramientas de IA para Docentes')->exists()) {
            $this->command->info('El curso de IA para Docentes ya existe. Seeder omitido.');
            return;
        }

        $curso = CursoEspecial::create([
            'nombre'      => 'Uso de Herramientas de IA para Docentes',
            'descripcion' => 'Curso para que los docentes de nivel Inicial y Primaria conozcan, reconozcan y sepan elegir las mejores herramientas de Inteligencia Artificial vigentes, integrándolas como aliadas para agilizar su trabajo pedagógico (planificación, materiales, evaluación, comunicación con familias, atención a la diversidad).',
            'activo'      => true,
            'orden'       => (int) (CursoEspecial::max('orden') ?? 0) + 1,
        ]);

        foreach (self::DATA as $nivelData) {
            $nivel = CeNivel::create([
                'curso_especial_id' => $curso->id,
                'nombre' => $nivelData['nombre'],
                'orden'  => $nivelData['orden'],
            ]);

            foreach ($nivelData['unidades'] as $unidadData) {
                $unidad = CeUnidad::create([
                    'ce_nivel_id' => $nivel->id,
                    'nombre'      => $unidadData['nombre'],
                    'descripcion' => $unidadData['descripcion'],
                    'orden'       => $unidadData['orden'],
                ]);

                foreach ($unidadData['lecciones'] as $leccionData) {
                    CeLeccion::create([
                        'ce_unidad_id'    => $unidad->id,
                        'nombre'          => $leccionData['nombre'],
                        'tipo'            => $leccionData['tipo'],
                        'contenido_texto' => $leccionData['contenido_texto'],
                        'duracion_min'    => $leccionData['duracion_min'],
                        'orden'           => $leccionData['orden'],
                    ]);
                }

                foreach ($unidadData['ejercicios'] as $ejercicioData) {
                    CeEjercicio::create([
                        'ce_unidad_id'       => $unidad->id,
                        'tipo'               => $ejercicioData['tipo'],
                        'pregunta'           => $ejercicioData['pregunta'],
                        'opciones'           => $ejercicioData['opciones'] ?? null,
                        'respuesta_correcta' => $ejercicioData['respuesta_correcta'],
                        'puntaje_max'        => $ejercicioData['puntaje_max'],
                        'orden'              => $ejercicioData['orden'],
                    ]);
                }
            }
        }

        $this->command->info('✅ Curso "Uso de Herramientas de IA para Docentes" creado con éxito.');
    }

    private const DATA = [
        [
            'nombre' => 'Básico',
            'orden'  => 1,
            'unidades' => [
                [
                    'nombre'      => 'Introducción a la IA en el Aula',
                    'descripcion' => 'Comprender qué es la IA, mitos comunes y su potencial en educación inicial y primaria.',
                    'orden'       => 1,
                    'lecciones' => [
                        [
                            'nombre'          => '¿Qué es y qué no es la Inteligencia Artificial?',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>¿Qué es la IA?</h4><p>Imagina que la IA es como un asistente muy rápido que ha leído millones de libros, páginas web y conversaciones. No piensa como nosotros, sino que reconoce patrones y predice respuestas basadas en esos datos. Es como un niño que aprende viendo muchos ejemplos.</p><h4>Mitos comunes</h4><ul><li><strong>Mito 1:</strong> \"La IA va a reemplazar a los docentes\". Realidad: La IA es una herramienta, no un reemplazo. La conexión humana y la pedagogía siguen siendo centrales.</li><li><strong>Mito 2:</strong> \"Necesito ser programador para usarla\". Realidad: La mayoría de herramientas son tan sencillas como usar WhatsApp o buscar en Google.</li><li><strong>Mito 3:</strong> \"Solo sirve para secundaria o universidad\". Realidad: Hay herramientas para crear cuentos infantiles, generar fichas de trazos, o adaptar actividades para niños con NEE.</li></ul><h4>Ejemplo en Primaria</h4><p>Usa ChatGPT para generar 5 preguntas de comprensión lectora sobre un cuento que leerás en clase. Ejemplo: \"Genera 5 preguntas para niños de 2do grado sobre el cuento 'El patito feo'. Incluye preguntas literales e inferenciales\".</p><h4>Mini-reto práctico</h4><p>Abre cualquier herramienta de IA (como ChatGPT o Gemini) y pídele que te dé 3 ideas para saludar a tus alumnos al inicio de la jornada. Anota la que más te guste para probar esta semana.</p>",
                            'duracion_min'    => 12,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => '¿Cómo integrar IA sin miedo?',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Empieza pequeño</h4><p>No necesitas usar IA para todo desde el día 1. Elige una tarea que te tome tiempo y que no requiera tu expertise pedagógica directa, por ejemplo: redactar un comunicado a familias, generar un listado de palabras para dictado, o crear una sopa de letras con vocabulario de la unidad.</p><h4>El ciclo propuesta-validación</h4><ul><li><strong>Propuesta:</strong> La IA te da un borrador o idea.</li><li><strong>Validación:</strong> Tú revisas, ajustas y pones tu sello pedagógico. La IA puede equivocarse o dar respuestas genéricas.</li><li><strong>Implementación:</strong> Usas el resultado en tu aula.</li></ul><h4>Ejemplo en Inicial</h4><p>Pide a la IA: \"Escribe un mensaje breve y afectuoso para las familias de mi sala de 4 años, informando que la próxima semana empezaremos el proyecto de los animales de la granja. Incluye un pedido de colaboración: que traigan una foto de una mascota\". Revisa el mensaje, ajusta el tono a tu estilo y envíalo.</p><h4>Mini-reto práctico</h4><p>Redacta un comunicado para tus familias con ayuda de IA. Luego edítalo: cambia una palabra por una más cálida, añade un emoji y verifica que los horarios estén correctos. Ese mismo comunicado lo enviarás por el medio que uses habitualmente.</p>",
                            'duracion_min'    => 10,
                            'orden'           => 2,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cuál de las siguientes afirmaciones sobre la IA es correcta?',
                            'opciones'           => ['La IA es un reemplazo del docente', 'La IA solo se puede usar si sabes programar', 'La IA es una herramienta que complementa el trabajo del docente', 'La IA no sirve para educación inicial'],
                            'respuesta_correcta' => 'La IA es una herramienta que complementa el trabajo del docente',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cuál es el primer paso recomendado para integrar IA en el aula?',
                            'opciones'           => ['Capacitar a todos los alumnos en IA', 'Elegir una tarea administrativa o repetitiva para empezar', 'Comprar la herramienta más cara del mercado', 'Dejar que la IA planifique todo el año escolar'],
                            'respuesta_correcta' => 'Elegir una tarea administrativa o repetitiva para empezar',
                            'puntaje_max'        => 10,
                            'orden'              => 2,
                        ],
                    ],
                ],
                [
                    'nombre'      => 'Asistentes de Texto y Conversación',
                    'descripcion' => 'Exploración de herramientas como ChatGPT, Gemini o Copilot para tareas cotidianas docentes.',
                    'orden'       => 2,
                    'lecciones' => [
                        [
                            'nombre'          => 'ChatGPT: Tu asistente para planificar',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Más que un chatbot</h4><p>ChatGPT es un modelo de lenguaje que puede ayudarte a generar ideas, borradores y secuencias didácticas. No es un oráculo: es un asistente que necesita indicaciones claras (prompts).</p><h4>La técnica del prompt efectivo</h4><ul><li><strong>Contexto:</strong> ¿Qué grado/nivel? ¿Cuántos alumnos? ¿Qué tema?</li><li><strong>Rol:</strong> Pide que actúe como un docente experto en Inicial o Primaria.</li><li><strong>Tarea específica:</strong> ¿Qué quieres generar? ¿Objetivos, actividades, preguntas, rúbrica?</li><li><strong>Formato:</strong> ¿Lista, tabla, párrafos, pasos?</li><li><strong>Restricciones:</strong> Ej: \"Usa lenguaje simple\", \"incluye juego\", \"sin materiales costosos\".</li></ul><h4>Ejemplo en Primaria (3er grado)</h4><p><strong>Prompt:</strong> \"Actúa como docente de 3er grado. Diseña una secuencia de 3 sesiones para enseñar fracciones usando materiales concretos (como frutas o bloques). Incluye: objetivo de cada sesión, actividad inicial, desarrollo y cierre. Las sesiones deben durar 45 minutos cada una.\"</p><h4>Mini-reto práctico</h4><p>Escribe un prompt para generar una actividad de inicio de clase para tu próximo tema. Pide a la IA que te dé 3 opciones y elige la que mejor se adapte a tu grupo. Guarda el prompt en un documento para reutilizarlo.</p>",
                            'duracion_min'    => 15,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => 'Gemini y Copilot: Alternativas gratuitas',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Conoce las opciones</h4><p>Además de ChatGPT (que tiene versión gratuita), existen otras herramientas gratuitas con diferentes fortalezas:</p><ul><li><strong>Gemini (Google):</strong> Integrado con Google Workspace, útil si usas Drive, Docs o Gmail. Puede resumir correos o ayudar a redactar documentos.</li><li><strong>Copilot (Microsoft):</strong> Integrado con Office 365. Puede ayudarte a crear presentaciones en PowerPoint o analizar datos en Excel.</li><li><strong>Claude (Anthropic):</strong> Muy bueno para textos largos y análisis detallados. Excelente para revisar secuencias didácticas extensas.</li></ul><h4>¿Cuál elegir?</h4><p>Prueba con la que tengas más fácil acceso. Todas hacen tareas similares con pequeños matices. La clave es tu habilidad para hacer buenos prompts.</p><h4>Ejemplo en Inicial</h4><p>Usa Gemini para generar una lista de canciones infantiles sobre los animales para tu salón de 3 años. Luego pídele que te ayude a escribir una pequeña coreografía para una de ellas.</p><h4>Mini-reto práctico</h4><p>Si usas correo Gmail u Outlook, prueba el asistente de IA integrado (\"Ayúdame a redactar\" o \"Sugerir respuestas\") para responder un correo de una familia. Observa cómo sugiere redacciones y elige la más adecuada.</p>",
                            'duracion_min'    => 12,
                            'orden'           => 2,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cuál es un elemento clave al redactar un buen prompt para IA?',
                            'opciones'           => ['Usar solo palabras clave sin contexto', 'Incluir contexto, rol, tarea y formato', 'Escribir todo en mayúsculas', 'No especificar el nivel educativo'],
                            'respuesta_correcta' => 'Incluir contexto, rol, tarea y formato',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Intermedio',
            'orden'  => 2,
            'unidades' => [
                [
                    'nombre'      => 'Creación de Materiales Didácticos',
                    'descripcion' => 'Herramientas para generar fichas, cuentos, imágenes y presentaciones adaptadas.',
                    'orden'       => 1,
                    'lecciones' => [
                        [
                            'nombre'          => 'Generación de cuentos e historias personalizadas',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>La magia de las historias personalizadas</h4><p>Los cuentos son poderosos en Inicial y Primaria. Con IA puedes crear historias donde los protagonistas sean tus propios alumnos o incluyan sus intereses.</p><h4>Herramientas</h4><ul><li><strong>ChatGPT/Gemini:</strong> Generan el texto del cuento a partir de un prompt.</li><li><strong>Storybird o Tome:</strong> Plataformas que ayudan a ilustrar historias con imágenes generadas o bancos de imágenes.</li><li><strong>My Storybook:</strong> Permite crear libros ilustrados en línea (más manual, menos IA, pero buen complemento).</li></ul><h4>Ejemplo en Inicial</h4><p>Prompt: \"Crea un cuento corto (5 párrafos) para niños de 5 años sobre un pato llamado Lucas que quiere aprender a volar. Incluye un momento donde Lucas se siente frustrado y otro donde recibe ayuda de sus amigos. El cuento debe tener un final feliz y enseñar sobre la perseverancia.\"</p><h4>Mini-reto práctico</h4><p>Genera un cuento donde el personaje principal tenga el nombre de uno de tus alumnos. Luego, lee el cuento en clase y observa su reacción. Puedes incluso pedir a los niños que dibujen al personaje.</p>",
                            'duracion_min'    => 14,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => 'Fichas y actividades imprimibles con IA',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Fichas a la carta</h4><p>Crear fichas de trabajo puede ser tedioso. La IA puede generar ejercicios de caligrafía, sumas, sopas de letras, o actividades de asociación en segundos.</p><h4>Herramientas</h4><ul><li><strong>ChatGPT:</strong> Genera el enunciado y contenido de la ficha.</li><li><strong>Canva + Magic Studio:</strong> Con IA integrada para editar y generar plantillas.</li><li><strong>Worksheets AI:</strong> Herramienta especializada en crear fichas en PDF (versión gratuita limitada).</li></ul><h4>Ejemplo en Primaria (1er grado)</h4><p>Prompt: \"Genera 5 ejercicios de sumas con números del 1 al 10 para niños de 1er grado. Cada ejercicio debe tener un dibujo de frutas para ayudar a contar. Incluye un espacio para que el niño escriba su nombre y la fecha.\" Luego, copia ese texto en Canva o Word, añade algunos dibujos simples y tendrás tu ficha lista.</p><h4>Mini-reto práctico</h4><p>Genera una ficha para repasar las vocales con niños de 4 años. Pide a la IA que incluya imágenes de objetos que empiecen con cada vocal. Imprime la ficha y úsala en tu próxima clase.</p>",
                            'duracion_min'    => 13,
                            'orden'           => 2,
                        ],
                        [
                            'nombre'          => 'Imágenes y presentaciones con IA',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Más que texto: imágenes</h4><p>Herramientas como DALL-E (en ChatGPT Plus), Leonardo AI o Canva AI permiten crear imágenes desde descripciones textuales. Ideal para generar ilustraciones para cuentos, tarjetas de vocabulario o fondos para tus presentaciones.</p><h4>Presentaciones</h4><p>Gamma.app y Tome son herramientas que, con un prompt, generan presentaciones completas con texto, imágenes y diseño. Ahorran horas de maquetación.</p><h4>Ejemplo en Primaria (Ciencias Naturales)</h4><p>Prompt para Gamma: \"Crea una presentación de 5 diapositivas sobre el ciclo del agua para niños de 4to grado. Incluye: definición, etapas (evaporación, condensación, precipitación), y un experimento simple. Usa un tono divertido y colores brillantes.\"</p><h4>Mini-reto práctico</h4><p>Elige un tema de la próxima unidad y genera una presentación con ayuda de IA. Revísala, ajusta el contenido que no sea preciso y agrégale una actividad interactiva (como una pregunta al final para tus alumnos).</p>",
                            'duracion_min'    => 15,
                            'orden'           => 3,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Qué herramienta es más adecuada para generar una presentación completa a partir de un tema?',
                            'opciones'           => ['Solamente ChatGPT', 'Gamma.app o Tome', 'Solamente Word', 'No existe herramienta para eso'],
                            'respuesta_correcta' => 'Gamma.app o Tome',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => 'Al generar un cuento con IA para Inicial, ¿qué es recomendable incluir en el prompt?',
                            'opciones'           => ['Solo el nombre del cuento', 'Edad de los niños, tema, moraleja y extensión', 'Una lista de palabras difíciles', 'No es necesario dar detalles'],
                            'respuesta_correcta' => 'Edad de los niños, tema, moraleja y extensión',
                            'puntaje_max'        => 10,
                            'orden'              => 2,
                        ],
                    ],
                ],
                [
                    'nombre'      => 'Evaluación y Retroalimentación',
                    'descripcion' => 'Creación de rúbricas, retroalimentación personalizada y adaptaciones.',
                    'orden'       => 2,
                    'lecciones' => [
                        [
                            'nombre'          => 'Rúbricas e indicadores con IA',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Rúbricas en minutos</h4><p>Diseñar rúbricas de evaluación requiere pensar en criterios, niveles y descriptores. La IA puede hacer un borrador que luego ajustas.</p><h4>Estructura para el prompt</h4><ul><li><strong>Qué evaluar:</strong> Ej: \"exposición oral\", \"producción escrita\", \"trabajo en equipo\".</li><li><strong>Niveles:</strong> Generalmente 3 o 4 (Ej: Inicio, Proceso, Logrado, Destacado).</li><li><strong>Dimensiones:</strong> Ej: contenido, claridad, creatividad, ortografía.</li><li><strong>Edad:</strong> Ajusta el lenguaje de los descriptores.</li></ul><h4>Ejemplo en Primaria</h4><p>Prompt: \"Actúa como docente de 3er grado. Diseña una rúbrica para evaluar una exposición oral sobre animales en peligro de extinción. La rúbrica debe tener 4 niveles (Inicio-Proceso-Logrado-Destacado) y 3 criterios: Contenido (precisión), Claridad al hablar, y Uso de material de apoyo (imágenes o carteles). Los descriptores deben ser comprensibles para niños de 8 años.\"</p><h4>Mini-reto práctico</h4><p>Genera una rúbrica para un proyecto de tu próxima unidad. Luego, adáptala: simplifica el lenguaje de un descriptor o agrega un criterio específico de tu contexto. Imprímela para tenerla lista.</p>",
                            'duracion_min'    => 14,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => 'Retroalimentación personalizada y atención a la diversidad',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Comentarios para cada alumno</h4><p>Dar retroalimentación individual a 20 o 30 alumnos es demandante. La IA puede redactar borradores de comentarios positivos y sugerencias de mejora.</p><h4>Prompt para retroalimentación</h4><p>Proporciona a la IA: nombre del alumno, logro observado, área de oportunidad, y tono deseado (ej: alentador, formal).</p><h4>Atención a la diversidad</h4><ul><li><strong>Adaptaciones de actividades:</strong> Pide a la IA que reformule una actividad para un alumno con TDAH (más corta, más visual) o para un alumno con dislexia (letra más grande, menos texto).</li><li><strong>Materiales alternativos:</strong> Genera versiones simplificadas de textos o actividades con apoyos visuales.</li></ul><h4>Ejemplo en Inicial</h4><p>Prompt: \"Escribe un comentario de retroalimentación para un niño de 5 años que está aprendiendo a recortar. Menciona que se esfuerza pero necesita practicar más la precisión. Usa un tono muy positivo y alentador, pensado para compartir con su familia.\"</p><h4>Mini-reto práctico</h4><p>Escribe un breve comentario de retroalimentación para 2 alumnos con ayuda de IA. Luego, personaliza cada comentario añadiendo un detalle específico que hayas observado en clase.</p>",
                            'duracion_min'    => 16,
                            'orden'           => 2,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cuál es un buen uso de la IA para atención a la diversidad?',
                            'opciones'           => ['Generar la misma actividad para todos', 'Crear versiones adaptadas de una actividad para diferentes necesidades', 'Reemplazar completamente al docente de apoyo', 'Ignorar las adaptaciones'],
                            'respuesta_correcta' => 'Crear versiones adaptadas de una actividad para diferentes necesidades',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                    ],
                ],
            ],
        ],
        [
            'nombre' => 'Avanzado',
            'orden'  => 3,
            'unidades' => [
                [
                    'nombre'      => 'Comunicación con Familias y Comunidad',
                    'descripcion' => 'Uso de IA para mejorar la comunicación escrita y la participación de las familias.',
                    'orden'       => 1,
                    'lecciones' => [
                        [
                            'nombre'          => 'Newsletters, circulares y mensajes efectivos',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Comunicación clara y atractiva</h4><p>Las familias reciben mucha información. La IA puede ayudarte a redactar mensajes claros, con estructura atractiva y adaptados al medio (WhatsApp, correo, cuaderno de comunicados).</p><h4>Estrategias</h4><ul><li><strong>Personalización:</strong> Incluye el nombre del grado o proyecto.</li><li><strong>Llamado a la acción:</strong> ¿Qué esperas que haga la familia? (leer, firmar, enviar material).</li><li><strong>Tono:</strong> Cálido pero profesional. La IA puede sugerir versiones.</li></ul><h4>Ejemplo en Inicial</h4><p>Prompt: \"Redacta un newsletter semanal para familias de sala de 4 años. Incluye: agradecimiento por asistencia a la reunión, tema de la semana (colores), una actividad para hacer en casa (clasificar objetos por color), y recordatorio de la excursión al parque. Extensión: media página.\"</p><h4>Mini-reto práctico</h4><p>Pide a la IA que te dé 3 versiones de un mensaje para recordar una reunión de familias (una formal, una breve, una cálida). Elige una, adáptala y envíala.</p>",
                            'duracion_min'    => 13,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => 'Traducción y accesibilidad lingüística',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Comunicación sin barreras</h4><p>En muchas escuelas hay familias que hablan otros idiomas o tienen dificultades de comprensión lectora. La IA puede traducir al instante o simplificar el lenguaje.</p><h4>Herramientas</h4><ul><li><strong>Google Translate / DeepL:</strong> Traducción de textos completos.</li><li><strong>ChatGPT:</strong> Puede traducir y también adaptar el nivel de lenguaje (ej: lenguaje fácil de entender para adultos con baja alfabetización).</li><li><strong>Read&Write:</strong> Extensión para leer textos en voz alta.</li></ul><h4>Ejemplo en Primaria</h4><p>Prompt: \"Traduce el siguiente comunicado al quechua (o aimara, o inglés, según tu contexto) y también genera una versión en lenguaje sencillo (para adultos con poca escolaridad): [texto del comunicado].\"</p><h4>Mini-reto práctico</h4><p>Selecciona un comunicado que ya hayas enviado. Pide a la IA que lo traduzca a otro idioma presente en tu comunidad y que lo simplifique. Guarda ambas versiones para futuras comunicaciones.</p>",
                            'duracion_min'    => 11,
                            'orden'           => 2,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cómo puede ayudar la IA en la comunicación con familias de diversos idiomas?',
                            'opciones'           => ['Traduciendo los mensajes a diferentes lenguas', 'Creando memes', 'Solo funciona en inglés', 'No es útil para eso'],
                            'respuesta_correcta' => 'Traduciendo los mensajes a diferentes lenguas',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                    ],
                ],
                [
                    'nombre'      => 'Integración Curricular y Proyectos',
                    'descripcion' => 'Diseño de proyectos interdisciplinarios y secuencias completas con apoyo de IA.',
                    'orden'       => 2,
                    'lecciones' => [
                        [
                            'nombre'          => 'Diseño de proyectos con IA',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Proyectos que integran saberes</h4><p>Los proyectos interdisciplinarios son potentes pero complejos de diseñar. La IA puede sugerir conexiones entre áreas y actividades secuenciadas.</p><h4>Prompt para proyectos</h4><ul><li><strong>Tema central:</strong> ¿Sobre qué es el proyecto? (ej: \"Los insectos\", \"La comunidad\", \"El agua\").</li><li><strong>Duración:</strong> ¿Cuántas semanas?</li><li><strong>Áreas a integrar:</strong> Comunicación, Matemática, Ciencia, Arte, etc.</li><li><strong>Producto final:</strong> ¿Una maqueta, una exposición, un libro?</li></ul><h4>Ejemplo en Primaria (4to grado)</h4><p>Prompt: \"Diseña un proyecto interdisciplinario de 3 semanas sobre 'La alimentación saludable'. Integra: Matemática (cálculo de porciones), Comunicación (lectura de etiquetas y escritura de recetas), Ciencia (nutrientes), y Arte (dibujo de platos saludables). Sugiere 2 actividades por semana y un producto final: una feria de comidas saludables en el aula. Incluye los materiales necesarios.\"</p><h4>Mini-reto práctico</h4><p>Elige un tema de tu currículo y genera un proyecto de 2 semanas. Revisa que las actividades sean coherentes y factibles en tu contexto. Ajusta lo necesario.</p>",
                            'duracion_min'    => 15,
                            'orden'           => 1,
                        ],
                        [
                            'nombre'          => 'Evaluación y reflexión con IA',
                            'tipo'            => 'texto',
                            'contenido_texto' => "<h4>Evaluar proyectos integrados</h4><p>Evaluar un proyecto que abarca varias áreas es complejo. La IA puede ayudarte a diseñar instrumentos como listas de cotejo, escalas de valoración o preguntas de reflexión para los alumnos.</p><h4>Autoevaluación y coevaluación</h4><p>La IA puede generar preguntas para que los alumnos reflexionen sobre su proceso, su trabajo en equipo y sus aprendizajes.</p><h4>Ejemplo en Inicial</h4><p>Prompt: \"Para niños de 5 años que finalizaron un proyecto sobre los insectos, genera 3 preguntas sencillas de reflexión para hacerles en asamblea. Ejemplo: ¿qué insecto les gustó más?, ¿qué fue lo más difícil de hacer?, ¿qué aprendieron?\"</p><h4>Mini-reto práctico</h4><p>Al finalizar un proyecto, genera con IA un conjunto de preguntas de reflexión para tus alumnos. Ajusta el lenguaje y aplícalas en una asamblea o conversación grupal.</p>",
                            'duracion_min'    => 12,
                            'orden'           => 2,
                        ],
                    ],
                    'ejercicios' => [
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Qué aspecto NO es necesario incluir en un prompt para diseñar un proyecto interdisciplinario?',
                            'opciones'           => ['Las áreas a integrar', 'El nombre del director del colegio', 'El producto final esperado', 'La duración del proyecto'],
                            'respuesta_correcta' => 'El nombre del director del colegio',
                            'puntaje_max'        => 10,
                            'orden'              => 1,
                        ],
                        [
                            'tipo'               => 'multiple',
                            'pregunta'           => '¿Cómo puede la IA apoyar la evaluación de un proyecto?',
                            'opciones'           => ['Calificando automáticamente sin revisión', 'Generando instrumentos de evaluación y preguntas de reflexión', 'Sustituyendo las observaciones del docente', 'Solo puede generar exámenes escritos'],
                            'respuesta_correcta' => 'Generando instrumentos de evaluación y preguntas de reflexión',
                            'puntaje_max'        => 10,
                            'orden'              => 2,
                        ],
                    ],
                ],
            ],
        ],
    ];
}
