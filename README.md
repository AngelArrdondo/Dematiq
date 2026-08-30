# Dematiq

Sitio web corporativo de **DEMATIQ Automatización** — automatización industrial, maquinaria a la medida e integración de sistemas — junto con su panel de administración (CMS a medida) para editar el contenido público sin tocar código.

Sitio en producción: [dematiq.com.mx](https://dematiq.com.mx)

## Stack

- **Frontend público**: HTML + CSS + JavaScript vanilla (sin build step, sin framework). Componentes compartidos (header, footer, quicknav) se inyectan por JS en cada página.
- **Backend / Admin**: PHP puro (sin framework), MySQL.
- **Autenticación**: sesión propia con verificación en dos pasos (TOTP) opcional por cuenta.
- **Correo**: PHPMailer vía SMTP (formulario de contacto y recuperación de contraseña).
- **Hosting**: Hostinger (Premium Web Hosting), con CDN propio de Hostinger delante del dominio.

## Estructura del proyecto

```
├── index.html              Home pública
├── pages/                  Resto de páginas públicas
│   ├── corporativo/        Nosotros, Contacto, Industrias, Proyectos, login/recuperación
│   ├── ensamble/           Página de Ensamble
│   ├── manufactura/        Página de Manufactura/Industrias
│   ├── maquinas/           Fichas de cada máquina (6 páginas)
│   └── servicios/          Página de Servicios
├── admin/                  Panel de administración (CMS)
│   ├── dashboard.php       Entrada principal del panel
│   ├── pages/              Un editor por sección de contenido (inicio, nosotros,
│   │                       servicios, máquinas, proyectos, industrias, marcas,
│   │                       partners, contacto, imágenes)
│   ├── api/                Endpoints internos del admin (contenido, imágenes, perfil)
│   └── includes/           Auth, helpers del admin
├── api/                    Endpoints públicos (contacto, contenido dinámico, contador de visitas)
├── includes/               Conexión a BD, auth, envío de correo (PHPMailer), TOTP, form guard
├── database/
│   ├── setup.sql           Esquema base + usuario admin inicial
│   ├── setup-root-only.sql Solo para local/VPS con acceso root (NO usar en Hostinger)
│   ├── contenido.sql       Contenido inicial editable
│   └── migrations/         Migraciones incrementales (orden numérico, ver abajo)
├── assets/                 CSS, JS, imágenes, videos, vendor
└── .env.example            Variables de entorno requeridas (documentadas, sin secretos reales)
```

## Contenido editable desde el admin

Casi todo el texto e imágenes del sitio público se sirven desde MySQL vía `api/contenido.php` y se editan en `admin/pages/`: Inicio, Nosotros, Servicios, Máquinas, Proyectos, Industrias, Marcas asociadas, Partners, datos de Contacto e Imágenes generales. El admin requiere login (`pages/corporativo/login.php`) y soporta 2FA opcional por cuenta (Mi Perfil → Contraseña → activar verificación en dos pasos).

## Variables de entorno

No hay archivo `.env` real en el repo — en Hostinger no existe UI de variables de entorno en este plan, así que las credenciales reales se definen vía `SetEnv` directamente en el `.htaccess` del servidor (fuera de git). Ver `.env.example` para la lista completa de variables necesarias (`DB_HOST/NAME/USER/PASS`, `SMTP_HOST/USER/PASS/PORT/SECURE/TO`) y las notas de cada una (por ejemplo, SMTP debe usar puerto 587 + STARTTLS; el 465/smtps se cuelga en este hosting).

Para desarrollo local, define esas mismas variables en tu entorno (nunca hardcodeadas en el código).

## Base de datos — orden de importación

1. `database/setup.sql` (tablas + usuario admin — reemplazar `REEMPLAZA_CON_HASH_BCRYPT` con un hash real antes de importar)
2. `database/contenido.sql`
3. `database/migrations/002_name_split.sql` → `010_maquinas_seed.sql`, en orden numérico

`database/setup-root-only.sql` es solo para un entorno local/VPS con acceso root; no usar en hosting compartido.

## Despliegue

Este sitio **no tiene auto-deploy desde GitHub** — cada cambio en `main` se sube manualmente a los archivos en vivo vía el Administrador de Archivos de Hostinger, y luego se purga la caché del CDN del dominio `dematiq.com.mx` para que el cambio se refleje de inmediato (sin la purga, el CDN puede seguir sirviendo la versión anterior por un rato).

Ver `DEPLOY-CHECKLIST.md` para el checklist completo de un despliegue desde cero (creación de BD, credenciales, orden de importación de esquema, variables de entorno y verificación post-deploy).

## Seguridad

- Ningún secreto (contraseñas de BD, SMTP, hashes) vive en este repo — solo nombres de variables en `.env.example`.
- Autenticación de admin con opción de 2FA (TOTP) y rate limiting en formularios públicos (`log_formularios`).
- Reglas de seguridad y caché a nivel de servidor en `.htaccess` (el de este repo; el `.htaccess` real del servidor añade además el bloque `SetEnv` con las credenciales, que nunca se sube a git).
