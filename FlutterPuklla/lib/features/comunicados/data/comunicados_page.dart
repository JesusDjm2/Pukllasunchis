import 'comunicado.dart';

/// Espejo de la envoltura `{"data": [...], "meta": {...}}` de
/// `GET /api/v1/comunicados`.
class ComunicadosPage {
  const ComunicadosPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  factory ComunicadosPage.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as List;
    final meta = json['meta'] as Map<String, dynamic>;
    return ComunicadosPage(
      items: data.map((j) => Comunicado.fromJson(j as Map<String, dynamic>)).toList(),
      currentPage: meta['current_page'] as int,
      lastPage: meta['last_page'] as int,
      total: meta['total'] as int,
    );
  }

  final List<Comunicado> items;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hayPaginaAnterior => currentPage > 1;
  bool get hayPaginaSiguiente => currentPage < lastPage;
}
