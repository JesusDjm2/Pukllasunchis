/*
 * Visor de fotos compartido (estilos en pk-foto.css). Cualquier elemento con
 *   data-foto="url"  [data-foto-titulo="Nombre"]  [data-foto-sub="DNI …"]
 * abre la foto en grande en un popup. Cierra con Esc, clic fuera o la ✕.
 * Funciona también con filas que se cargan después (delegación en document).
 */
(function () {
    if (window.PkFoto) return;
    var visor, img, titulo, sub, abrirEn, ultimoFoco;

    function crear() {
        visor = document.createElement('div');
        visor.className = 'pk-foto';
        visor.hidden = true;
        visor.setAttribute('role', 'dialog');
        visor.setAttribute('aria-modal', 'true');
        visor.setAttribute('aria-label', 'Foto');
        visor.innerHTML =
            '<div class="pk-foto__caja">' +
            '  <button type="button" class="pk-foto__cerrar" aria-label="Cerrar">&times;</button>' +
            '  <img class="pk-foto__img" alt="">' +
            '  <div class="pk-foto__pie">' +
            '    <div><div class="pk-foto__titulo"></div><div class="pk-foto__sub"></div></div>' +
            '    <a class="pk-foto__abrir" target="_blank" rel="noopener">Abrir original</a>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(visor);
        img = visor.querySelector('.pk-foto__img');
        titulo = visor.querySelector('.pk-foto__titulo');
        sub = visor.querySelector('.pk-foto__sub');
        abrirEn = visor.querySelector('.pk-foto__abrir');
        visor.addEventListener('click', function (e) {
            if (e.target === visor || e.target.closest('.pk-foto__cerrar')) cerrar();
        });
    }

    function abrir(url, t, s) {
        if (!visor) crear();
        ultimoFoco = document.activeElement;
        img.src = url;
        img.alt = t ? 'Foto de ' + t : 'Foto';
        titulo.textContent = t || '';
        sub.textContent = s || '';
        abrirEn.href = url;
        visor.hidden = false;
        document.body.classList.add('pk-foto-abierto');
        visor.querySelector('.pk-foto__cerrar').focus();
    }

    function cerrar() {
        if (!visor || visor.hidden) return;
        visor.hidden = true;
        img.removeAttribute('src');
        document.body.classList.remove('pk-foto-abierto');
        if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus();
    }

    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-foto]');
        if (!el) return;
        e.preventDefault();
        e.stopPropagation();
        abrir(el.getAttribute('data-foto'), el.getAttribute('data-foto-titulo'), el.getAttribute('data-foto-sub'));
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });

    window.PkFoto = { abrir: abrir, cerrar: cerrar };
})();
