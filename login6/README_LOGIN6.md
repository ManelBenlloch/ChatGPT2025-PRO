# LOGIN6 - Sistema de Autenticación con Roles Dinámicos y Permisos Granulares

## Fecha: 17 de noviembre de 2025

---

## 🎯 Descripción General

**login6** es un sistema de autenticación avanzado con gestión de roles dinámicos y permisos granulares. Permite crear roles personalizados sin necesidad de crear carpetas físicas, manteniendo los roles por defecto del sistema (user, personal, admin, root) como intocables.

---

## ✨ Características Principales

### Sistema de Roles Dinámicos

- ✅ **Roles del Sistema** (intocables): `user`, `personal`, `admin`, `root`
- ✅ **Roles Personalizados**: Crea infinitos roles desde la interfaz web
- ✅ **Sin Carpetas Físicas**: Todo se gestiona por base de datos
- ✅ **Permisos Granulares**: Control fino sobre qué puede hacer cada rol
- ✅ **Gestión Completa**: CRUD completo de roles desde el panel root

### Permisos Granulares

**30+ permisos predefinidos** organizados en 5 categorías:

#### Categoría: users
- `view_users` - Ver listado de usuarios
- `create_users` - Crear usuarios
- `edit_users` - Editar usuarios
- `delete_users` - Eliminar usuarios
- `manage_user_roles` - Gestionar roles de usuarios
- `view_deleted_users` - Ver usuarios eliminados
- `restore_users` - Restaurar usuarios eliminados

#### Categoría: posts
- `view_posts` - Ver publicaciones
- `create_posts` - Crear publicaciones
- `edit_own_posts` - Editar propias publicaciones
- `edit_all_posts` - Editar todas las publicaciones
- `delete_own_posts` - Eliminar propias publicaciones
- `delete_all_posts` - Eliminar todas las publicaciones
- `publish_posts` - Publicar/despublicar posts

#### Categoría: system
- `view_logs` - Ver logs del sistema
- `manage_settings` - Gestionar configuración
- `manage_roles` - Gestionar roles y permisos
- `manage_permissions` - Gestionar permisos
- `access_root_panel` - Acceder al panel root
- `access_admin_panel` - Acceder al panel admin
- `view_system_info` - Ver información del sistema

#### Categoría: sessions
- `view_sessions` - Ver sesiones activas
- `manage_own_sessions` - Gestionar propias sesiones
- `manage_all_sessions` - Gestionar todas las sesiones
- `revoke_sessions` - Revocar sesiones de otros usuarios

#### Categoría: security
- `manage_2fa` - Gestionar autenticación de dos factores
- `view_security_logs` - Ver logs de seguridad
- `manage_ip_blocks` - Gestionar bloqueos de IP
- `manage_waf` - Gestionar Web Application Firewall

### Características Heredadas de login5

- ✅ Sistema de autenticación completo (login, registro, recuperación)
- ✅ Verificación de email
- ✅ Rate limiting y protección contra fuerza bruta
- ✅ Web Application Firewall (WAF)
- ✅ Logs de actividad
- ✅ Gestión de sesiones
- ✅ Panel root con 9 módulos de gestión
- ✅ API REST completa
- ✅ Portabilidad extrema (funciona en cualquier carpeta)

---

## 📁 Estructura de Base de Datos

### Tablas Nuevas

#### `roles`
Almacena roles del sistema y roles personalizados.

```sql
- id (INT, PK)
- name (VARCHAR, UNIQUE)
- display_name (VARCHAR)
- description (TEXT)
- is_system_role (BOOLEAN)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- created_by (INT, FK)
```

#### `permissions`
Almacena permisos granulares del sistema.

```sql
- id (INT, PK)
- name (VARCHAR, UNIQUE)
- display_name (VARCHAR)
- description (TEXT)
- category (VARCHAR)
- created_at (TIMESTAMP)
```

#### `role_permissions`
Relación muchos a muchos entre roles y permisos.

```sql
- id (INT, PK)
- role_id (INT, FK)
- permission_id (INT, FK)
- granted_at (TIMESTAMP)
- granted_by (INT, FK)
```

### Modificación en `users`

Se agregó el campo `role_id` para asignar roles personalizados:

```sql
- role_id (INT, FK, NULL)
```

**Lógica de Prioridad**:
1. Si `role` = 'root' → Todos los permisos automáticamente
2. Si `role_id` está definido → Usa permisos del rol personalizado
3. Si `role` está definido → Usa permisos del rol del sistema

---

## 🚀 Instalación

### Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- XAMPP (desarrollo local) o servidor web compatible

### Paso 1: Copiar archivos

Copia la carpeta `login6` a tu servidor web:

```
C:\xampp\htdocs\login6\           (Windows - XAMPP)
/var/www/html/login6/             (Linux)
public_html/login6/               (Hostinger)
```

### Paso 2: Crear base de datos

1. Accede a phpMyAdmin
2. Crea una nueva base de datos llamada `login6_db`
3. Importa el archivo `database/INSTALAR_LOGIN6_DB.sql`

### Paso 3: Configurar conexión

Edita el archivo `config/config.php` y ajusta las credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'login6_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Paso 4: Acceder al sistema

Accede desde tu navegador:

```
http://localhost/login6/
```

**Usuario root por defecto**:
- Email: `root@system.local`
- Contraseña: `Root@2025`

---

## 🔐 Gestión de Roles y Permisos

### Acceder a la Gestión de Roles

1. Inicia sesión como usuario root
2. Accede al panel root
3. Haz clic en "Gestión de Roles y Permisos"
4. URL directa: `http://localhost/login6/roles`

### Crear un Rol Personalizado

1. Haz clic en "Crear Nuevo Rol"
2. Ingresa:
   - **Nombre del rol**: Identificador único (ej: `editor_contenido`)
   - **Nombre para mostrar**: Nombre legible (ej: "Editor de Contenido")
   - **Descripción**: Opcional, describe el propósito del rol
3. Haz clic en "Crear Rol y Asignar Permisos"
4. Selecciona los permisos que tendrá el rol
5. Guarda los cambios

### Asignar Rol a Usuario

1. Ve a "Gestión de Usuarios" en el panel root
2. Edita un usuario
3. Selecciona el rol personalizado del dropdown
4. Guarda los cambios

### Editar Permisos de un Rol

1. En la lista de roles, haz clic en "Permisos"
2. Marca/desmarca los permisos deseados
3. Haz clic en "Guardar Permisos"

### Eliminar un Rol

Solo se pueden eliminar roles personalizados que **no tengan usuarios asignados**.

1. En la lista de roles, haz clic en "Eliminar"
2. Confirma la acción

---

## 💻 Uso del Middleware de Permisos

### En Rutas (index.php)

```php
$router->get('/posts/create', function() {
    PermissionMiddleware::requirePermission('create_posts');
    
    require_once app_path('app/Controllers/PostController.php');
    $controller = new PostController();
    $controller->create();
});
```

### Verificar Múltiples Permisos

```php
// Requiere ALGUNO de los permisos
PermissionMiddleware::requireAnyPermission(['edit_own_posts', 'edit_all_posts']);

// Requiere TODOS los permisos
PermissionMiddleware::requireAllPermissions(['view_users', 'edit_users']);
```

### En Vistas

```php
<?php
require_once app_path('app/Middleware/PermissionMiddleware.php');

if (PermissionMiddleware::hasPermission('edit_users')): ?>
    <a href="<?= asset('users/edit/' . $user->id) ?>">Editar</a>
<?php endif; ?>
```

### En Controladores

```php
class PostController extends Controller {
    public function create() {
        PermissionMiddleware::requirePermission('create_posts');
        
        // Código del controlador...
    }
}
```

---

## 📚 Ejemplos de Uso

### Ejemplo 1: Crear rol "Editor de Contenido"

**Objetivo**: Usuario que puede gestionar posts pero no usuarios.

1. Crear rol `editor_contenido`
2. Asignar permisos:
   - `view_posts`
   - `create_posts`
   - `edit_own_posts`
   - `delete_own_posts`
   - `publish_posts`
3. Asignar rol a usuario "Juan"

**Resultado**: Juan puede gestionar posts pero NO usuarios ni configuración.

### Ejemplo 2: Crear rol "Moderador"

**Objetivo**: Usuario que puede moderar contenido y ver usuarios.

1. Crear rol `moderador`
2. Asignar permisos:
   - `view_users`
   - `view_posts`
   - `edit_all_posts`
   - `delete_all_posts`
   - `view_logs`
3. Asignar rol a usuario "María"

**Resultado**: María puede moderar contenido pero NO gestionar usuarios ni sistema.

### Ejemplo 3: Crear rol "Soporte Técnico"

**Objetivo**: Usuario que puede ver información del sistema y gestionar sesiones.

1. Crear rol `soporte_tecnico`
2. Asignar permisos:
   - `view_users`
   - `view_sessions`
   - `manage_all_sessions`
   - `view_logs`
   - `view_system_info`
3. Asignar rol a usuario "Carlos"

**Resultado**: Carlos puede ayudar a usuarios con sesiones pero NO modificar configuración.

---

## 🔒 Seguridad

### Protecciones Implementadas

1. **Roles del Sistema Intocables**: No se pueden editar ni eliminar
2. **Solo Root Puede Gestionar Roles**: Middleware `requirePermission('manage_roles')`
3. **Validación de Permisos**: Cada acción verifica permisos antes de ejecutar
4. **Logs de Auditoría**: Registra quién otorgó/revocó permisos
5. **Prevención de Escalada**: Un usuario no puede asignarse permisos a sí mismo
6. **Verificación de Usuarios Asignados**: No se puede eliminar un rol con usuarios

### Verificación de Permisos

El sistema verifica permisos en este orden:

1. ¿Es usuario root? → Todos los permisos
2. ¿Tiene `role_id`? → Permisos del rol personalizado
3. ¿Tiene `role`? → Permisos del rol del sistema
4. Si no tiene ninguno → Sin permisos

---

## 🛠️ Arquitectura Técnica

### Modelos

- **`Role.php`**: Gestión de roles (CRUD, asignación de permisos)
- **`Permission.php`**: Gestión de permisos y verificación
- **`User.php`**: Gestión de usuarios (heredado de login5)
- **`ActivityLog.php`**: Registro de actividad
- **`RateLimit.php`**: Control de intentos de login

### Controladores

- **`RoleController.php`**: CRUD de roles y gestión de permisos
- **`AuthController.php`**: Autenticación (heredado)
- **`RootController.php`**: Panel root (heredado)
- **`ApiController.php`**: API REST (heredado)

### Middleware

- **`PermissionMiddleware.php`**: Verificación de permisos granulares
- **`AuthMiddleware.php`**: Verificación de autenticación (heredado)
- **`WAFMiddleware.php`**: Web Application Firewall (heredado)

### Vistas

```
views/
├── roles/
│   ├── index.php           # Listado de roles
│   ├── create.php          # Crear rol
│   ├── edit.php            # Editar rol
│   └── permissions.php     # Gestionar permisos
├── root/
│   ├── dashboard.php       # Panel root (actualizado con enlace a roles)
│   └── ...
└── ...
```

---

## 🌐 Portabilidad

### Funciona en Cualquier Carpeta

El sistema es **100% portable** y funciona en cualquier carpeta sin modificar código:

```
http://localhost/login6/
http://localhost/login-me-cago-en-tu-puta-madre/
http://localhost/auth-system/
http://localhost/cualquier-nombre/
https://manelbenlloch.es/login6/
https://tudominio.com/sistema-auth/
```

### Funciona en Cualquier Servidor

- ✅ XAMPP (localhost)
- ✅ Hostinger
- ✅ SiteGround
- ✅ Cualquier servidor con Apache + PHP + MySQL

---

## 📊 Diferencias con login5

| Característica | login5 | login6 |
|----------------|--------|--------|
| Roles del sistema | ✅ | ✅ |
| Roles personalizados | ❌ | ✅ |
| Permisos granulares | ❌ | ✅ |
| Gestión de roles desde UI | ❌ | ✅ |
| Tabla `roles` | ❌ | ✅ |
| Tabla `permissions` | ❌ | ✅ |
| Tabla `role_permissions` | ❌ | ✅ |
| Middleware de permisos | ❌ | ✅ |
| Panel de gestión de roles | ❌ | ✅ |
| 30+ permisos predefinidos | ❌ | ✅ |

---

## 🐛 Solución de Problemas

### Error: "No se puede conectar a la base de datos"

**Solución**: Verifica las credenciales en `config/config.php`

### Error: "Permiso denegado" al acceder a /roles

**Solución**: Asegúrate de estar logueado como usuario root

### Los roles personalizados no aparecen

**Solución**: Verifica que la tabla `roles` tenga datos. Ejecuta el SQL de instalación.

### No se pueden asignar permisos

**Solución**: Verifica que la tabla `permissions` tenga los 30+ permisos predefinidos.

---

## 📝 Notas Importantes

1. **Usuario Root**: Siempre tiene todos los permisos, independientemente de la tabla `role_permissions`
2. **Roles del Sistema**: No se pueden editar ni eliminar (marcados con `is_system_role = 1`)
3. **Compatibilidad**: Mantiene compatibilidad con el campo `role` de login5
4. **Migración**: Usuarios existentes de login5 seguirán funcionando sin cambios

---

## 🎓 Buenas Prácticas

1. **Nombres Descriptivos**: Usa nombres que reflejen claramente la función del rol
2. **Roles Específicos**: Define roles específicos en lugar de roles muy amplios
3. **Mínimo Privilegio**: Asigna solo los permisos necesarios
4. **Documentación**: Documenta el propósito del rol en la descripción
5. **Auditoría**: Revisa periódicamente los permisos asignados
6. **Pruebas**: Prueba los roles con usuarios de prueba antes de asignarlos

---

## 📞 Soporte

Para dudas o problemas, contacta al administrador del sistema.

---

## 📜 Licencia

Sistema desarrollado para uso interno. Todos los derechos reservados.

---

**Versión**: 6.0.0  
**Fecha**: 17 de noviembre de 2025  
**Estado**: ✅ Producción
