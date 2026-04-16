
document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    var searchButton = document.getElementById('searchButton');
    var searchForm = document.getElementById('searchForm');
    if (!searchInput || !searchForm) {
        return;
    }
    var debounceTimer;

    function performSearch() {
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

