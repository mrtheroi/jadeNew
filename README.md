# Jade
Sistema de gestion financiera y reportes de ventas para restaurantes multi-unidad (Jade, Fuego Ambar, KIN). Permite registrar ventas diarias mediante extraccion automatica de PDFs via LlamaIndex Cloud, controlar gastos e insumos, y generar reportes financieros exportables en Excel y PDF.

## Requisitos
- PHP 8.2+
- Composer
- Node.js y npm
- MySQL o MariaDB

## Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

## Configuracion
Configura las siguientes variables de entorno en tu archivo `.env`:

### Base de datos
```
DB_CONNECTION=mysql
DB_HOST=tu_host
DB_PORT=3306
DB_DATABASE=tu_database
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

### LlamaIndex Cloud (Extraccion de datos)
```
LLAMA_INDEX_BASE_URL=https://api.cloud.llamaindex.ai/api/v2
LLAMA_INDEX_API_KEY=tu_api_key
LLAMA_INDEX_CONFIGURATION_ID=tu_configuration_id
```

El `configuration_id` se obtiene desde el dashboard de LlamaIndex Cloud. Tambien acepta `LLAMA_INDEX_EXTRACTION_AGENT_ID` como fallback.

## Ejecutar el proyecto

```bash
composer run dev
```

O por separado:

```bash
php artisan serve
npm run dev
```

Para procesar los jobs en la cola (necesario para la extraccion de PDFs):

```bash
php artisan queue:work
```

## Roles y Permisos
El sistema maneja tres roles con permisos jerarquicos:

| Rol | Permisos | Descripcion |
|-----|----------|-------------|
| Super | super, admin, user | Acceso total al sistema |
| Admin | admin, user | Administracion de catalogos, gastos, ventas |
| User | user | Operaciones basicas |

Los roles y permisos se crean con el seeder `RolSeeder` al ejecutar `php artisan migrate --seed`.

## Unidades de Negocio
El sistema soporta tres unidades de negocio definidas en el enum `App\Domain\BusinessUnit`:
- **Jade**
- **Fuego Ambar**
- **KIN**

Cada registro de ventas, gastos, categorias e ingresos esta asociado a una unidad de negocio.

## Rutas

| Metodo | Ruta | Componente | Descripcion |
|--------|------|------------|-------------|
| GET | `/` | login | Pagina de inicio / login |
| GET | `/dashboard` | SalesDashboard | Dashboard principal de ventas y KPIs |
| GET | `/ventas` | DailySalesController | Listado y gestion de ventas diarias |
| GET | `/supplies` | SuppliesController | CRUD de gastos e insumos |
| GET | `/categories` | CategoryController | Catalogo de categorias de gasto |
| GET | `/expense-types` | ExpenseTypeController | Catalogo de tipos de gasto |
| GET | `/users` | UserController | Gestion de usuarios |
| GET | `/dashboard/ventas/export/excel` | DashboardExportController | Exportar reporte de ventas a CSV |
| GET | `/dashboard/ventas/export/pdf` | DashboardExportController | Exportar reporte de ventas a PDF |
| GET | `/dashboard/estado-resultados/export/excel` | DashboardExportController | Exportar estado de resultados a CSV |
| GET | `/dashboard/estado-resultados/export/pdf` | DashboardExportController | Exportar estado de resultados a PDF |
| POST | `/api/webhook/llama` | LlamaWebhookController | Webhook de LlamaIndex (sin auth) |
| GET | `/settings/profile` | Volt settings.profile | Perfil del usuario |
| GET | `/settings/password` | Volt settings.password | Cambio de contrasena |
| GET | `/settings/appearance` | Volt settings.appearance | Apariencia del sistema |
| GET | `/settings/two-factor` | Volt settings.two-factor | Configuracion 2FA |

Todas las rutas (excepto `/`) requieren autenticacion.

## Estructura

```
app/
├── Actions/
│   └── Fortify/
│       ├── CreateNewUser.php                  # Accion para registro de nuevos usuarios
│       ├── PasswordValidationRules.php        # Reglas de validacion de contrasenas
│       └── ResetUserPassword.php              # Accion para resetear contrasena
├── Application/
│   └── Supplies/
│       └── SuppliesQuery.php                  # Query builder con filtros para tabla de supplies
├── Domain/
│   └── BusinessUnit.php                       # Enum: Jade, Fuego Ambar, KIN
├── Enums/                                     # (reservado para futuros enums)
├── Exports/
│   ├── ExpensesReportExport.php               # Export multi-hoja de gastos (Excel)
│   └── Sheets/
│       ├── ExpensesDetailSheet.php            # Hoja de detalle de gastos
│       └── ExpensesSummarySheet.php           # Hoja de resumen de gastos
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php                     # Controlador base
│   │   ├── DashboardExportController.php      # Exportacion del dashboard a Excel y PDF
│   │   └── Api/
│   │       └── LlamaWebhookController.php     # Receptor del webhook de LlamaIndex
│   └── Middleware/
│       └── EnsureIdempotency.php              # Proteccion contra webhooks duplicados
├── Jobs/
│   └── ProcessLlamaExtractionJob.php          # Job en cola para procesar extraccion via LlamaIndex
├── Livewire/
│   ├── Actions/
│   │   └── Logout.php                         # Accion de cierre de sesion
│   ├── CategoryController.php                 # CRUD de categorias de gasto
│   ├── ConfirmModal.php                       # Modal reutilizable de confirmacion
│   ├── DailySalesController.php               # Listado, importacion y gestion de ventas diarias
│   ├── ExpenseTypeController.php              # CRUD de tipos de gasto
│   ├── Notification.php                       # Componente de notificaciones
│   ├── SalesDashboard.php                     # Dashboard principal: KPIs, graficas, filtros por periodo
│   ├── SuppliesController.php                 # CRUD de gastos/insumos con exportacion
│   └── UserController.php                     # CRUD de usuarios con roles y permisos
├── Models/
│   ├── CashExtraction.php                     # Corte de caja: desglose efectivo/tarjeta por turno
│   ├── Category.php                           # Categoria de gasto (unidad, tipo, proveedor)
│   ├── DailySale.php                          # Venta diaria: ventas, metodos de pago, propinas, datos operativos
│   ├── ExpenseType.php                        # Tipo de gasto (Luz, Renta, etc.)
│   ├── IncomePeriod.php                       # Ingreso mensual por unidad de negocio
│   ├── Supply.php                             # Gasto/insumo con estatus de pago
│   └── User.php                               # Usuario con roles Spatie y soporte 2FA
├── Providers/
│   ├── AppServiceProvider.php                 # Service provider principal
│   ├── FortifyServiceProvider.php             # Configuracion de Fortify (auth, 2FA)
│   └── VoltServiceProvider.php                # Registro de componentes Volt
└── Services/
    ├── LlamaIndexService.php                  # Cliente HTTP para LlamaIndex Cloud API
    ├── DailySaleExtractionMapper.php          # Mapeo de JSON LlamaIndex a campos de daily_sales
    └── Reports/
        └── ExpensesReportService.php          # Logica de generacion de reportes de gastos (Excel/PDF)

config/
├── app.php                                    # Configuracion general de la aplicacion
├── auth.php                                   # Guards y providers de autenticacion
├── cache.php                                  # Configuracion de cache
├── database.php                               # Conexiones de base de datos
├── dompdf.php                                 # Configuracion de DomPDF para generacion de PDFs
├── filesystems.php                            # Discos de almacenamiento
├── fortify.php                                # Configuracion de Fortify (features, views, 2FA)
├── logging.php                                # Canales de logging
├── mail.php                                   # Configuracion de correo
├── permission.php                             # Configuracion de Spatie Permission
├── queue.php                                  # Configuracion de colas
├── services.php                               # Credenciales de servicios externos (Extract)
└── session.php                                # Configuracion de sesiones

database/
├── factories/
│   ├── DailySaleFactory.php                   # Factory para ventas diarias
│   └── UserFactory.php                        # Factory para usuarios
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2025_09_02_..._add_two_factor_columns_to_users_table.php
│   ├── 2025_12_01_..._create_permission_tables.php
│   ├── 2025_12_08_..._create_cash_extractions_table.php
│   ├── 2025_12_09_..._create_expense_types_table.php
│   ├── 2025_12_09_..._create_categories_table.php
│   ├── 2025_12_09_..._create_supplies_table.php
│   ├── 2026_02_01_..._create_income_periods_table.php
│   └── 2026_03_04_..._create_daily_sales_table.php
└── seeders/
    ├── DatabaseSeeder.php                     # Seeder principal
    └── RolSeeder.php                          # Roles (Super, Admin, User) y permisos

resources/views/
├── components/
│   ├── action-message.blade.php               # Mensaje de accion temporal
│   ├── app-logo.blade.php                     # Logo de la aplicacion
│   ├── app-logo-icon.blade.php                # Icono del logo
│   ├── auth-header.blade.php                  # Header de paginas de autenticacion
│   ├── auth-session-status.blade.php          # Mensajes de estado de sesion
│   ├── layouts/
│   │   ├── app.blade.php                      # Layout principal de la aplicacion
│   │   ├── app/
│   │   │   ├── header.blade.php               # Header con navegacion superior
│   │   │   └── sidebar.blade.php              # Sidebar con menu de navegacion
│   │   ├── auth.blade.php                     # Layout base de autenticacion
│   │   └── auth/
│   │       ├── card.blade.php                 # Layout auth estilo card
│   │       ├── simple.blade.php               # Layout auth simple
│   │       └── split.blade.php                # Layout auth split (dos columnas)
│   ├── nav-item.blade.php                     # Item de navegacion del sidebar
│   ├── placeholder-pattern.blade.php          # Patron de placeholder/skeleton
│   └── settings/
│       └── layout.blade.php                   # Layout de paginas de configuracion
├── exports/
│   └── dashboard-ventas-pdf.blade.php         # Template PDF del dashboard de ventas
├── flux/
│   ├── icon/
│   │   ├── book-open-text.blade.php           # Icono personalizado Flux
│   │   ├── chevrons-up-down.blade.php         # Icono personalizado Flux
│   │   ├── folder-git-2.blade.php             # Icono personalizado Flux
│   │   └── layout-grid.blade.php              # Icono personalizado Flux
│   └── navlist/
│       └── group.blade.php                    # Override de grupo de navegacion Flux
├── livewire/
│   ├── auth/
│   │   ├── confirm-password.blade.php         # Confirmacion de contrasena
│   │   ├── forgot-password.blade.php          # Recuperar contrasena
│   │   ├── login.blade.php                    # Inicio de sesion
│   │   ├── register.blade.php                 # Registro de usuario
│   │   ├── reset-password.blade.php           # Resetear contrasena
│   │   ├── two-factor-challenge.blade.php     # Desafio 2FA
│   │   └── verify-email.blade.php             # Verificacion de email
│   ├── category-controller.blade.php          # Vista de categorias
│   ├── daily-sales-controller.blade.php       # Vista de ventas diarias
│   ├── expense-type.blade.php                 # Vista de tipos de gasto
│   ├── modals/
│   │   ├── cash.blade.php                     # Modal de corte de caja
│   │   ├── category-form.blade.php            # Modal formulario de categoria
│   │   ├── confirm.blade.php                  # Modal de confirmacion generica
│   │   ├── detail-daily-sales.blade.php       # Modal detalle de venta diaria
│   │   ├── detail-supplies.blade.php          # Modal detalle de gasto
│   │   ├── expense-type-form.blade.php        # Modal formulario de tipo de gasto
│   │   ├── form-daily-sales.blade.php         # Modal formulario de venta diaria
│   │   ├── form-incomes.blade.php             # Modal formulario de ingresos
│   │   ├── form-supplies.blade.php            # Modal formulario de gastos
│   │   ├── form-user.blade.php                # Modal formulario de usuario
│   │   └── notification.blade.php             # Modal de notificacion
│   ├── sales-dashboard.blade.php              # Vista del dashboard de ventas
│   ├── settings/
│   │   ├── appearance.blade.php               # Configuracion de apariencia
│   │   ├── delete-user-form.blade.php         # Formulario para eliminar cuenta
│   │   ├── password.blade.php                 # Cambio de contrasena
│   │   ├── profile.blade.php                  # Edicion de perfil
│   │   ├── two-factor.blade.php               # Configuracion 2FA
│   │   └── two-factor/
│   │       └── recovery-codes.blade.php       # Codigos de recuperacion 2FA
│   ├── supplies-controller.blade.php          # Vista de gastos/insumos
│   └── users.blade.php                        # Vista de gestion de usuarios
├── partials/
│   ├── head.blade.php                         # Head HTML (meta, scripts, styles)
│   └── settings-heading.blade.php             # Encabezado de paginas de settings
├── reports/
│   └── expenses-pdf.blade.php                 # Template PDF del reporte de gastos
├── dashboard.blade.php                        # Vista base del dashboard
└── welcome.blade.php                          # Pagina de bienvenida

routes/
├── api.php                                    # Rutas API (webhook LlamaIndex)
├── console.php                                # Comandos de consola
└── web.php                                    # Rutas web (auth, dashboard, CRUD, exports, settings)

tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php             # Tests de login/logout
│   │   ├── EmailVerificationTest.php          # Tests de verificacion de email
│   │   ├── PasswordConfirmationTest.php       # Tests de confirmacion de contrasena
│   │   ├── PasswordResetTest.php              # Tests de reseteo de contrasena
│   │   ├── RegistrationTest.php               # Tests de registro
│   │   └── TwoFactorChallengeTest.php         # Tests de autenticacion 2FA
│   ├── DailySalesTest.php                     # Tests de ventas diarias
│   ├── DashboardTest.php                      # Tests del dashboard
│   ├── ExampleTest.php                        # Test de ejemplo
│   └── Settings/
│       ├── PasswordUpdateTest.php             # Tests de actualizacion de contrasena
│       ├── ProfileUpdateTest.php              # Tests de actualizacion de perfil
│       └── TwoFactorAuthenticationTest.php    # Tests de configuracion 2FA
├── Unit/
│   └── ExampleTest.php                        # Test unitario de ejemplo
├── Pest.php                                   # Configuracion de Pest
└── TestCase.php                               # Caso base de tests
```

## Tests

```bash
php artisan test --compact
```

Filtrar por nombre:

```bash
php artisan test --compact --filter=DailySales
php artisan test --compact --filter=Dashboard
php artisan test --compact --filter=Authentication
php artisan test --compact --filter=TwoFactor
```

Ejecutar un archivo especifico:

```bash
php artisan test --compact tests/Feature/DailySalesTest.php
```

## Formateo de Codigo

```bash
# Formatear archivos modificados
vendor/bin/pint --dirty

# Formatear todo el proyecto
vendor/bin/pint
```

## Stack Tecnologico
- **Backend:** PHP 8.4, Laravel 12, Eloquent ORM
- **Frontend:** Livewire 3, Volt 1, Flux UI 2 (free), Tailwind CSS 4, Vite 7
- **Autenticacion:** Laravel Fortify (con soporte 2FA)
- **Autorizacion:** Spatie Laravel Permission (roles y permisos)
- **Excel:** Maatwebsite Excel 3.1 (importacion y exportacion)
- **PDF:** Barryvdh DomPDF 3.1
- **Alertas:** Livewire Alert (jantinnerezo)
- **Testing:** Pest 3, PHPUnit 11
- **Formateo:** Laravel Pint

## Licencia
MIT
