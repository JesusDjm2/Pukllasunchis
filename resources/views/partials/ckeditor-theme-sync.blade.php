<script>
    /**
     * El área editable de CKEditor 4 vive en un <iframe> con su propio
     * documento: el CSS de darkmode.css no puede alcanzarla. Este script
     * inyecta un <style> dentro de cada iframe con los colores del tema
     * activo (claro/oscuro/tenue) y se re-ejecuta cada vez que cambia la
     * clase dark-mode/dim-mode del body (togglear el tema, o abrir un
     * editor nuevo después de togglear).
     */
    (function () {
        function themeCss() {
            var body = document.body;
            if (body.classList.contains('dark-mode')) {
                return 'html,body{background:#0d1117;color:#c9d1d9;margin:8px;}' +
                    'a{color:#58a6ff;} ::selection{background:#264f78;}';
            }
            if (body.classList.contains('dim-mode')) {
                return 'html,body{background:#2f2230;color:#f6e9f1;margin:8px;}' +
                    'a{color:#f49ac1;} ::selection{background:#5e3a55;}';
            }
            return 'html,body{background:#fff;color:#1f2328;margin:8px;}';
        }

        function applyToEditor(editor) {
            try {
                var doc = editor.document;
                if (!doc) return;
                var css = themeCss();
                var existing = doc.getById('puklla-theme-style');
                if (existing) {
                    existing.setHtml(css);
                } else {
                    var style = new CKEDITOR.dom.element('style', doc);
                    style.setAttribute('id', 'puklla-theme-style');
                    style.setHtml(css);
                    doc.getHead().append(style);
                }
            } catch (e) {}
        }

        function applyToAll() {
            if (typeof CKEDITOR === 'undefined') return;
            for (var name in CKEDITOR.instances) {
                var ed = CKEDITOR.instances[name];
                if (ed.status === 'ready') applyToEditor(ed);
            }
        }

        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.on('instanceReady', function (evt) {
                applyToEditor(evt.editor);
            });
        }

        if (document.body) {
            new MutationObserver(applyToAll).observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });
        }
    })();
</script>
