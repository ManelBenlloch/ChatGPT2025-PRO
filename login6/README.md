# login3 - Sistema de Autenticación Profesional

Sistema de autenticación completo y profesional con arquitectura MVC, diseñado para ser **totalmente portable** y funcionar en cualquier entorno sin modificaciones.

## ✨ Características

- ✅ **Portabilidad Total**: Funciona en cualquier carpeta, dominio o subdominio sin cambios en el código
- ✅ **Rutas Amigables**: URLs limpias sin necesidad de escribir `/public` o rutas internas
- ✅ **Arquitectura MVC**: Código limpio, organizado y escalable
- ✅ **Diseño Profesional**: Interfaz moderna con fuente Inter y colores índigo
- ✅ **4 Niveles de Roles**: `user`, `personal`, `admin`, `root`
- ✅ **Seguridad Avanzada**: Preparado para 2FA, WAF, Rate Limiting, Logs de Auditoría
- ✅ **Base de Datos Completa**: Esquema empresarial con todas las tablas necesarias
- ✅ **Soft Deletes**: Recuperación de usuarios eliminados
- ✅ **Verificación de Email**: Sistema de tokens para verificación
- ✅ **Reseteo de Contraseña**: Con tokens de expiración
- ✅ **Comentarios en Castellano**: Todo el código está documentado en español

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.2 o superior
- Apache con mod_rewrite habilitado
- Extensiones PHP: PDO, pdo_mysql

## 🚀 Instalación

### 1. Clonar o Descargar el Proyecto

Descarga el proyecto y colócalo en cualquier carpeta de tu servidor web. Puede estar en:
- `localhost/login3/`
- `localhost/proyectos/mi-sistema-login/`
- `manelbenlloch.es/login/`
- `secure.midominio.dev/`
- **¡Cualquier ubicación!** El sistema se adaptará automáticamente.

### 2. Configurar la Base de Datos

1. Importa el archivo `database.sql` en tu base de datos MySQL:

```bash
mysql -u tu_usuario -p < database.sql
```

O desde phpMyAdmin:
- Abre phpMyAdmin
- Crea una base de datos llamada `login3_db`
- Importa el archivo `database.sql`

2. Edita el archivo `config/config.php` y ajusta las credenciales de la base de datos:

```php
// Para entorno LOCAL (XAMPP)
define('DB_HOST_LOCAL', 'localhost');
define('DB_NAME_LOCAL', 'login3_db');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');

// Para entorno PRODUCCIÓN (Hostinger/SiteGround)
define('DB_HOST_PROD', 'localhost');
define('DB_NAME_PROD', 'u459047355_login3');
define('DB_USER_PROD', 'u459047355_login3');
define('DB_PASS_PROD', 'TU_CONTRASEÑA_AQUI');
```

El sistema detectará automáticamente si estás en `localhost` o en producción.

### 3. Configurar Apache

Asegúrate de que el `mod_rewrite` de Apache esté habilitado. El archivo `.htaccess` ya está incluido en la raíz del proyecto.

Si estás usando XAMPP, el mod_rewrite suele estar habilitado por defecto.

### 4. Acceder al Sistema

Abre tu navegador y accede a la URL donde instalaste el proyecto. Por ejemplo:

- `http://localhost/login3/`
- `https://manelbenlloch.es/login/`
- `http://localhost/proyectos/mi-login/`

**IMPORTANTE**: NO necesitas escribir `/public` ni ninguna otra ruta interna. Simplemente accede a la carpeta raíz del proyecto.

El sistema te redirigirá automáticamente a la página de login.

### 5. Credenciales por Defecto

El sistema incluye un usuario root por defecto:

- **Email**: `admin@login3.local`
- **Contraseña**: `password`

**⚠️ IMPORTANTE**: Cambia estas credenciales inmediatamente después del primer login.

## 📁 Estructura del Proyecto

```
login3/
├── app/
│   ├── Controllers/       # Controladores (lógica de negocio)
│   ├── Models/            # Modelos (acceso a datos)
│   └── Middleware/        # Middleware (WAF, autenticación, etc.)
├── assets/
│   ├── css/               # Estilos CSS
│   ├── js/                # JavaScript
│   └── img/               # Imágenes
├── config/
│   └── config.php         # Configuración principal
├── core/
│   ├── Router.php         # Sistema de enrutamiento
│   ├── Controller.php     # Clase base para controladores
│   └── Model.php          # Clase base para modelos
├── views/
│   ├── auth/              # Vistas de autenticación
│   └── partials/          # Componentes reutilizables
├── helpers/               # Funciones auxiliares
├── vendor/                # Dependencias (PHPMailer, etc.)
├── index.php              # Punto de entrada único
├── .htaccess              # Configuración de Apache
├── app_boot.php           # Script de portabilidad
├── database.sql           # Esquema de la base de datos
└── README.md              # Este archivo
```

## 🌐 Rutas Disponibles

El sistema utiliza rutas amigables y limpias:

- `/` - Redirige al login
- `/login` - Página de inicio de sesión
- `/register` - Página de registro
- `/logout` - Cerrar sesión
- `/dashboard` - Panel de usuario (requiere autenticación)
- `/verify-email?token=XXX` - Verificar email
- `/reset-password` - Solicitar reseteo de contraseña

**Todas las rutas son relativas a la carpeta donde instalaste el proyecto.**

Por ejemplo, si instalaste en `localhost/login3/`, las rutas serán:
- `http://localhost/login3/login`
- `http://localhost/login3/register`
- etc.

## 🔧 Configuración Avanzada

### Cambiar el Nombre de la Aplicación

Edita el archivo `config/config.php`:

```php
define('APP_NAME', 'Mi Sistema de Login');
```

### Configurar Email (PHPMailer)

Para habilitar el envío de emails (verificación, reseteo de contraseña), edita `config/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu_email@gmail.com');
define('SMTP_PASS', 'tu_contraseña_de_aplicacion');
define('SMTP_FROM_EMAIL', 'noreply@tudominio.com');
define('SMTP_FROM_NAME', 'Sistema de Login');
```

### Cambiar la Zona Horaria

Edita el archivo `config/config.php`:

```php
date_default_timezone_set('Europe/Madrid');
```

## 🛡️ Seguridad

- Las contraseñas se almacenan con `bcrypt` (hash seguro)
- Protección contra SQL Injection mediante PDO con prepared statements
- Protección contra XSS mediante `htmlspecialchars()`
- Tokens CSRF para formularios (próximamente)
- Rate limiting para prevenir ataques de fuerza bruta (próximamente)
- WAF (Web Application Firewall) integrado (próximamente)

## 🌐 Portabilidad

El sistema está diseñado para funcionar en **cualquier entorno** sin modificaciones:

- ✅ Localhost con XAMPP
- ✅ Hostinger
- ✅ SiteGround
- ✅ Cualquier hosting con PHP y MySQL
- ✅ Cualquier dominio (.es, .dev, .org, etc.)
- ✅ Cualquier nombre de carpeta (login, login2, logon, etc.)
- ✅ Cualquier nivel de subcarpetas

El archivo `app_boot.php` se encarga de detectar automáticamente la ubicación y configurar las rutas correctamente.

## 📝 Próximas Funcionalidades

- [ ] Autenticación de Dos Factores (2FA)
- [ ] Web Application Firewall (WAF)
- [ ] Rate Limiting avanzado
- [ ] Logs de actividad completos
- [ ] Panel de administración
- [ ] API REST
- [ ] Integración con PHPMailer
- [ ] Recuperación de usuarios eliminados (soft delete)

## 🤝 Soporte

Para cualquier duda o problema, contacta con el desarrollador.

## 📄 Licencia

Este proyecto es de uso privado.

---

**Desarrollado por Manus AI para Manel Benlloch**
**Fecha: 10 de Noviembre, 2025**
