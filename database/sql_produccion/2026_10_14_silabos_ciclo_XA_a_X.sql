-- Pasa los sílabos de los cursos del ciclo especial "X - A" (Programa Inicial, solo 2025)
-- a su curso equivalente del Ciclo "X" del mismo programa, SOLO si el nombre del curso
-- es idéntico. Así el docente del Ciclo X puede "Reusar" ese sílabo desde su dashboard.
-- Unidades, enfoques y rúbricas cuelgan del sílabo (silabo_id), no hace falta moverlos.
-- En local solo aplicó a "Escuela, Familia y Comunidad" (curso 199 -> 57, sílabo 73, 2025-I).
SET NAMES utf8mb4;

-- 1) VISTA PREVIA: qué se va a mover (revisar antes del paso 2)
SELECT s.id AS silabo_id, s.periodo, ca.id AS curso_origen, cx.id AS curso_destino, ca.nombre
FROM silabos s
JOIN cursos ca ON ca.id = s.curso_id
JOIN ciclos cia ON cia.id = ca.ciclo_id AND cia.nombre = 'X - A'
JOIN ciclos cix ON cix.programa_id = cia.programa_id AND cix.nombre = 'X'
JOIN cursos cx ON cx.ciclo_id = cix.id AND cx.nombre = ca.nombre;

-- 2) MOVER
UPDATE silabos s
JOIN cursos ca ON ca.id = s.curso_id
JOIN ciclos cia ON cia.id = ca.ciclo_id AND cia.nombre = 'X - A'
JOIN ciclos cix ON cix.programa_id = cia.programa_id AND cix.nombre = 'X'
JOIN cursos cx ON cx.ciclo_id = cix.id AND cx.nombre = ca.nombre
SET s.curso_id = cx.id;
