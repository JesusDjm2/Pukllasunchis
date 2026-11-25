/**
 * Lista de compañeros para grupos de Investigaciones (crear grupo, editar grupo y gestión del Super Admin).
 * Recibe [{id, nombre, programa}] (sin DNI: protección de datos) ya ordenados por el servidor (programa del curso primero y
 * alfabético dentro de cada programa) y pinta un buscador + un acordeón por programa con casillas
 * integrantes[]. Estilos en public/css/pk-taqe.css (.tq-comp-*).
 *
 *   TaqeCompaneros.render(contenedor, companeros, { vacio: 'texto si no hay nadie' });
 */
(function (window) {
    'use strict';

    var escapar = function (s) {
        return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    };

    // Sin tildes ni mayúsculas: «nuñez» encuentra «Núñez».
    var normalizar = function (s) {
        return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    };

    function render(contenedor, companeros, opciones) {
        opciones = opciones || {};
        if (!contenedor) return;

        if (!companeros || !companeros.length) {
            contenedor.innerHTML = '<span class="tq-nota">' + escapar(opciones.vacio || 'No hay compañeros disponibles.') + '</span>';
            return;
        }

        // Agrupar respetando el orden en que llegan.
        var grupos = [];
        var indice = {};
        companeros.forEach(function (c) {
            var programa = c.programa || 'Sin programa';
            if (!(programa in indice)) {
                indice[programa] = grupos.length;
                grupos.push({ programa: programa, alumnos: [] });
            }
            grupos[indice[programa]].alumnos.push(c);
        });

        var html = '<div class="tq-comp-buscar"><i class="fas fa-search"></i>'
            + '<input type="search" class="tq-comp-input" placeholder="Buscar por nombre o apellido…" autocomplete="off"></div>'
            + '<div class="tq-comp-grupos">';

        grupos.forEach(function (g) {
            html += '<details class="tq-comp-grupo">' // todos plegados al inicio; el buscador abre los que coinciden
                + '<summary><i class="fas fa-chevron-right tq-comp-flecha"></i>'
                + '<span class="tq-comp-programa">' + escapar(g.programa) + '</span>'
                + '<span class="tq-comp-sel" hidden></span>'
                + '<span class="tq-comp-total">' + g.alumnos.length + '</span></summary>'
                + '<div class="tq-comp-items">';
            g.alumnos.forEach(function (c) {
                html += '<label class="tq-comp-item" data-busqueda="' + escapar(normalizar(c.nombre)) + '">'
                    + '<input type="checkbox" name="integrantes[]" value="' + parseInt(c.id, 10) + '">'
                    + '<span class="tq-comp-nombre">' + escapar(c.nombre) + '</span>'
                    + '</label>';
            });
            html += '</div></details>';
        });
        html += '</div><p class="tq-comp-sin" hidden>Ningún compañero coincide con la búsqueda.</p>';

        contenedor.innerHTML = html;
        contenedor.classList.add('tq-comp');

        var input = contenedor.querySelector('.tq-comp-input');
        var bloques = Array.prototype.slice.call(contenedor.querySelectorAll('.tq-comp-grupo'));
        var sinResultados = contenedor.querySelector('.tq-comp-sin');
        var abiertosAntes = null;

        var contarSeleccion = function () {
            bloques.forEach(function (b) {
                var n = b.querySelectorAll('input[type=checkbox]:checked').length;
                var badge = b.querySelector('.tq-comp-sel');
                badge.hidden = n === 0;
                badge.textContent = n + (n === 1 ? ' elegido' : ' elegidos');
            });
        };

        var filtrar = function () {
            var q = normalizar(input.value.trim());
            if (q && abiertosAntes === null) {
                abiertosAntes = bloques.map(function (b) { return b.open; });
            }
            var hayAlguno = false;
            bloques.forEach(function (b, i) {
                var visibles = 0;
                b.querySelectorAll('.tq-comp-item').forEach(function (item) {
                    var ok = !q || item.getAttribute('data-busqueda').indexOf(q) !== -1;
                    item.hidden = !ok;
                    if (ok) visibles++;
                });
                b.hidden = visibles === 0;
                if (q) {
                    b.open = visibles > 0;
                } else if (abiertosAntes) {
                    b.open = abiertosAntes[i];
                }
                if (visibles) hayAlguno = true;
            });
            if (!q) abiertosAntes = null;
            sinResultados.hidden = hayAlguno;
        };

        input.addEventListener('input', filtrar);
        // Enter en el buscador no debe enviar el formulario del grupo.
        input.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
        contenedor.addEventListener('change', function (e) {
            if (e.target && e.target.type === 'checkbox') contarSeleccion();
        });
    }

    window.TaqeCompaneros = { render: render };
})(window);
