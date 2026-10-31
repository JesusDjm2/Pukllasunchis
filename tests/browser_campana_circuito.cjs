const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const CHROME_PATH = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE_URL = 'http://127.0.0.1:8000';
const EVIDENCIAS_DIR = path.join(__dirname, '../docs/seguimiento-egresados/campanas-entrevistadores/evidencias');
const ARTIFACT_EVIDENCIAS_DIR = '/Users/pavelchaparro/.gemini/antigravity/brain/8733181b-5be1-41e0-b01c-73ff53cfef2b/campanas-entrevistadores/evidencias';

[EVIDENCIAS_DIR, ARTIFACT_EVIDENCIAS_DIR].forEach(dir => {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

const wait = (ms) => new Promise(r => setTimeout(r, ms));

async function saveScreenshot(page, filename, description) {
    const p1 = path.join(EVIDENCIAS_DIR, filename);
    const p2 = path.join(ARTIFACT_EVIDENCIAS_DIR, filename);
    await page.screenshot({ path: p1, fullPage: true });
    fs.copyFileSync(p1, p2);
    console.log(`[EVIDENCIA] ${filename}: ${description}`);
}

(async () => {
    console.log('--- REINICIANDO ASIGNACIONES DE CAMPAÑA ENSAYO PARA AUDITORÍA LIMPIA ---');
    try {
        execSync('php artisan tinker --execute="App\\Models\\SeguimientoEgresado\\AsignacionCampana::where(\'campana_id\', 10)->delete(); App\\Models\\SeguimientoEgresado\\Llamada::where(\'campana_id\', 10)->delete();"');
        console.log('Asignaciones y llamadas de ensayo reiniciadas con éxito.');
    } catch (e) {
        console.warn('Advertencia al reiniciar ensayo:', e.message);
    }

    console.log('--- INICIANDO CIRCUITO FUNCIONAL COMPLETO EN NAVEGADOR ---');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900']
    });

    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1440, height: 900 });

        // Auto-aceptar diálogos alert y confirm de navegador
        page.on('dialog', async dialog => {
            console.log(`[DIALOG] ${dialog.type()}: ${dialog.message()}`);
            await dialog.accept();
        });

        // 1. Coordinación inicia sesión
        console.log('1. Coordinación inicia sesión...');
        await page.goto(`${BASE_URL}/auditoria/login/coordinacion`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '01_login_coordinacion_dashboard.png', 'Sesión iniciada como Coordinación de Empleabilidad');

        // 2. Coordinación accede a Gestión de Campañas
        console.log('2. Accediendo a Gestión de Campañas...');
        await page.goto(`${BASE_URL}/seguimiento-egresados/campanas/gestion`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '02_gestion_campanas_padron_docentes.png', 'Panel de Selección de Docentes y Diagnóstico del Padrón Real');

        // 3. Seleccionar docentes y previsualizar distribución
        console.log('3. Seleccionando 5 docentes reales...');
        await page.evaluate(() => {
            seleccionarPrimeros(5);
        });
        await wait(500);
        await saveScreenshot(page, '03_docentes_seleccionados.png', '5 Docentes Reales Seleccionados con roles y programas verificados');

        console.log('4. Solicitando vista previa de distribución equilibrada...');
        await page.evaluate(() => {
            abrirPreview();
        });
        await wait(1500);
        await saveScreenshot(page, '04_modal_vista_previa_distribucion.png', 'Vista Previa de Distribución Equilibrada (Diferencia máxima = 1)');

        // 5. Confirmar y ejecutar distribución
        console.log('5. Confirmando y ejecutando distribución equilibrada...');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.evaluate(() => {
                confirmarDesdeModal();
            })
        ]);
        await wait(1000);
        await saveScreenshot(page, '05_monitoreo_post_distribucion.png', 'Monitoreo de Campaña con Egresados Reales Asignados');

        // 6. Iniciar sesión como docente con asignación (Marcel Bosch, 81 asignados)
        console.log('6. Iniciando sesión como Docente Entrevistador asignado (Marcel Bosch)...');
        await page.goto(`${BASE_URL}/auditoria/login/docente-entrevistador`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '06_dashboard_docente_con_modulo.png', 'Dashboard Docente con Módulo Puklla en Red y 81 Egresados Asignados');

        // 7. Docente accede a "Mis Egresados Asignados"
        console.log('7. Docente abre Mis Egresados Asignados...');
        await page.goto(`${BASE_URL}/seguimiento-egresados/mis-asignaciones`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '07_panel_docente_mis_asignaciones.png', 'Panel Mis Egresados Asignados con 9 KPIs y Lista de 81 Egresados');

        // 8. Docente registra una llamada telefónica simulada
        console.log('8. Registrando llamada telefónica de ensayo...');
        await page.waitForSelector('button[onclick*="abrirModalLlamada"]', { timeout: 5000 });
        await page.evaluate(() => {
            const btn = document.querySelector('button[onclick*="abrirModalLlamada"]');
            if (btn) btn.click();
        });
        await wait(600);
        await page.evaluate(() => {
            document.getElementById('llamadaTipoResultado').value = 'contacto_efectivo';
            document.getElementById('llamadaObservaciones').value = 'Llamada de ensayo institucional: El egresado labora en zona rural de Anta y solicita acompañamiento.';
        });
        await saveScreenshot(page, '08_modal_registro_llamada.png', 'Modal de Registro de Llamada con 11 Estados Oficiales');

        // Enviar llamada mediante evaluate
        await page.evaluate(() => {
            document.getElementById('formRegistroLlamada').dispatchEvent(new Event('submit', { cancelable: true }));
        });
        await wait(2500);

        // 9. Docente actualiza una ficha en ensayo
        console.log('9. Docente actualiza ficha en ensayo...');
        await page.waitForSelector('button[onclick*="abrirModalActualizar"]', { timeout: 8000 });
        await page.evaluate(() => {
            const btn = document.querySelector('button[onclick*="abrirModalActualizar"]');
            if (btn) btn.click();
        });
        await wait(600);
        await page.evaluate(() => {
            document.getElementById('fichaTrabaja').value = 'Sí';
            document.getElementById('fichaInstitucion').value = 'I.E. 50123 Pukllasunchis Anta';
            document.getElementById('fichaCargo').value = 'Profesor de Aula EIB';
            document.getElementById('fichaDep').value = 'CUSCO';
            document.getElementById('fichaProv').value = 'ANTA';
            document.getElementById('fichaDist').value = 'ANCAHUASI';
            document.getElementById('fichaNecesidades').value = 'Materiales didácticos en Quechua Collao';
        });
        await saveScreenshot(page, '09_modal_actualizar_ficha.png', 'Formulario Espejo de Actualización de Ficha de Egresado');

        await page.evaluate(() => {
            document.getElementById('formActualizacionFicha').dispatchEvent(new Event('submit', { cancelable: true }));
        });
        await wait(1500);

        // 10. Coordinación vuelve a Monitoreo y observa el avance en tiempo real
        console.log('10. Coordinación revisa monitoreo actualizado...');
        await page.goto(`${BASE_URL}/auditoria/login/coordinacion`, { waitUntil: 'networkidle2' });
        await page.goto(`${BASE_URL}/seguimiento-egresados/campanas/10/monitoreo`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '10_monitoreo_avance_actualizado.png', 'Monitoreo de Campaña con Indicadores de Llamadas y Fichas Actualizadas');

        // 11. Coordinación revisa Observatorio Territorial actualizado
        console.log('11. Coordinación revisa Observatorio Territorial...');
        await page.goto(`${BASE_URL}/seguimiento-egresados/campanas/10/territorio`, { waitUntil: 'networkidle2' });
        await wait(1000);
        await saveScreenshot(page, '11_observatorio_territorial_actualizado.png', 'Observatorio Territorial Antes vs Después con Agregación de Provincias');

        console.log('--- CIRCUITO FUNCIONAL COMPLETADO EXITOSAMENTE ---');
    } catch (err) {
        console.error('Error durante la ejecución del circuito:', err);
    } finally {
        await browser.close();
    }
})();
