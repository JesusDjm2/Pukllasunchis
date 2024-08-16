
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    var searchButton = document.getElementById('searchButton');
    var debounceTimer;

    function performSearch() {
        document.getElementById('searchForm').submit();
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(performSearch, 300);

        searchButton.disabled = !searchInput.value.trim();
    });
});
$(document).ready(function () {
    $('th').click(function () {
        var table = $(this).parents('table').eq(0);
        var column = $(this).index(); // Obtén el índice de la columna clicada
        var rows = table.find('tr:gt(0)').toArray().sort(comparer(column));
        this.asc = !this.asc;

        // Cambia la clase de la flecha en la columna clicada
        $(this).find('span').toggleClass('fa-caret-up fa-caret-down');

        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
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