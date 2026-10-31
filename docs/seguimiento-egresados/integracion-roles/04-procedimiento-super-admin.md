# PUKLLA EN RED — Gestión Institucional de Superadministradores
## Documento 04: Procedimiento de Alta Segura de Super-Admin y Asignación de Roles

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  

---

### 1. PRINCIPIO DE SEGURIDAD PARA CUENTAS PRIVILEGIADAS

En cumplimiento estricto de las directivas institucionales y de auditoría externa:
1. **Cero credenciales en código:** Ninguna contraseña, hash estático ni clave predeterminada está escrita en controladores, migraciones, seeders, commits ni archivos Markdown.
2. **Preservación de cuentas preexistentes:** Las cuentas preexistentes con rol `super-admin` (David Jesús ID 210 y Vilma Raquel ID 675) y cuentas `admin` (Escuela ID 1, Sharon Sthepanie ID 375, Vanessa ID 434, Marilyn ID 668, David Jesus ID 309, Pavel ID 312) no han sido modificadas ni eliminadas.
3. **No puertas traseras (Backdoors):** El alta de una cuenta superadministradora requiere ejecución en consola protegida del servidor o autorización desde una sesión de superadministrador activa.

---

### 2. COMANDO ARTISAN DE ALTA SEGURA: `puklla:superadmin-crear`

Se desarrolló un comando de consola reproducible y validado en `app/Console/Commands/CrearSuperAdminSeguroCommand.php`.

#### 2.1. Firma del Comando:
```bash
php artisan puklla:superadmin-crear [--email=] [--dni=] [--name=] [--apellidos=]
```

#### 2.2. Flujo de Operación:
1. **Validación de Identidad:** Solicita el correo institucional del superadministrador. Comprueba formato y verifica si ya existe en la tabla `users`.
2. **Prevención de Duplicados:** Si el usuario ya existe y ya tiene `super-admin`, informa que la cuenta ya está provista y finaliza sin alterar nada.
3. **Promoción de Cuenta Existente:** Si el usuario existe pero tiene otro rol institucional, ofrece promoverlo a `super-admin` conservando su ID institucional.
4. **Captura Oculta de Credenciales:** Solicita la contraseña mediante `$this->secret()`. Los caracteres **no se muestran en la pantalla** ni quedan grabados en el historial de comandos de la shell (`.bash_history` / `.zsh_history`).
5. **Doble Confirmación:** Exige reingresar la contraseña para evitar errores de tipeo y valida longitud mínima de 8 caracteres.
6. **Cifrado Robusto:** Aplica `Hash::make()` mediante el algoritmo bcrypt configurado en Laravel.
7. **Asignación Spatie y Trazabilidad:** Asigna el rol `super-admin` mediante Spatie y registra el evento en `egresado_auditoria_roles` con fecha, hora e ID.

---

### 3. PROCEDIMIENTO PASO A PASO PARA EL ADMINISTRADOR DEL SISTEMA

Para dar de alta de forma segura a un nuevo superadministrador en el servidor o entorno local:

```bash
cd /ruta/hacia/Pukllasunchis
php artisan puklla:superadmin-crear
```

**Ejemplo de interacción segura en consola:**
```
================================================================
  EESP PUKLLASUNCHIS · PROVISIÓN SEGURA DE CUENTA SUPER-ADMIN   
================================================================
Ingrese el correo institucional del superadministrador: direccion.academica@pukllavirtual.edu.pe
Nombres: Carlos Alberto
Apellidos: Huamán Quispe
DNI: 23984512
Ingrese una contraseña segura (mínimo 8 caracteres): [ENTRADA OCULTA]
Confirme la contraseña: [ENTRADA OCULTA]

Creando nuevo usuario super-admin institucional...

✔ Provisión completada exitosamente.
  Usuario: Carlos Alberto Huamán Quispe (direccion.academica@pukllavirtual.edu.pe)
  Rol: super-admin asignado correctamente.
  Trazabilidad registrada en egresado_auditoria_roles.
```

---

### 4. PROCEDIMIENTO PARA ASIGNAR EL ROL A UN DOCENTE DESDE EL PANEL

Una vez iniciada sesión como `super-admin`:

1. Ingresar al panel institucional: `https://intranet.pukllasunchis.edu.pe/Administrador`.
2. En el listado de usuarios, buscar al docente deseado por nombre, apellidos o DNI.
3. Hacer clic en el botón de edición (**Editar usuario**).
4. En la sección **Roles del usuario**, se mostrarán las píldoras de roles activos del usuario:
   - La píldora `Docente` estará seleccionada (`.rp-docente`).
   - Marcar la casilla de la píldora `Seguimiento Egresados` (`.rp-seguimiento`, color granate con icono de red institucional).
5. Hacer clic en **Guardar cambios**.
6. El sistema valida la petición en el backend (`AdminController@update`), verifica que el usuario ejecutante sea `super-admin` o `admin`, sincroniza los roles manteniendo intactos los cursos y relaciones docentes, y crea el registro de auditoría en `egresado_auditoria_roles`.
7. Cuando el docente inicie sesión, encontrará inmediatamente la entrada **Seguimiento de Egresados — Puklla en Red** en su menú lateral y en su tarjeta de bienvenida.

---

### 5. PROCEDIMIENTO PARA REVOCAR EL ROL

1. En el panel administrativo (`/Administrador`), editar al docente.
2. Desmarcar la casilla `Seguimiento Egresados`.
3. Mantener marcada la casilla `Docente`.
4. Hacer clic en **Guardar cambios**.
5. El sistema elimina el rol `seguimiento-egresados`, genera el evento `revocado` en la tabla de auditoría y conserva el registro docente con todos sus cursos y notas inalterados.
