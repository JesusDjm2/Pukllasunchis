-- Cursos pendientes (users.pendiente): quienes tienen escrito "No", "Ninguno", "-"…
-- figuraban con "¿Cursos pendientes? Sí" y la lista "No". Se dejan SIN cursos pendientes.
-- Los textos con cursos reales NO se tocan: al editar a cada alumno, el formulario nuevo
-- los reconoce (o los muestra como "texto anterior") y se guardan elegidos del sistema.
-- (12/10/2026)

-- 1) VER antes de cambiar: valores actuales y cuántos usuarios tienen cada uno.
SELECT TRIM(pendiente) AS valor, COUNT(*) AS usuarios
FROM users
WHERE pendiente IS NOT NULL
GROUP BY TRIM(pendiente)
ORDER BY usuarios DESC;

-- 2) Cuántos se van a limpiar (debe coincidir con las filas "No", "Ninguno", "-"… de arriba).
SELECT COUNT(*) AS se_limpiaran
FROM users
WHERE pendiente IS NOT NULL
  AND LOWER(TRIM(TRAILING '.' FROM TRIM(pendiente))) IN ('', 'no', 'ninguno', 'ninguna', '-', '--', 'n/a', 'na', 'sin curso', 'sin cursos', 'sin cursos pendientes');

-- 3) LIMPIAR.
UPDATE users
SET pendiente = NULL
WHERE pendiente IS NOT NULL
  AND LOWER(TRIM(TRAILING '.' FROM TRIM(pendiente))) IN ('', 'no', 'ninguno', 'ninguna', '-', '--', 'n/a', 'na', 'sin curso', 'sin cursos', 'sin cursos pendientes');

-- 4) Comprobar: ya no debe quedar ninguno de esos valores.
SELECT TRIM(pendiente) AS valor, COUNT(*) AS usuarios
FROM users
WHERE pendiente IS NOT NULL
GROUP BY TRIM(pendiente)
ORDER BY usuarios DESC;
