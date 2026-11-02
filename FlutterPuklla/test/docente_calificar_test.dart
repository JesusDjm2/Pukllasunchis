import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_puklla/features/docente/data/docente_calificar_data.dart';

void main() {
  group('DocenteCalificarData - Motor de Rúbricas y Escala Vigesimal', () {
    test('calcularPromedio calcula correctamente según la cantidad de competencias', () {
      // 3 competencias asignadas con notas 5, 4, 5 => promedio 4.67
      final prom3 = DocenteCalificarData.calcularPromedio([5, 4, 5], 3);
      expect(prom3, equals(4.67));

      // 2 competencias asignadas con notas 4, 3 => promedio 3.50
      final prom2 = DocenteCalificarData.calcularPromedio([4, 3], 2);
      expect(prom2, equals(3.50));

      // Lista vacía retorna null
      expect(DocenteCalificarData.calcularPromedio([], 3), isNull);
    });

    test('calcularValoracionVigesimal mapea fielmente los umbrales oficiales', () {
      expect(DocenteCalificarData.calcularValoracionVigesimal(1.0), equals(1));
      expect(DocenteCalificarData.calcularValoracionVigesimal(1.144), equals(1));
      expect(DocenteCalificarData.calcularValoracionVigesimal(1.145), equals(2));
      expect(DocenteCalificarData.calcularValoracionVigesimal(2.944), equals(10));
      expect(DocenteCalificarData.calcularValoracionVigesimal(3.244), equals(11));
      expect(DocenteCalificarData.calcularValoracionVigesimal(4.67), equals(18));
      expect(DocenteCalificarData.calcularValoracionVigesimal(5.0), equals(20));
      expect(DocenteCalificarData.calcularValoracionVigesimal(null), isNull);
    });

    test('calcularNivelLogro asigna el rango institucional exacto', () {
      expect(DocenteCalificarData.calcularNivelLogro(5), equals('Previo al inicio'));
      expect(DocenteCalificarData.calcularNivelLogro(10), equals('Inicio'));
      expect(DocenteCalificarData.calcularNivelLogro(11), equals('En proceso'));
      expect(DocenteCalificarData.calcularNivelLogro(14), equals('En proceso'));
      expect(DocenteCalificarData.calcularNivelLogro(15), equals('Logrado'));
      expect(DocenteCalificarData.calcularNivelLogro(18), equals('Logrado'));
      expect(DocenteCalificarData.calcularNivelLogro(20), equals('Destacado'));
      expect(DocenteCalificarData.calcularNivelLogro(null), isNull);
    });

    test('DocenteCalificarData.fromJson deserializa payloads FID y PPD sin fallos', () {
      final jsonSample = {
        'curso': {
          'id': 26,
          'nombre': 'Inclusión Educativa',
          'area': 'fid',
          'es_ppd': false,
          'ciclo': 'Ciclo IV',
          'programa': 'Educación Inicial',
        },
        'periodo': {
          'nombre': '2026-I',
          'parcial1_habilitado': true,
          'parcial2_habilitado': false,
        },
        'competencias': [
          {'id': 1, 'nombre': 'Competencia 1', 'descripcion': 'Planificación'},
          {'id': 3, 'nombre': 'Competencia 3', 'descripcion': 'Evaluación'},
        ],
        'alumnos': [
          {
            'id': 101,
            'nombre_completo': 'Pérez Gómez, Juan',
            'dni': '71234567',
            'es_inhabilitado': false,
            'no_matriculado': false,
            'p1': {
              'valoracion_1': 5,
              'valoracion_2': 4,
              'valoracion_curso': 4.5,
              'calificacion_curso': 'Logrado',
              'calificacion_sistema': 17,
              'observaciones': 'Buen desempeño',
            }
          }
        ]
      };

      final data = DocenteCalificarData.fromJson(jsonSample);
      expect(data.curso.id, equals(26));
      expect(data.curso.esPpd, isFalse);
      expect(data.periodo.parcial1Habilitado, isTrue);
      expect(data.periodo.parcial2Habilitado, isFalse);
      expect(data.competencias.length, equals(2));
      expect(data.alumnos.length, equals(1));
      expect(data.alumnos.first.p1?.calificacionSistema, equals(17));
      expect(data.alumnos.first.p1?.calificacionCurso, equals('Logrado'));
    });
  });
}
