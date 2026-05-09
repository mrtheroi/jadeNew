# Changelog

Todos los cambios notables del proyecto Jade serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.8.0] - 2026-05-09

### Agregado

- **Numeración automática de empleados (formato `WYB-XXXX`)**: el campo `employee_number` ya no se teclea — se asigna automáticamente al guardar un empleado nuevo, en formato `WYB-0001`, `WYB-0002`, etc. con padding de 4 dígitos. El servicio `App\Services\EmployeeNumberGenerator` calcula el siguiente número buscando el `MAX(employee_number)` que matchee el patrón `WYB-XXXX` y sumando 1; si no existe ninguno, arranca en `WYB-0001`. Los empleados históricos cargados a mano antes de esta convención **conservan su número original** y son ignorados por el generador
  - **Form**: el campo en el modal queda `readonly` con texto auxiliar "Formato WYB-XXXX, asignado automáticamente. No es editable"
  - **Edición**: en update el número original se conserva intacto (NO se regenera)
- **Salario en empleados** con privacidad por rol: nuevos campos `salary_gross`, `salary_net` y `salary_period` (`Semanal` / `Quincenal` / `Mensual`). Los tres son nullable a nivel BD pero opcionales a nivel form (no requeridos para crear empleado)
  - **Privacidad — gate `view-salary`**: registrado en `AppServiceProvider::boot()`, retorna true solo si el usuario tiene rol `Super` o `Admin`. Aplica en dos puntos:
    - **Frontend**: la sección "Salario" del modal queda envuelta en `@can('view-salary')` — usuarios con rol básico no ven los inputs
    - **Backend (defensa en profundidad)**: `EmployeesController::save()` descarta los campos de salario del array validado si el usuario carece de la gate, aunque venga manipulado por el cliente
- **Beneficiario único en empleados** (LFT art. 501): nuevos campos planos `beneficiary_name`, `beneficiary_relationship`, `beneficiary_phone`, `beneficiary_percentage` (default 100). Se rendean como tabla en la cláusula II.c del contrato. Si no hay datos, el contrato muestra "Sin información a mostrar"
- **Puesto del empleado**: nuevo campo `position` (string nullable). Se usa en la cláusula tercera del contrato laboral
- **Datos de empresa por unidad de negocio (`config/company.php`)**: estructura `units` indexada por nombre de unidad (`Jade`, `Jade Orgánico`, `KIN`). Cada entrada contiene razón social, nombre comercial, apoderado legal, domicilio, RFC, objeto social y ciudad de firma. Solo `Jade` está cargada con datos reales (de Café Jade Restaurante en Palenque, Chiapas); `Jade Orgánico` y `KIN` quedan con placeholders `[DUMMY]` para reemplazar cuando el negocio confirme la información real. El contrato del empleado lee el bloque correspondiente vía `Employee::companyData()` según la `business_unit` del empleado
- **Generación on-demand del Contrato Individual de Trabajo (PDF)**: nuevo endpoint `GET /rrhh/empleados/{employee}/contrato/pdf` (named `rrhh.empleados.contrato.pdf`) que genera con DomPDF el contrato laboral del empleado, populando todas las cláusulas con datos reales (datos personales, fecha de ingreso, puesto, salario diario calculado, beneficiario, datos de la empresa según unidad). Botón "Descargar contrato" (icono `fa-file-pdf`) agregado a cada fila del listado de empleados
  - **Helper `Employee::dailySalary()`**: calcula salario diario dividiendo `salary_gross` por 30 (mensual), 15 (quincenal) o 7 (semanal) según `salary_period`. Convención de nómina mexicana
  - **Helper `Employee::dailySalaryInWords()`**: convierte el salario diario a formato cheque mexicano (`(DOSCIENTOS CUARENTA Y OCHO 93/100)`) usando `NumberFormatter` con locale `es_MX` (extensión `intl` ya disponible en PHP 8.4)
  - **Locale `es` forzado en el controller** del PDF para que `Carbon::isoFormat('DD [de] MMMM [de] YYYY')` rinda los meses en español, sin afectar el locale global de la app
- **13 tests nuevos**: `tests/Feature/EmployeeNumberGeneratorTest.php` (4 tests del generador, incluye orden numérico vs string para evitar bug `WYB-0009` > `WYB-0010`) y `tests/Feature/EmployeeContractTest.php` (9 tests: endpoint PDF responde 200 PDF, requiere auth, helpers `dailySalary` / `dailySalaryInWords` / `companyData`, gate `view-salary` permite solo Super/Admin, save descarta salario para usuarios sin gate y persiste para los autorizados)

### Cambiado

- **`EmployeesController::save()`** ahora recibe `EmployeeNumberGenerator` por inyección (Livewire lo resuelve automáticamente) y le pide el siguiente número solo cuando se crea un empleado. En edición no toca `employee_number`
- **2 tests de `EmployeesCrudTest` adaptados al nuevo flujo**: `can create an employee from the controller` ahora verifica que el número generado matchea `/^WYB-\d{4}$/` (en vez de buscar un número manual). El test `cannot create employee with duplicated employee_number` se reemplazó por `consecutive employee creates produce sequential WYB numbers` que valida que dos saves consecutivos producen `WYB-0001` y `WYB-0002`

### Migración de datos

- **Migración aditiva** `2026_05_09_170138_add_position_salary_and_beneficiary_to_employees_table`: agrega 8 columnas a `employees` (todas nullable o con default seguro). Sin data-fill — los empleados existentes quedan con esos campos en `null` / `100` (default de `beneficiary_percentage`). El down() borra las columnas

---

## [1.7.0] - 2026-05-09

### Agregado

- **Trazabilidad de solicitante y aprobador en compras**: cada `Supply` captura ahora dos nuevos campos — `requester_id` (solicitante) y `approver_id` (aprobador) — referenciando `employees.id`. Es metadata informativa sobre quién pidió y quién autorizó cada compra, NO un flujo de aprobación previa transaccional (esa decisión sigue intacta desde 1.5.0). Aplica end-to-end:
  - **Captura en el formulario**: dos `<select>` nuevos en el modal de creación/edición de Supplies. Validación `required|exists:employees,id` en `SupplyForm`. Los selectores se filtran por la unidad de negocio del Supply (resuelta vía `category.business_unit`) — no se puede elegir un empleado de KIN para una compra de Jade Orgánico
  - **Aviso cuando la unidad no tiene empleados activos**: si el usuario elige una unidad y no hay ningún empleado activo cargado para ella, los dos `<select>` quedan deshabilitados y un banner ámbar muestra «No hay empleados activos cargados en {unidad}» con link directo al módulo de RRHH (`rrhh.empleados`). Evita el cuello de botella UX donde el form quedaba mudo sin explicar por qué no se podía continuar
  - **Cascada con cambio de unidad**: el hook `updatedFormBusinessUnit()` también limpia `requester_id` / `approver_id` y recarga el listado de empleados al cambiar de unidad, evitando estados inconsistentes
  - **Validación cruzada al guardar**: además de `required|exists`, se verifica que el empleado pertenezca a la unidad de negocio elegida. Defensa en profundidad sobre el guard de UI
  - **Render en la vista de OC (web)**: el modal de detalle de OC muestra «Solicitó: …» y «Aprobó: …» en cada compra. Si el dato es `null` (compra histórica) se muestra «—»
  - **Render en el PDF de OC**: dos columnas nuevas («Solicitante» y «Aprobador») se suman a la tabla de compras del PDF. El PDF cambia a orientación **landscape** para acomodar las columnas con legibilidad
  - **Eager loading**: `PurchaseOrderPdfController::show()` y `PurchaseOrdersController::showDetail()` ahora cargan `supplies.requester` y `supplies.approver` para evitar N+1 al renderizar nombres
- **Backfill de solicitante/aprobador en compras dentro de OC cerrada (bypass parcial del lock)**: las compras que pertenecen a una OC cerrada siguen lockeadas para edición vía `Supply::isLocked()`, PERO ahora el form abre en **modo backfill**: se permite actualizar SOLO `requester_id` y `approver_id`. El resto de campos (item, cantidad, precio, fecha, estado) queda readonly. Esto preserva la inmutabilidad contable de la OC mientras permite completar la metadata informativa de compras históricas
  - **UI**: el modal muestra un banner amarillo informativo en backfill mode, los campos no-bypass aparecen `disabled`, el bloque de comprobante se oculta, y el header del modal cambia a «Completar solicitante y aprobador»
  - **Backend**: `SuppliesController::edit()` setea `$backfillMode = $supply->isLocked()`. `SuppliesController::save()` se desvía a `saveBackfill()` que valida y persiste solo los dos campos vía un `update()` directo
- **11 nuevos tests** en `tests/Feature/SupplyRequesterApproverTest.php`: cubren validación required, validación cruzada por unidad, modo backfill (apertura, persistencia parcial, rechazo de empleados de otra unidad, no-aplicación cuando no hay OC cerrada), eager loading en el modal de detalle de OC y endpoint del PDF

### Cambiado

- **PDF de Orden de Compra**: orientación pasa de portrait a **landscape** para acomodar las columnas nuevas de Solicitante y Aprobador sin sacrificar legibilidad

### Migración de datos

- **Migración aditiva** `2026_05_09_160936_add_requester_and_approver_to_supplies_table`: agrega `requester_id` y `approver_id` a `supplies` como `foreignId` nullable con `nullOnDelete` apuntando a `employees`. Los Supplies pre-existentes quedan en `null` (de ahí el «—» en la OC) y pueden completarse vía edit aunque la OC ya esté cerrada. Schema tolerante (nullable) + form estricto (required) — patrón intencional para soportar backfill de históricos sin romper datos viejos

---

## [1.6.1] - 2026-05-09

### Cambiado

- **Renombre de unidad de negocio «Fuego Ambar» → «Jade Orgánico»**: la unidad cambia de nombre a pedido del negocio. El cambio aplica de extremo a extremo:
  - **Enum `App\Domain\BusinessUnit`**: el case `FuegoAmbar` pasa a `JadeOrganico` y el `value` de `'Fuego Ambar'` a `'Jade Orgánico'` (con tilde — el case PHP no la lleva por restricción del lenguaje, pero el valor visible y persistido sí)
  - **Paleta del badge**: el badge de la unidad cambia de la familia `amber` a `lime` para reflejar el nuevo nombre y mantenerse claramente distinguible del Jade actual (que usa `emerald`)
  - **Validaciones**: `SupplyForm` y `EmployeeForm` ahora validan `in:Jade,Jade Orgánico,KIN`. Cualquier integración o test que persistiera literalmente `'Fuego Ambar'` debe migrarse al nuevo valor
  - **Vista de Ingresos**: el `<option>` del modal de Income usa el nuevo label
  - **Factories**: `CategoryFactory`, `EmployeeFactory` y `PurchaseOrderFactory` generan registros con el valor nuevo

### Migración de datos

- **Migración reversible** `2026_05_09_145115_rename_fuego_ambar_to_jade_organico`: hace `UPDATE business_unit` de `'Fuego Ambar'` → `'Jade Orgánico'` en las cinco tablas que persisten la unidad (`categories`, `income_periods`, `employees`, `purchase_orders`, `daily_sales`) dentro de una transacción. El `down()` revierte el rename. Las OCs existentes con `oc_number` del estilo `OC-FuegoAmbar-…` quedan inmutables por la convención del proyecto sobre OCs (los nuevos OCs se generarán con slug `OC-JadeOrgánico-…`)

---

## [1.6.0] - 2026-05-09

### Agregado

- **Selector obligatorio de unidad de negocio en el modal de creación/edición de Supplies**: el formulario ahora exige elegir la unidad ANTES de buscar categoría. Esto previene una clase entera de errores de input donde el usuario elegía una categoría de otra unidad por accidente y la compra terminaba contabilizada bajo la unidad equivocada (la unidad se hereda de `category.business_unit`)
  - **Buscador de categorías filtrado**: `SuppliesQuery::searchCategories($term, ?string $businessUnit)` acepta unidad opcional. El input de búsqueda queda deshabilitado hasta que se elija unidad, y al seleccionar unidad sólo se devuelven categorías de esa unidad
  - **Cambio de unidad invalida la categoría**: el hook `updatedFormBusinessUnit()` limpia `form.category_id` y `categorySearch` cuando cambia la unidad, evitando un estado inconsistente
  - **Validación cruzada al guardar**: en `save()` se verifica que `category.business_unit === form.business_unit`. Si no coinciden (manipulación o estado raro) se rechaza con error en el campo categoría. Defensa en profundidad sobre el guard de UI
  - **Edición**: al abrir un supply existente, la unidad se infiere de la categoría asociada y se prellena en el formulario, manteniendo coherencia visual del label completo de la categoría
  - **Validación de regla**: `business_unit` pasa a ser `required|in:Jade,Fuego Ambar,KIN` en `SupplyForm`. No se persiste en la tabla `supplies` (la unidad sigue viviendo en `categories`); es sólo del form para guiar el flujo

### Corregido

- **Regresión multi-unidad en listado de Supplies**: el fix del «select fantasma» en 1.5.0 inicializaba `business_unit` al primer caso del enum (`Jade`) en `mount()`, lo que provocaba que la tabla filtrara por Jade desde el arranque y ocultara las compras de otras unidades. Una compra cargada para KIN o Fuego Ámbar simplemente no aparecía hasta cambiar manualmente el filtro de unidad. La causa raíz era haber mezclado dos requerimientos distintos en una sola decisión: el botón «Generar OC» necesita unidad seleccionada, pero la tabla NO debería filtrar por una unidad específica por defecto
  - **Fix correcto**: `business_unit` vuelve a `''` por default; el `<select>` ahora incluye `<option value="">Todas</option>` como primera opción, lo que elimina el estado fantasma original (el modelo vacío matchea con una `<option>` real, sin discrepancia visual entre browser y servidor) y permite ver todas las compras al entrar
  - **Botón «Generar OC»**: sigue apareciendo sólo cuando hay unidad seleccionada vía `@if($business_unit)`. La validación del flujo de generación queda intacta
  - `resetFilters()` también vuelve al default vacío para mantener consistencia

---

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
