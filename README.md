<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## GEDIS — Gestión Documental y Archivística

### Configuración inicial

1. **Clonar el repositorio:**
   ```bash
   git clone <repo-url>
   cd gedis-main
   ```

2. **Instalar dependencias PHP:**
   ```bash
   composer install
   ```

3. **Configurar archivo de entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar base de datos MySQL:**
   - Crear BD: `mysql -u root -e "CREATE DATABASE gda;"`
   - Actualizar `.env` con credenciales MySQL

5. **Ejecutar migraciones Laravel:**
   ```bash
   php artisan migrate
   ```

6. **Crear tablas del módulo de Correspondencia:**
   ```bash
   mysql -u root gda < database/sql/004-correspondencias.sql
   ```

7. **Instalar dependencias npm:**
   ```bash
   npm install
   ```

8. **Compilar assets (desarrollo):**
   ```bash
   npm run dev
   ```

   O producción:
   ```bash
   npm run build
   ```

9. **Iniciar servidor:**
   ```bash
   php artisan serve
   ```

10. **Acceder a la aplicación:**
    - URL: `http://localhost:8000`
    - Login predeterminado: revisar seeders o crear usuario vía `/Createuser`

### Módulo de Correspondencia (004)

#### Características
- Crear y gestionar documentos de correspondencia dentro de expedientes
- Editor WYSIWYG (TinyMCE) con soporte para texto, imágenes y tablas
- Firma electrónica SAT integrada
- Generación de PDF con metadata y firma
- Auditoría de accesos y cambios
- Clasificación de documentos (pública, reservada, confidencial)

#### Acceso a módulo
- URL: `/correspondencia`
- Roles permitidos: `root`, `administrador`, `editor`, `lector` (vista)

#### Configuración SAT
Para integrar firma electrónica con SAT:

1. Solicitar credenciales al SAT:
   - Endpoint de API
   - API Key
   - Certificado digital

2. Actualizar `.env`:
   ```
   SAT_SIGNING_ENDPOINT=https://api.sat.gob.mx/firmar
   SAT_SIGNING_API_KEY=your-api-key
   SAT_SIGNING_CERT_PATH=/path/to/cert.pem
   ```

3. En desarrollo, el módulo usa simulación (SANDBOX) si `.env` no tiene credenciales.

#### Estructura de rutas de API

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/correspondencia?id_expediente=X` | Listar correspondencias |
| POST | `/api/correspondencia` | Crear nueva |
| GET | `/api/correspondencia/{id}` | Ver detalle |
| PUT | `/api/correspondencia/{id}` | Editar (solo borrador) |
| DELETE | `/api/correspondencia/{id}` | Eliminar (solo borrador) |
| POST | `/api/correspondencia/{id}/firmar` | Firmar electrónicamente |
| GET | `/api/correspondencia/{id}/pdf` | Descargar PDF |

#### Base de datos
Tablas principales:
- `correspondencias` — documentos de correspondencia
- `correspondencia_firmas` — firmas electrónicas SAT
- `correspondencia_anexos` — relación con PDFs (opcional)
- `correspondencia_auditorias` — trazabilidad de acciones

Para resetting o troubleshooting:
```bash
mysql -u root gda < database/sql/004-correspondencias.sql
```

### Documentación de features

Cada feature importante se documenta en `spec/features/NNN-nombre/`:
- `spec.md` — qué hace
- `plan.md` — cómo se implementa
- `tasks.md` — checklist de implementación

Ver `spec/constitution/` para:
- `mission.md` — misión y principios
- `tech-stack.md` — stack y convenciones
- `roadmap.md` — estado de features

### Soporte y contacto

Para dudas sobre configuración, features o bugs, consultar documentación de spec o contactar al equipo de desarrollo.

