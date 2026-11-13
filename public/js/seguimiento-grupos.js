/* Datos institucionales de la población ya autorizada y filtrada por el servidor. */
(function (root) {
    'use strict';
    const api = {
        crearPadron(datos) {
            if (!Array.isArray(datos)) return [];
            const unique = new Map();
            for (const p of datos) {
                if (!p || !p.id || unique.has(p.id)) continue;
                unique.set(p.id, {...p, grupos_formacion: Array.isArray(p.grupos_formacion) ? [...new Set(p.grupos_formacion.filter(g => g === 'FID' || g === 'PPD'))] : [], situacion: p.estado_academico || p.situacion || 'Por verificar', fuente: p.origen || 'Registro institucional'});
            }
            return [...unique.values()];
        },
    };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.PukllaGrupos = api;
})(typeof window !== 'undefined' ? window : globalThis);
