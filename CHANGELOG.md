# Changelog

Todos los cambios notables del proyecto Jade serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.5.0] - 2026-05-08

### Agregado

- **Generación retroactiva de Órdenes de Compra**: el modal de generación ahora incluye un selector de fecha (`<input type="date">`) que permite consolidar compras de cualquier día pasado, no sólo del día actual. Por defecto la fecha es hoy y se puede modificar libremente; el `max` está topado al día de hoy para evitar OCs futuras
  - **Preview reactivo**: al cambiar la fecha en el modal, la cantidad de compras y el monto total se recalculan automáticamente vía `wire:model.live` y el método `updatedOcPreviewDate()` del `SuppliesController`
  - **Botón confirmar deshabilitado**: si para la fecha elegida no hay compras elegibles (sin OC asignada), el botón «Confirmar y generar OC» queda deshabilitado y se muestra un aviso indicando probar otra fecha
  - **Modal abre siempre**: ya no se rechaza la apertura del modal cuando hoy no tiene compras elegibles; se abre igual para que el usuario pueda explorar otras fechas

### Cambiado

- **Botón «Generar OC»**: el botón en la vista de Supplies pasó de «Generar OC del día» a «Generar OC» para reflejar que ya no está atado al día actual
- **PDF de Orden de Compra**: la línea de firma «Recibió / Aprobó» ya no muestra «Contabilidad» hardcodeado. La etiqueta queda limpia para firma manuscrita, evitando comunicar un flujo de aprobación que el sistema no implementa

### Corregido

- **Bug de selector de unidad en Supplies**: el `<select>` de unidad de negocio no tenía opción vacía y `business_unit` arrancaba en `''`. Esto provocaba que el navegador mostrara visualmente «Jade» pero el modelo en el servidor siguiera vacío, ocultando el botón «Generar OC» hasta cambiar a otra unidad y volver. El `mount()` ahora inicializa `business_unit` al primer caso del enum (`BusinessUnit::cases()[0]->value`), eliminando el estado fantasma. `resetFilters()` también respeta este default
- **Validación de `payment_date` en alta de compras**: la regla pasó de `nullable|date` a `required|date` en `SupplyForm`. En el mundo nuevo de OCs, una compra sin fecha jamás puede entrar en una OC (`eligibleSupplies()` filtra con `whereDate('payment_date')`), por lo que el dato faltante quedaba en el limbo. Ahora se exige al guardar

---

## [1.4.1] - 2026-05-08

### Corregido

- **Orden de ejecución de las migrations de OC**: las dos migrations introducidas en 1.4.0 (`create_purchase_orders_table` y `add_purchase_order_id_to_supplies_table`) compartían exactamente el mismo timestamp, por lo que Laravel las ordenaba alfabéticamente y ejecutaba primero la `add_...` (que crea la FK hacia `purchase_orders`). Como la tabla `purchase_orders` aún no existía, fallaba con `SQLSTATE[42P01]: Undefined table: relation "purchase_orders" does not exist`. El fix renombra la migration de `add_...` con timestamp `+1` segundo para garantizar que `create_purchase_orders_table` se ejecute primero. PostgreSQL maneja DDL transaccional, así que el fallo previo no dejó cambios parciales en la BD: ambas migrations corren limpio una vez aplicado el fix.

---

## [1.4.0] - 2026-05-08

### Agregado

- **Órdenes de Compra (OC) postmortem por día y unidad**: nuevo flujo en el módulo de Compras y Pagos para consolidar las compras del día en una OC interna por unidad de negocio
  - **Generación**: botón «Generar OC del día» en la vista de Supplies que requiere unidad activa. Modal de preview con cantidad de compras y monto total antes de confirmar. Al confirmar, todas las compras del día y unidad sin OC asignada quedan agrupadas en una nueva `PurchaseOrder` que se crea con `status='closed'`
  - **Numeración**: formato `OC-{Unidad}-{YYYY-MM-DD}` (ej: `OC-Jade-2026-05-08`). Si ya existe una OC para esa combinación, se agrega sufijo `-2`, `-3`, etc.
  - **Inmutabilidad**: las compras asociadas a una OC cerrada quedan bloqueadas (no se pueden editar ni eliminar). Visualmente aparece un ícono de candado y un badge con el número de OC en la fila
  - **Anulación**: botón «Anular» que cambia la OC a `status='cancelled'` y libera todos los supplies asociados (`purchase_order_id = null`), permitiendo nuevamente su edición
- **Módulo nuevo «Órdenes de Compra»** (`/ordenes-compra`):
  - Nuevo item en sidebar bajo grupo Platform
  - Listado paginado con filtros (search en número y notas, unidad, estado, rango de fechas)
  - Cards de resumen: total facturado en OCs cerradas + breakdown por unidad + card aparte de OCs anuladas
  - Modal de detalle con desglose de las compras agrupadas por proveedor, subtotales y total general
  - Botones de Anular y Exportar PDF en el detalle y en la fila del listado
- **PDF de OC** (`/ordenes-compra/{id}/pdf`): documento interno con encabezado (número, fecha, unidad, estado), tabla de compras agrupadas por proveedor con subtotales, total general, área de firmas (generó/recibió) y footer con timestamp
- **Tabla `purchase_orders`** con: `oc_number` (unique), `oc_date`, `business_unit`, `total_amount` y `total_items` denormalizados, `notes`, `status` (closed/cancelled), `created_by` (FK a users con auditoría), `closed_at`, timestamps. Índices sobre business_unit, oc_date y status
- **Columna `purchase_order_id`** en `supplies`: FK nullable a `purchase_orders` con `nullOnDelete`. Las 291 compras existentes quedan con `null` (lo decidido para histórico)
- **Estructura del módulo** siguiendo la convención modular:
  - `app/Application/PurchaseOrders/PurchaseOrdersQuery.php` — query layer con filtros, totales y breakdown
  - `app/Livewire/Expenses/PurchaseOrdersController.php` — Livewire del listado, detalle y anulación
  - `app/Models/PurchaseOrder.php` con scopes (`closed`, `cancelled`), relaciones (`supplies`, `creator`) y helpers (`isClosed`, `isCancelled`)
  - `app/Services/PurchaseOrderGenerator.php` — servicio con métodos `generate()`, `cancel()` y `eligibleSupplies()`. Usa transacción para atomicidad
  - `app/Http/Controllers/PurchaseOrderPdfController.php` + `resources/views/reports/purchase-order-pdf.blade.php` — generación con dompdf
- **`tests/Feature/PurchaseOrdersTest.php`**: 16 tests / 29 assertions cubriendo generación (con sufijo de regeneración, exclusión de compras con OC, manejo de fecha y unidad), inmutabilidad (`isLocked` con OC cerrada/cancelada/sin OC), anulación que libera supplies, y flujo Livewire completo (preview, confirmación, bloqueo de edit/delete)

### Cambiado

- **`Supply::isLocked()`**: nuevo helper en el modelo que devuelve `true` cuando la compra pertenece a una OC cerrada
- **`SuppliesController::edit()` y `destroy()`**: ahora chequean `isLocked()` antes de permitir la operación. Si está bloqueada, devuelven una notificación de warning explicando que hay que anular la OC para liberarla
- **`SuppliesQuery::base()`**: agrega `purchaseOrder` al eager loading para evitar N+1 al renderizar el ícono de candado en el listado
- **Vista de Supplies**: cada fila muestra el badge del número de OC y un ícono de candado cuando aplica. Los botones de editar/eliminar quedan deshabilitados visualmente (`opacity-30`, cursor not-allowed) con tooltip «Bloqueada por OC»

---

## [1.3.0] - 2026-05-08

### Agregado

- **Módulo de Recursos Humanos — CRUD de Empleados**: primer módulo del área de RRHH, sentando la base para los siguientes (Asistencia, Vacaciones, Nómina)
  - Listado paginado con búsqueda (nombre, email, número de empleado, CURP), filtro por unidad de negocio (Jade/Fuego Ambar/KIN) y filtro por estado (activos / inactivos / todos)
  - Card de total de empleados activos + breakdown por unidad de negocio + card aparte de empleados dados de baja cuando aplica
  - Modal de alta/edición agrupado en 4 secciones visuales: Identificación, Datos personales, Datos laborales, Contacto de emergencia
  - Modal de detalle completo con todos los campos del empleado y edad calculada automáticamente desde `birth_date`
  - Estado del empleado con manejo de baja: campo `is_active` + `terminated_at` (requerido cuando `is_active=false`); al reactivar un empleado se limpia automáticamente `terminated_at`
- **Tabla `employees`** con 21 campos editables agrupados en identificación, datos personales (`birth_date`, género, estado civil, nacionalidad, hijos, dirección), datos laborales (`business_unit`, departamento, gerente, fecha de ingreso) y contacto de emergencia (nombre, teléfono, parentesco). Edad NO se persiste, se calcula desde `birth_date` con accessor del modelo
- **Estructura del módulo** siguiendo la convención modular establecida en 1.2.1:
  - `app/Application/HumanResources/Employees/EmployeesQuery.php` — query layer con métodos `base()`, `totalActive()`, `totalInactive()`, `totalsByUnit()`
  - `app/Livewire/HumanResources/EmployeesController.php` — componente Livewire del listado y CRUD
  - `app/Livewire/HumanResources/Forms/EmployeeForm.php` — formulario con validaciones, regla `terminated_at` requerido cuando `is_active=false`
  - `app/Models/Employee.php` con scopes (`active`, `inactive`) y accessor `age`
- **Ruta** `GET /rrhh/empleados` con nombre `rrhh.empleados`
- **Sidebar**: nuevo grupo "Recursos Humanos" con el item "Empleados", separado del grupo "Platform" para diferenciar dominios
- **`tests/Feature/EmployeesCrudTest.php`**: 18 tests / 35 assertions cubriendo modelo (accessor de edad, scopes), filtros del query (search, business_unit, status, totales), CRUD del componente Livewire (alta, edición, baja, validaciones críticas) y limpieza de filtros

### Notas

- **Edad calculada en UI**: por decisión de producto la edad se obtiene desde `birth_date` con `Carbon::diffInYears`. No se persiste para evitar inconsistencias temporales
- **`gender` y `marital_status`**: se manejan como strings con validación `in:` por simplicidad. Si en el futuro se reportan estadísticamente, se pueden migrar a enums PHP
- **Permisos**: el módulo accede al admin del sistema sin filtros de roles. Cuando entre el módulo de Asistencia se planeará el sistema de permisos por unidad

---

## [1.2.1] - 2026-05-08

### Cambiado

- **Reorganización modular de Livewire**: componentes y vistas Blade reagrupados en subcarpetas por dominio para soportar el crecimiento del proyecto, especialmente la incorporación del próximo módulo de Recursos Humanos
  - `app/Livewire/Sales/` — `SalesDashboard`, `DailySalesController` y sus Forms (`DailySaleUploadForm`, `IncomeForm`, `ReconciliationForm`)
  - `app/Livewire/Expenses/` — `SuppliesController`, `CategoryController`, `ExpenseTypeController` y sus Forms (`SupplyForm`, `CategoryForm`, `ExpenseTypeForm`)
  - `app/Livewire/Users/` — `UserController` y `UserForm`
  - `app/Livewire/HumanResources/` — estructura vacía con `.gitkeep`, lista para el próximo módulo
  - Vistas Blade espejadas en `resources/views/livewire/{sales,expenses,users,human-resources}/`
- **Llamadas a `view()` explícitas**: cada controller referencia su vista con el path completo del nuevo namespace (`view('livewire.{dominio}.{nombre}', ...)`) sin depender de la auto-resolución de Livewire
- **URLs públicas y nombres de rutas inalterados**: el refactor es 100% interno — sidebar, bookmarks y comportamiento del usuario final quedan intactos
- **Carpetas shared en raíz**: `app/Livewire/Actions/`, `app/Livewire/Concerns/`, `ConfirmModal.php` y `Notification.php` se mantienen sin namespace de dominio porque son utilitarios genéricos compartidos por todos los módulos
- **Identificado código muerto**: `IncomeForm` se movió a `app/Livewire/Sales/Forms/` pero no es usado por ningún componente; queda pendiente de revisión y posible eliminación en una versión futura

---

## [1.2.0] - 2026-05-08

### Agregado

- **Filtro por tipo de gasto en Supplies**: dropdown para acotar la tabla y las cards al tipo seleccionado, en cascada con la unidad de negocio activa
- **Filtro por categoría en Supplies**: dropdown en cascada que se acota al tipo seleccionado, la unidad y los demás filtros activos
- **Cards de breakdown por tipo de gasto**: el total general se desglosa en N cards (una por cada tipo presente en el filtro), ordenadas por monto. La suma de las cards equivale al total general, dando coherencia entre cabecera y tabla
- **Card aparte de Cancelados**: muestra monto y cantidad de registros con `status='cancelado'` del filtro vigente. Aparece solo si hay cancelados; no suma al total general (visibilidad sin distorsionar el reporte de gasto efectivo)
- **Cascada total en dropdowns**: los dropdowns de tipo y categoría solo muestran opciones que tienen registros con todos los filtros vigentes (search + unidad + período). La selección actual se mantiene siempre disponible aunque no matchee con los filtros, evitando que "desaparezca"
- **`tests/Feature/SuppliesFiltersTest.php`**: 16 tests / 28 assertions cubriendo filtros nuevos, breakdown por tipo, separación de cancelados y cascada de dropdowns en backend y componente Livewire
- **`tests/Unit/PeriodRangeTest.php`**: 7 tests de regresión para el fix de overflow en febrero (commit c6f42c0)

### Cambiado

- **Cards principales en Supplies**: reemplazadas las cards "una por unidad de negocio" por una card de TOTAL GENERAL prominente + N cards de breakdown por tipo + card de Cancelados aparte cuando aplica
- **`SuppliesQuery::base()`**: ahora acepta `expense_type_id` y `category_id` como filtros opcionales además de los existentes
- **Cancelados en cards**: el TOTAL GENERAL y el breakdown por tipo excluyen explícitamente `status='cancelado'`. La tabla los sigue mostrando con su badge para mantener visibilidad del estado
- **Exports Excel/PDF**: respetan los nuevos filtros de tipo y categoría — lo que ves en la tabla es lo que se exporta

### Eliminado

- **`SuppliesQuery::totalsByUnit()`**: reemplazado por `totalGeneral()`, `totalsByExpenseType()`, `cancelledTotal()` y `cancelledCount()`. Sin uso fuera del controller de Supplies

---

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
