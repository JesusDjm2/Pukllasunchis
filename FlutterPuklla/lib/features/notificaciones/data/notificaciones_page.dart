import 'notificacion.dart';

class NotificacionesPage {
  const NotificacionesPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  factory NotificacionesPage.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as List;
    final meta = json['meta'] as Map<String, dynamic>;
    return NotificacionesPage(
      items: data.map((j) => Notificacion.fromJson(j as Map<String, dynamic>)).toList(),
      currentPage: meta['current_page'] as int,
      lastPage: meta['last_page'] as int,
      total: meta['total'] as int,
    );
  }

  final List<Notificacion> items;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hayPaginaAnterior => currentPage > 1;
  bool get hayPaginaSiguiente => currentPage < lastPage;
}
