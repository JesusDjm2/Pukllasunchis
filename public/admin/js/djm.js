document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    var searchButton = document.getElementById('searchButton');
    var searchForm = document.getElementById('searchForm');
    if (!searchInput || !searchForm) {
        return;
    }
    var debounceTimer;

    function performSearch() {
        // Si existe una función global de filtro client-side, usarla sin recargar
        if (typeof window.filtrarAlumnosFid === 'function') {
            window.filtrarAlumnosFid(searchInput.value);
            return;
        }
        if (typeof window.filtrarAlumnosPpd === 'function') {
            window.filtrarAlumnosPpd(searchInput.value);
            return;
        }
        // Fallback: enviar formulario (recarga de página para vistas sin filtro client-side)
        searchForm.submit();
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(performSearch, 300);

        if (searchButton) {
            searchButton.disabled = !searchInput.value.trim();
        }
    });
});
$(document).ready(function () {
    $('table.table-sortable thead th').click(function () {
        var table = $(this).closest('table');
        var column = $(this).index();
        var rows = table.find('tbody tr').toArray().sort(comparer(column));
        this.asc = !this.asc;

        $(this).find('span').toggleClass('fa-caret-up fa-caret-down');

        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.find('tbody').append(rows[i]);
        }
    });

    function comparer(index) {
        return function (a, b) {
            var valA = getCellValue(a, index),
                valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
        };
    }

    function getCellValue(row, index) {
        return $(row).children('td').eq(index).text();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    if (!searchInput) {
        return;
    }
    var storedValue = sessionStorage.getItem('searchValue');

    if (storedValue) {
        searchInput.value = storedValue;
    }

    searchInput.addEventListener('input', function () {
        sessionStorage.setItem('searchValue', this.value);
    });

    searchInput.focus();
    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
});

$(document).ready(function () {
    $('.arrow-icon').on('click', function () {
        $(this).toggleClass('down');
    });
});

/* ── Drawer móvil: cerrar al tocar el backdrop ──────────────────────────────
   SB Admin 2 ya gestiona abrir/cerrar con el hamburguesa (#sidebarToggleTop).
   Este listener cierra el drawer cuando el usuario toca fuera del sidebar
   (es decir, toca el ::before backdrop que cubre el content-wrapper). ── */
(function () {
    function isMobile() { return window.innerWidth < 768; }

    document.addEventListener('click', function (e) {
        if (!isMobile()) return;
        if (!document.body.classList.contains('sidebar-toggled')) return;

        var sidebar  = document.getElementById('accordionSidebar');
        var hamburger = document.getElementById('sidebarToggleTop');
        if (!sidebar) return;

        /* Si el clic fue dentro del sidebar o en el hamburguesa, no cerrar */
        if (sidebar.contains(e.target) || (hamburger && hamburger.contains(e.target))) return;

        /* Simular clic en el hamburguesa para que SB Admin 2 limpie sus clases */
        if (hamburger) hamburger.click();
    });

    /* Cerrar con tecla Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !isMobile()) return;
        if (!document.body.classList.contains('sidebar-toggled')) return;
        var hamburger = document.getElementById('sidebarToggleTop');
        if (hamburger) hamburger.click();
    });
}());

