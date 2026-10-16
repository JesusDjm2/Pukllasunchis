/**
 * Sistema de alertas propio de Puklla2026 — envuelve SweetAlert2 (ya cargado
 * por el layout) con el diseño de marca de cada rol y expone una API sencilla
 * en `window.PkAlert`, para no repetir la misma configuración de Swal.fire()
 * en cada vista ni seguir usando confirm()/alert() nativos del navegador.
 *
 * Marca: el color sale del acento del rol en public/css/pk-tokens.css (--pk-acento, según
 * body.pk-rol-*). Un layout puede forzar otro con `window.PK_BRAND_COLOR`; si no hay ninguno,
 * se usa un azul neutro.
 *
 * Migración sin romper nada: los <form> existentes con
 * onsubmit="return confirm(...)" siguen funcionando igual — este archivo NO
 * los toca. Para adoptar el nuevo diseño en un formulario, se reemplaza ese
 * atributo por data-confirm="mensaje" (y data-confirm-danger para el estilo
 * de "acción destructiva"); este script intercepta el submit solo.
 */
(function (window, document) {
    'use strict';

    if (typeof window.Swal === 'undefined') {
        // SweetAlert2 no está cargado en este layout — no hay nada que envolver.
        return;
    }

    // El color de marca pasa a una variable CSS: botones, foco e ícono se dibujan en
    // puklla-alert.css con los tokens del sistema, no con los estilos propios de Swal.
    // Solo si el layout lo fuerza: si no, puklla-alert.css usa --pk-acento de los tokens del rol.
    if (window.PK_BRAND_COLOR) {
        document.documentElement.style.setProperty('--pk-brand', window.PK_BRAND_COLOR);
    }

    // Ícono Font Awesome sobrio en lugar del ícono animado grande de SweetAlert.
    var ICONOS = { warning: 'fa-exclamation', error: 'fa-times', success: 'fa-check', info: 'fa-info', question: 'fa-question' };

    function clases(peligro) {
        return {
            popup: 'pk-swal-popup' + (peligro ? ' is-danger' : ''),
            icon: 'pk-swal-icon',
            actions: 'pk-swal-actions',
            confirmButton: 'pk-swal-btn pk-swal-btn-confirm' + (peligro ? ' is-danger' : ''),
            cancelButton: 'pk-swal-btn pk-swal-btn-cancel',
        };
    }

    var Swal = window.Swal.mixin({
        reverseButtons: true,
        buttonsStyling: false,
        showClass: { popup: 'pk-swal-in', backdrop: 'swal2-backdrop-show', icon: '' },
        hideClass: { popup: 'pk-swal-out', backdrop: 'swal2-backdrop-hide', icon: '' },
        customClass: clases(false),
    });

    var Base = {
        fire: function (config) {
            if (config.icon && ICONOS[config.icon] && !config.iconHtml) {
                config.iconHtml = '<i class="fas ' + ICONOS[config.icon] + '"></i>';
            }
            return Swal.fire(config);
        },
    };

    function aOpciones(valor) {
        return typeof valor === 'string' ? { text: valor } : (valor || {});
    }

    /** Confirmación genérica (reemplazo directo de `confirm()`). Devuelve una Promise<boolean>. */
    function confirmar(opciones) {
        var config = aOpciones(opciones);
        return Base.fire(Object.assign({
            icon: 'warning',
            title: '¿Estás seguro?',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
        }, config)).then(function (r) { return r.isConfirmed; });
    }

    /** Confirmación para acciones destructivas (eliminar, retirar, etc.) — botón en rojo. */
    function confirmarEliminar(opciones) {
        var config = aOpciones(opciones);
        return confirmar(Object.assign({
            title: 'Esta acción no se puede deshacer',
            confirmButtonText: 'Sí, eliminar',
        }, config, { customClass: clases(true) }));
    }

    function exito(mensaje, titulo) {
        return Base.fire({ icon: 'success', title: titulo || '¡Listo!', text: mensaje, timer: 2800, timerProgressBar: true, showConfirmButton: false });
    }

    function error(mensaje, titulo) {
        return Base.fire({ icon: 'error', title: titulo || 'Ocurrió un problema', text: mensaje });
    }

    function aviso(mensaje, titulo) {
        return Base.fire({ icon: 'info', title: titulo || 'Aviso', text: mensaje });
    }

    /** Notificación pequeña, esquina superior derecha, se cierra sola. */
    function toast(mensaje, icono) {
        return Base.fire({
            toast: true,
            position: 'top-end',
            icon: icono || 'success',
            title: mensaje,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
    }

    // Intercepta el submit de cualquier <form data-confirm="mensaje">: evita
    // migrar JS view por view, basta con cambiar el atributo en el blade.
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        var mensaje = form.getAttribute('data-confirm');
        if (!mensaje || form.dataset.pkConfirmado === 'true') {
            return;
        }

        evento.preventDefault();
        // El botón que se presionó: form.submit() no lo envía, y algunos formularios
        // tienen varios (name="tipo" value="recordatorio" | "urgente").
        var boton = evento.submitter;
        var esDestructiva = form.hasAttribute('data-confirm-danger');
        // Opcionales: data-confirm-titulo y data-confirm-boton (texto del botón de aceptar).
        var opciones = { text: mensaje };
        if (form.dataset.confirmTitulo) {
            opciones.title = form.dataset.confirmTitulo;
        }
        if (form.dataset.confirmBoton) {
            opciones.confirmButtonText = form.dataset.confirmBoton;
        }
        var promesa = esDestructiva ? confirmarEliminar(opciones) : confirmar(opciones);

        promesa.then(function (confirmado) {
            if (confirmado) {
                form.dataset.pkConfirmado = 'true';
                if (boton && boton.name) {
                    var oculto = document.createElement('input');
                    oculto.type = 'hidden';
                    oculto.name = boton.name;
                    oculto.value = boton.value;
                    form.appendChild(oculto);
                }
                form.submit();
            }
        });
    });

    window.PkAlert = {
        confirmar: confirmar,
        confirmarEliminar: confirmarEliminar,
        exito: exito,
        error: error,
        aviso: aviso,
        toast: toast,
    };
})(window, document);
