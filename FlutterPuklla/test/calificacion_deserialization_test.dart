import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_puklla/features/calificaciones/data/calificacion.dart';

void main() {
  group('Calificacion & CalificacionesResponse deserialization', () {
    test('Calificacion.fromJson maneja creditos y horas como strings o ints defensivamente', () {
      final jsonWithStringCredits = {
        'id': '3127',
        'curso_id': '3',
        'curso_nombre': 'Lectura y Escritura de la Educación Superior',
        'ciclo_nombre': 'I',
        'programa_nombre': 'Programa Inicial',
        'creditos': '3',
        'horas': '4',
        'valoracion_curso': '4.67',
        'calificacion_curso': 'Logrado',
        'calificacion_sistema': '18',
      };

      final c = Calificacion.fromJson(jsonWithStringCredits);
      expect(c.id, 3127);
      expect(c.cursoId, 3);
      expect(c.creditos, 3);
      expect(c.horas, 4);
      expect(c.cursoNombre, 'Lectura y Escritura de la Educación Superior');
      expect(c.calificacionSistema, '18');
    });

    test('CalificacionesResponse.fromJson maneja anteriores como Map o como List vacía', () {
      final jsonWithEmptyListAnteriores = {
        'periodo_actual': {
          'periodo_nombre': '2026-I',
          'p1_visible': true,
          'p2_visible': true,
          'cursos': [],
        },
        'anteriores': [], // lista vacía cuando no hay periodos anteriores
      };

      final resp1 = CalificacionesResponse.fromJson(jsonWithEmptyListAnteriores);
      expect(resp1.periodoActual.periodoNombre, '2026-I');
      expect(resp1.anteriores, isEmpty);

      final jsonWithMapAnteriores = {
        'periodo_actual': {
          'periodo_nombre': '2026-I',
          'p1_visible': true,
          'p2_visible': true,
          'cursos': [
            {
              'curso_id': 14,
              'curso_nombre': 'Arte, Creatividad y Aprendizaje',
              'ciclo_nombre': 'III',
              'programa_nombre': 'Programa Inicial',
              'parcial_1': {
                'valoracion_curso': '4.67',
                'calificacion_curso': 'Logrado',
                'calificacion_sistema': '18',
              },
              'parcial_2': null,
              'promedio': null,
              'observaciones': null,
            }
          ],
        },
        'anteriores': {
          '2025-I': [
            {
              'id': 3127,
              'curso_id': 3,
              'curso_nombre': 'Lectura y Escritura de la Educación Superior',
              'creditos': '3',
              'horas': '4',
              'calificacion_sistema': '18',
            }
          ]
        },
      };

      final resp2 = CalificacionesResponse.fromJson(jsonWithMapAnteriores);
      expect(resp2.periodoActual.cursos.length, 1);
      expect(resp2.periodoActual.cursos[0].parcial1?.calificacionSistema, '18');
      expect(resp2.anteriores.length, 1);
      expect(resp2.anteriores[0].periodoNombre, '2025-I');
      expect(resp2.anteriores[0].calificaciones[0].creditos, 3);
    });
  });
}
