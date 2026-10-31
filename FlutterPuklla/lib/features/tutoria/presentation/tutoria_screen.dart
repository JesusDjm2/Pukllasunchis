import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../routing/app_drawer.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';

class TutoriaScreen extends StatefulWidget {
  const TutoriaScreen({super.key});

  @override
  State<TutoriaScreen> createState() => _TutoriaScreenState();
}

class _TutoriaScreenState extends State<TutoriaScreen> {
  final _formKey = GlobalKey<FormState>();
  final _motivoController = TextEditingController();
  String _tipo = 'tutoria'; // 'tutoria' o 'sugerencia'
  bool _enviado = false;

  @override
  void dispose() {
    _motivoController.dispose();
    super.dispose();
  }

  void _enviar() {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _enviado = true;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_tipo == 'tutoria'
              ? '¡Solicitud de tutoría enviada a tu tutor asignado!'
              : '¡Gracias! Tu sugerencia ha sido recibida con éxito.'),
          backgroundColor: const Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tutoría y Sugerencias'),
        actions: const [NotificationBellAction()],
      ),
      drawer: const AppDrawer(),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        children: [
          // Banner institucional
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F766E), Color(0xFF115E59)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0F766E).withValues(alpha: 0.18),
                  blurRadius: 18,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.favorite_outline, color: Colors.white, size: 28),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Acompañamiento Integral',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Estamos para escucharte, orientarte y crecer juntos en tu camino docente.',
                        style: TextStyle(
                          color: Color(0xFFCCFBF1),
                          fontSize: 12.5,
                          height: 1.35,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          // Formulario
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF1E293B).withValues(alpha: 0.05),
                  blurRadius: 16,
                  offset: const Offset(0, 4),
                ),
              ],
              border: Border.all(color: Colors.black.withValues(alpha: 0.04)),
            ),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    '¿En qué podemos apoyarte hoy?',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                  const SizedBox(height: 14),
                  // Selector tipo
                  Row(
                    children: [
                      Expanded(
                        child: _SelectorBoton(
                          label: 'Pedir Tutoría',
                          icon: Icons.person_pin_outlined,
                          seleccionado: _tipo == 'tutoria',
                          onTap: () => setState(() => _tipo = 'tutoria'),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _SelectorBoton(
                          label: 'Buzón / Sugerencia',
                          icon: Icons.lightbulb_outline,
                          seleccionado: _tipo == 'sugerencia',
                          onTap: () => setState(() => _tipo = 'sugerencia'),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  Text(
                    _tipo == 'tutoria'
                        ? 'Describe brevemente el tema o situación que deseas conversar:'
                        : 'Escribe tu sugerencia para mejorar la experiencia institucional:',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF475569),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _motivoController,
                    maxLines: 5,
                    decoration: InputDecoration(
                      hintText: _tipo == 'tutoria'
                          ? 'Ejemplo: Necesito orientación sobre el avance de mis prácticas o dificultades con el horario...'
                          : 'Ejemplo: Sería de gran ayuda contar con más computadoras en sala de estudio...',
                      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                    ),
                    validator: (val) {
                      if (val == null || val.trim().length < 10) {
                        return 'Por favor escribe al menos 10 caracteres.';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: FilledButton.icon(
                      onPressed: _enviado ? null : _enviar,
                      icon: Icon(_enviado ? Icons.check : Icons.send_rounded, size: 18),
                      label: Text(
                        _enviado ? 'Enviado correctamente' : 'Enviar mensaje',
                        style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: _tipo == 'tutoria' ? AppColors.teal : AppColors.gold,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SelectorBoton extends StatelessWidget {
  const _SelectorBoton({
    required this.label,
    required this.icon,
    required this.seleccionado,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool seleccionado;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: seleccionado ? AppColors.teal.withValues(alpha: 0.1) : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: seleccionado ? AppColors.teal : Colors.transparent,
            width: 1.5,
          ),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: seleccionado ? AppColors.teal : Colors.grey.shade600,
              size: 22,
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: seleccionado ? FontWeight.w700 : FontWeight.w600,
                color: seleccionado ? AppColors.teal : Colors.grey.shade700,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
