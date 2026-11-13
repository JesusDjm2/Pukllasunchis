# Red Puklla de Egresados · implementación comunitaria

Código para integrar en `feature/modulo-seguimiento`, sobre los paneles de la entrega anterior. Se conserva Ayllu Puklla como comunidad institucional y una sola identidad `users`.

## Incorporado

- Enlace **Red Puklla de Egresados** en el menú izquierdo de egresados FID/PPD, docentes en general, coordinación y administración. Se mantienen las otras funciones institucionales. La entrada comunitaria no concede acceso a expedientes ni contactos privados.
- Inicio, Comunidad, Oportunidades y Mi espacio. Inicio reúne conversaciones existentes; no crea otro muro con copias de sus publicaciones.
- Categorías Yachay, Yuyay, Ruray, Munay, Rimay y Pukllay con explicación visible, separadas de la intención de la publicación: experiencia, pregunta, colaboración, ayuda o encuentro. Los formatos no añaden categorías redundantes.
- Conversaciones y participación con aportes concretos. Sin likes, puntos ni rankings. Los estados de participación se adaptan a la intención; las colaboraciones realizadas se presentan como declaración de su autor.
- Ayllus por interés o promoción, abiertos dentro de la Red o privados con solicitud y aceptación. Facilitar un ayllu no concede administración. La promoción indica su foco, sin restringir automáticamente quién puede participar.
- Ayllus con actividad reciente, recursos compartidos y colaboración de integrantes; chat del grupo y encuentros del Calendario Vivo. Solicitantes pendientes no ven integrantes, publicaciones, chat ni encuentros privados. Nombre, propósito y cantidad de integrantes permiten descubrir el grupo y solicitar ingreso.
- Compartir enlaces canónicos y difundir dentro de ayllus mediante referencias. Se conserva la publicación original, su autoría y conversación. No se permite sacar contenidos de un ayllu privado fuera de su audiencia.
- Oportunidades: trabajo, formación continua y convocatorias, diferenciando quién ofrece y quién comparte una fuente encontrada. Institución, descripción/requisitos, lugar/modalidad, plazo y contacto autorizado. El autor puede actualizar/cerrar su oportunidad.
- Guardados privados con estados: guardado, me interesa, contacté, postulé y finalizada. Abrir un contacto no se registra como postulación enviada. La colaboración permanece en Comunidad.
- Mi espacio: guardados, publicaciones propias, avisos, progreso y acceso al formulario privado del egresado, solicitudes, trámites y perfil/portafolio comunitario existente. Se conserva la confirmación personal inicial antes de acceder a la Red.
- Reportar, ocultar, retirar y restaurar con motivo y trazabilidad. El autor consulta las decisiones sobre su publicación. No se borra físicamente el contenido al retirarlo. El autor puede editar texto/enlace y retirar su publicación visible; no puede restaurar por sí mismo una intervención de moderación ni cambiar su audiencia/intención para reinterpretar aportes.

## Reutilización y privacidad

Se reutilizan los modelos y tablas Ayllu, la bolsa existente `bolsa_trabajo_ofertas`, usuarios/roles Spatie y las notificaciones Laravel. No hay otra base de usuarios, otra bolsa ni una segunda administración de privilegios.

Los nuevos registros tienen `red_egresados=true`. El trait `AmbitoRedPuklla` mantiene las consultas históricas fuera de ese ámbito; `deRedPuklla()` retira únicamente ese scope y exige el ámbito Red. Así se evita que contenido interno aparezca en la bolsa pública, en el muro estudiantil o en las rutas antiguas de reacciones. Las publicaciones históricas no se copian ni reclasifican automáticamente, ya que pueden tener otras audiencias. No se generan publicaciones o miembros ficticios.

Moderación utiliza el permiso existente `ayllu.moderar` o las funciones de gestión de seguimiento ya autorizadas. El docente general puede participar sin obtener esos permisos. La moderación puede revisar contenido privado para su función, pero el chat exige membresía aceptada. Los datos personales de seguimiento siguen protegidos por los servicios anteriores.

FID/PPD se resuelven mediante evidencia institucional con `GrupoFormacionService`, no por nombres/correos. Se muestran etiquetas y filtros FID, PPD, ambos o por verificar; TODOS incluye docentes y personal. No hay rankings por procedencia ni privilegios distintos.

La migración `2026_11_13_000003_extend_ayllu_red_puklla.php` añade columnas de ámbito, privacidad, intención, oportunidad y preferencias. Crea cinco tablas auxiliares: participaciones, guardados, referencias de difusión, auditoría de moderación y preferencias. No duplican los cuerpos de publicaciones/ofertas. Es aditiva; `down()` conserva datos e historial. Aplicar migraciones antes de activar el código, pues los scopes requieren las columnas nuevas.

## Enlaces, avisos y límites

Solo enlaces HTTP/HTTPS validados, sin HTML suministrado por el usuario ni solicitudes de metadatos a URLs arbitrarias. YouTube usa el reproductor de privacidad mejorada; Instagram, su script oficial. Ambos se cargan tras una acción explícita y mantienen acceso a la fuente original. La reproducción/inserción depende del proveedor y del autor; historias y otras fuentes quedan como enlaces. No se garantiza la reproducción de todos los contenidos externos.

Los avisos reutilizan la tabla y campanita institucionales. Hay preferencias globales y por ayllu, avisos de conversaciones/encuentros y actualizaciones de oportunidades guardadas. Esta función solo escribe avisos internos, sin correo ni mensajería externa. Los avisos privados no incluyen el contenido sensible y al abrirse se vuelve a comprobar el acceso.

El chat muestra los últimos 60 mensajes, con actualización manual; no es chat en tiempo real. No se ha añadido mensajería individual ni otro sistema de carga de archivos; los recursos externos se comparten por enlace. Los perfiles y portafolios se enlazan a los módulos existentes.

## Validación

101 pruebas aisladas ejecutadas: 84 casos PHP 8.2 WebAssembly y 17 pruebas Node/jsdom. Incluyen clasificación, permisos, primera visita, enlaces, ayllus privados, separación de ámbitos, formulario, carga externa voluntaria y copia de enlaces. Sintaxis de 43 archivos PHP comprobada y JavaScript nuevo validado con Node.

Estas pruebas sustituyen los repositorios en las comprobaciones de permisos; no ejecutan SQL ni Laravel completo. **No se han ejecutado Blade, migraciones MySQL ni pruebas funcionales de extremo a extremo en este entorno.** El ensayo completo y la revisión visual deben realizarse en Antigravity antes de activar.

No hay despliegue ni escritura remota a GitHub de esta entrega. El paquete contiene código y parches para integración sobre la rama indicada.
