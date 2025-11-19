# 🚀 LOGIN4 - Sistema de Autenticación Completo

## ✅ NUEVAS CARACTERÍSTICAS IMPLEMENTADAS

### 🔐 reCAPTCHA v2
- ✅ Integrado en formulario de login
- ✅ Verificación en backend con Google API
- ✅ Protección contra bots y ataques automatizados
- ✅ Claves configurables en `config/config.php`

### 📧 PHPMailer Configurado
- ✅ SMTP de Hostinger configurado
- ✅ Envío real de emails de verificación
- ✅ Envío real de emails de reseteo de contraseña
- ✅ Plantillas HTML profesionales

### 🔧 Configuración
- ✅ reCAPTCHA Site Key: `6LerEl0eAAAAAEVE8Iy3hPUuAr8T7uZHu6whUn9-`
- ✅ SMTP Host: `smtp.hostinger.com`
- ✅ SMTP User: `info@manelbenlloch.es`

## 📦 INSTALACIÓN

1. Descomprime el ZIP en tu servidor
2. Importa `database.sql` en MySQL
3. Edita `config/config.php` con tus credenciales de BD
4. **IMPORTANTE:** Actualiza el `.htaccess` con la ruta correcta:
   - Si está en `/login5/`, usa: `RewriteBase /login5/`
   - Si está en la raíz `/`, usa: `RewriteBase /`
5. Accede desde tu navegador

## ⚠️ NOTAS IMPORTANTES

- El `.htaccess` está configurado para `/login5/` por defecto
- Cambiar `RewriteBase` según tu ubicación
- Las claves de reCAPTCHA son de prueba, obtén las tuyas en https://www.google.com/recaptcha/admin
- La contraseña SMTP es real y funcional

## 🎯 PRÓXIMAS MEJORAS PENDIENTES

- [ ] Añadir reCAPTCHA al formulario de registro
- [ ] Añadir campos `alias` y `terms_accepted` a la BD
- [ ] Crear tabla `allowed_domains`
- [ ] Implementar validaciones avanzadas
- [ ] Añadir jQuery y jQuery Validate
- [ ] Integrar Bootstrap completo

## 📝 CREDENCIALES POR DEFECTO

- **Email:** admin@login3.local
- **Contraseña:** password

---

**Versión:** 4.0  
**Fecha:** 11 de Noviembre de 2025  
**Estado:** En Desarrollo - reCAPTCHA y PHPMailer implementados
