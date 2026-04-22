# Changelog

Todos los cambios notables del proyecto Jade serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.1.1] - 2026-04-21

### Corregido

- **Estado del buscador de categorías en modal de insumos**: `closeModal()` y `create()` no reseteaban `categorySearch` — al reabrir el modal aparecía el texto de búsqueda anterior
- **Query innecesaria en selección de categoría**: `selectCategory()` hacía un `Category::findOrFail()` cuando los datos ya estaban cargados en `$categoryResults`. Ahora usa `collect()->firstWhere()` sin consultar la base de datos
- **`wire:model.live` en campos del formulario de insumos**: 6 campos del modal (fecha de pago, monto, ajuste, método de pago, estado, notas) disparaban queries a la BD en cada interacción del usuario. Cambiados a `wire:model` (diferido)

### Cambiado

- **Debounce de búsqueda de categorías**: Aumentado de 100ms a 300ms para reducir round-trips innecesarios al servidor
- **Resultados del buscador de categorías**: Mapeados a array plano en `updatedCategorySearch()`, eliminando serialización de modelos Eloquent en estado de Livewire
- **Búsqueda de categorías case-insensitive**: `SuppliesQuery::searchCategories()` convierte el término a mayúsculas antes del `LIKE`, permitiendo búsquedas en minúsculas cuando los datos están guardados en mayúsculas

### Agregado

- **Indicador de carga en buscador de categorías**: `wire:loading` muestra "Buscando…" mientras se procesa la consulta al servidor
- **Cierre con Escape del dropdown de categorías**: El dropdown se cierra al presionar Escape sin requerir click fuera
- **`wire:key` en resultados del dropdown**: Previene problemas de diffing de Livewire en listas dinámicas
- **Mutators de mayúsculas en modelos**: `Category` (`business_unit`, `expense_name`, `provider_name`) y `ExpenseType` (`expense_type_name`) normalizan los valores a mayúsculas en la capa del modelo, aplicando para formularios, seeders, imports y cualquier otra fuente de datos

### Eliminado

- **`resources/views/livewire/modals/form-supplies.blade.php`**: Archivo con implementación Alpine.js alternativa del formulario de insumos que no estaba incluida en ninguna ruta ni componente

---

## [1.1.0] - 2026-03-31

### Added
- **Soporte de PLANTA ALTA en extracción de ventas**: `DailySaleExtractionMapper` ahora suma todas las áreas de venta (COMEDOR + PLANTA ALTA) en lugar de usar solo COMEDOR. Cuando PLANTA ALTA tiene datos, se suman a alimentos, bebidas, otros, subtotal, IVA, total, número de personas, cuentas y cantidad de productos. El promedio por persona se recalcula con los totales combinados
- **Test de cobertura** para la suma de PLANTA ALTA en el mapper de extracción

## [1.0.0] - 2026-03-21

### Added
- **Dashboard de ventas** con KPIs: ventas totales, subtotal, IVA, utilidad y total de gastos
- **Desglose de ventas** por área (alimentos, bebidas, otros) y métodos de pago (efectivo, débito, crédito, crédito cliente)
- **Métricas operativas**: número de personas, cuentas, ticket promedio y cantidad de productos
- **Análisis por turno** (turno 1 y turno 2) en el dashboard
- **Exportación de reportes** a Excel (CSV) y PDF para ventas y estado de resultados
- **Gestión de ventas diarias** (`/ventas`) con CRUD completo
- **Integración con LlamaIndex Cloud** para extracción automática de datos desde PDFs de tickets POS
- **Webhook de LlamaIndex** con middleware de idempotencia para procesamiento confiable
- **Mapeo automático de datos** extraídos a campos de ventas diarias via `DailySaleExtractionMapper`
- **Módulo de reconciliación** de ventas con modal dedicado, campos de estado y tracking
- **Modelo CashExtraction** para cortes de caja y validación de efectivo por turno
- **Gestión de gastos/insumos** (`/supplies`) con CRUD, recibos y filtros por categoría, tipo de pago y estado
- **Carga de imágenes de recibos** en insumos con almacenamiento público y visualización en modal
- **Catálogo de categorías** (`/categories`) por unidad de negocio con tipo de gasto y proveedor
- **Catálogo de tipos de gasto** (`/expense-types`) con activación/desactivación
- **Gestión de periodos de ingreso** (`IncomePeriod`) para tracking de ingresos mensuales
- **Gestión de usuarios** (`/users`) con asignación de roles (Super, Admin, User) via Spatie Permission
- **Autenticación completa** con Laravel Fortify: login, registro, recuperación de contraseña
- **Autenticación de dos factores** (2FA) con TOTP
- **Configuración de perfil** de usuario: datos personales, contraseña, 2FA y apariencia
- **Soporte multi-unidad de negocio**: Jade, Fuego Ambar, KIN
- **Enum BusinessUnit** para manejo tipado de unidades de negocio
- **Servicio de reportes de gastos** (`ExpensesReportService`) con generación multi-hoja en Excel
- **Query builder de insumos** (`SuppliesQuery`) con filtros encadenables
- **Suite de tests con Pest 3**: autenticación, ventas diarias, dashboard, recibos, configuración de usuario
- **UI con Flux UI 2** (edición gratuita) y Tailwind CSS 4
- **Componentes Livewire 3** con Volt para páginas interactivas
