{{-- Ciclo de tema claro → tenue → oscuro (botón #darkModeToggle de la barra superior).
     Lo comparten las intranets de estudiante (FID y PPD). --}}
<script>
(function () {
    var THEME_KEY = 'puklla-theme';
    var body   = document.body;
    var toggle = document.getElementById('darkModeToggle');
    var icon   = document.getElementById('darkModeIcon');
    var CYCLE  = ['light', 'dim', 'dark'];
    var ICONS  = { light: 'fas fa-moon', dim: 'fas fa-adjust', dark: 'fas fa-sun' };
    var TITLES = { light: 'Modo tenue', dim: 'Modo oscuro', dark: 'Modo claro' };

    function getTheme() {
        if (body.classList.contains('dark-mode')) return 'dark';
        if (body.classList.contains('dim-mode'))  return 'dim';
        return 'light';
    }

    function applyTheme(theme) {
        body.classList.remove('dark-mode', 'dim-mode');
        if (theme === 'dark') body.classList.add('dark-mode');
        if (theme === 'dim')  body.classList.add('dim-mode');
        localStorage.setItem(THEME_KEY, theme);
        if (icon)   icon.className = ICONS[theme];
        if (toggle) toggle.setAttribute('title', TITLES[theme]);
    }

    applyTheme(getTheme());

    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = CYCLE[(CYCLE.indexOf(getTheme()) + 1) % CYCLE.length];
            applyTheme(next);
        });
    }
})();
</script>
