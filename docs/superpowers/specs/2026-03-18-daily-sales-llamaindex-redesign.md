# Daily Sales — Rediseno con LlamaIndex Extraction

**Fecha:** 2026-03-18
**Estado:** Aprobado

## Resumen

Reemplazar la importacion manual de Excel en el modulo de ventas diarias por un servicio de extraccion de datos via LlamaIndex Cloud. El usuario sube un PDF del reporte de SoftRestaurant, LlamaIndex extrae los datos via webhook asincrono, y el sistema los persiste automaticamente. Se enriquece el dashboard con metricas de metodos de pago, datos operativos y comparativo por turno. Se eliminan archivos y tablas en desuso.

---

## 1. Migracion de Base de Datos

### 1.1 Tabla `daily_sales` — campos nuevos

Se crea una migracion `add_new_fields_to_daily_sales_table` que agrega:

| Campo | Tipo | Descripcion |
|-------|------|-------------|
e| `turno` | tinyint unsigned, default 1 | 1 = Matutino (7-15h), 2 = Vespertino (15-22h). Default 1 para compatibilidad con registros existentes |
| `status` | string, default 'completed' | processing, completed, failed. Default 'completed' para que registros existentes (importados via Excel) se muestren correctamente |
| `error_message` | text, nullable | Detalle del error si falla |
| `llama_job_id` | string, nullable, index | ID del job en LlamaIndex |
| `extraction_raw_json` | json, nullable | JSON completo para auditoria |
| `efectivo_monto` | decimal(12,2), default 0 | Ventas en efectivo |
| `efectivo_propina` | decimal(12,2), default 0 | Propinas en efectivo |
| `debito_monto` | decimal(12,2), default 0 | Ventas tarjeta debito |
| `debito_propina` | decimal(12,2), default 0 | Propinas tarjeta debito |
| `credito_monto` | decimal(12,2), default 0 | Ventas tarjeta credito |
| `credito_propina` | decimal(12,2), default 0 | Propinas tarjeta credito |
| `credito_cliente_monto` | decimal(12,2), default 0 | Ventas a credito cliente |
| `credito_cliente_propina` | decimal(12,2), default 0 | Propinas credito cliente |
| `numero_personas` | int unsigned, default 0 | Personas atendidas |
| `numero_cuentas` | int unsigned, default 0 | Cuentas cerradas |
| `promedio_por_persona` | decimal(12,2), default 0 | Ticket promedio |
| `cantidad_productos` | int unsigned, default 0 | Productos vendidos |
| `period_start` | datetime, nullable | Inicio del periodo del reporte |
| `period_end` | datetime, nullable | Fin del periodo del reporte |

**Cambio de constraint unico:**
1. La migracion debe primero `dropUnique` del indice existente `(business_unit, operation_date)`
2. Luego crear el nuevo indice unico `(business_unit, operation_date, turno)`

### 1.2 Eliminar tabla `cash_extractions`

Se crea una migracion `drop_cash_extractions_table`. **Nota:** La tabla real en BD se llama `cash_xtraction` (typo en la migracion original), no `cash_extractions`. El `Schema::dropIfExists` debe apuntar a `cash_xtraction`. El metodo `down()` la recrea para rollback.

### 1.3 Cola de trabajos

El proyecto usa `QUEUE_CONNECTION=database` por defecto. El job `ProcessLlamaExtractionJob` requiere que el queue worker este corriendo: `php artisan queue:work`. La tabla `jobs` ya existe (migracion `0001_01_01_000002_create_jobs_table.php`).

---

## 2. Servicio LlamaIndex

### 2.1 LlamaIndexService

**Archivo:** `app/Services/LlamaIndexService.php`

Replica del proyecto `extractInformation`. Singleton registrado en `AppServiceProvider`.

**Constructor:**
- `baseUrl`: desde `config('services.llama_index.base_url')`
- `apiKey`: desde `config('services.llama_index.api_key')`
- `extractionAgentId`: desde `config('services.llama_index.extraction_agent_id')`

**Metodos:**
- `uploadFile(UploadedFile $file): Response` — POST /files con multipart
- `createExtractionJob(string $fileId, ?array $webhookConfigurations): Response` — POST /extraction/jobs
- `getExtractionJobResult(string $jobId): Response` — GET /extraction/jobs/{jobId}/result

Todos los requests usan `Http::withToken($apiKey)` con Bearer.

### 2.2 Configuracion

**`config/services.php`** — nuevas entradas:
```php
'llama_index' => [
    'base_url' => env('LLAMA_INDEX_BASE_URL', 'https://api.cloud.llamaindex.ai/api/v1'),
    'api_key' => env('LLAMA_INDEX_API_KEY'),
    'extraction_agent_id' => env('LLAMA_INDEX_EXTRACTION_AGENT_ID'),
],
```

**Variables de entorno:**
```
LLAMA_INDEX_BASE_URL=https://api.cloud.llamaindex.ai/api/v1
LLAMA_INDEX_API_KEY=tu_api_key
LLAMA_INDEX_EXTRACTION_AGENT_ID=tu_agent_id
```

---

## 3. Job de Procesamiento

**Archivo:** `app/Jobs/ProcessLlamaExtractionJob.php`

- Implementa `ShouldQueue`
- Timeout: 120 segundos
- Reintentos: 3

**Flujo:**
1. Recibe `DailySale` (ya creado con status `processing`) y el path del archivo PDF temporal
2. Sube PDF a LlamaIndex via `uploadFile()`
3. Crea extraction job con webhook:
   - URL: `{config('app.url')}/api/webhook/llama`
   - Eventos: `['extract.success', 'extract.error']`
   - Formato: `json`
4. Guarda `llama_job_id` en el `DailySale`
5. Si falla → marca `DailySale` como `failed` con `error_message`
6. Limpia archivo temporal en bloque `finally`

---

## 4. Webhook Controller

### 4.1 LlamaWebhookController

**Archivo:** `app/Http/Controllers/Api/LlamaWebhookController.php`

**Ruta:** `POST /api/webhook/llama` — sin middleware auth, con middleware idempotent.

**Configuracion de ruta:** El proyecto no tiene `routes/api.php`. Se crea `routes/api.php` y se registra en `bootstrap/app.php` via `->withRouting(api: __DIR__.'/../routes/api.php')`. Las rutas API en Laravel 12 no incluyen CSRF verification, resolviendo el problema de requests externos. La ruta del webhook se define en este archivo.

**Seguridad del webhook:** Se valida que el `llama_job_id` exista en la BD antes de procesar. Requests con job_id inexistente reciben 404. El middleware de idempotencia previene reprocesamiento duplicado.

**Flujo:**
1. Extrae `event_type` y `data.job_id` del request
2. Busca `DailySale` por `llama_job_id` — si no existe retorna 404
3. Si `extract.success`:
   - Llama a `LlamaIndexService::getExtractionJobResult($jobId)`
   - Pasa el JSON a `DailySaleExtractionMapper`
   - Actualiza el `DailySale` con los campos mapeados
   - Guarda JSON completo en `extraction_raw_json`
   - Marca status `completed`
4. Si `extract.error`:
   - Marca status `failed` con `error_message`
5. Retorna 200 OK

### 4.2 DailySaleExtractionMapper

**Archivo:** `app/Services/DailySaleExtractionMapper.php`

Clase dedicada que recibe el JSON crudo y retorna un array mapeado.

**Mapeo:**

| Campo JSON | Campo BD |
|------------|----------|
| `sales_by_area[COMEDOR].food_sales` | `alimentos` |
| `sales_by_area[COMEDOR].beverage_sales` | `bebidas` |
| `sales_by_area[COMEDOR].other_sales` | `otros` |
| `sales_by_area[COMEDOR].subtotal` | `subtotal` |
| `sales_by_area[COMEDOR].tax` | `iva` |
| `sales_by_area[COMEDOR].total` | `total` |
| `sales_by_area[COMEDOR].number_of_people` | `numero_personas` |
| `sales_by_area[COMEDOR].number_of_accounts` | `numero_cuentas` |
| `sales_by_area[COMEDOR].average_per_person` | `promedio_por_persona` |
| `sales_by_area[COMEDOR].product_count` | `cantidad_productos` |
| `payment_summary[EFECTIVO].amount` | `efectivo_monto` |
| `payment_summary[EFECTIVO].tip` | `efectivo_propina` |
| `payment_summary[TARJETA DEBITO].amount` | `debito_monto` |
| `payment_summary[TARJETA DEBITO].tip` | `debito_propina` |
| `payment_summary[TARJETA CREDITO].amount` | `credito_monto` |
| `payment_summary[TARJETA CREDITO].tip` | `credito_propina` |
| `payment_summary[CREDITO].amount` | `credito_cliente_monto` |
| `payment_summary[CREDITO].tip` | `credito_cliente_propina` |
| `report_period.start_datetime` | `period_start` |
| `report_period.end_datetime` | `period_end` |

Busca el area COMEDOR en `sales_by_area` por `area_name`. Busca metodos de pago por `payment_method` en `payment_summary`. Parsea fechas con Carbon desde formato `dd/MM/yyyy hh:mm:ss AM/PM`.

### 4.3 Middleware de Idempotencia

**Archivo:** `app/Http/Middleware/EnsureIdempotency.php`

Replica del proyecto `extractInformation`:
- Usa cache para almacenar respuestas por `event_id` del payload
- TTL: 1440 minutos (24 horas)
- Retorna respuesta cacheada con header `X-Idempotent-Replayed: true` si es duplicado
- Solo cachea respuestas exitosas (2xx)

---

## 5. Cambios en Livewire DailySalesController

### 5.1 Flujo de subida

El metodo `importFile()` se reemplaza por `uploadPdf()`:
1. Valida archivo PDF, max 10MB
2. Valida que no exista registro `completed` para (unidad, fecha, turno)
3. Si existe un registro `failed` → lo elimina
4. Crea `DailySale` con status `processing`, `user_id`, turno
5. Guarda PDF temporalmente
6. Despacha `ProcessLlamaExtractionJob`
7. Cierra modal y notifica "Archivo enviado a procesar"

### 5.2 Vista de tabla

Cambios en columnas:
- Agrega columna **Turno** (Matutino/Vespertino)
- Agrega columna **Status** con badges de color (amarillo/verde/rojo)
- Agrega columna **Subido por** (nombre del usuario)
- Registros `failed` muestran boton de reintentar (abre modal prellenado)
- Registros `processing` muestran celdas vacias en datos financieros
- Registros `completed` no permiten eliminacion (solo lectura) — validacion server-side en metodo `destroy()`, no solo UI
- Solo se pueden eliminar registros `failed` o `processing`

### 5.3 Modal de subida

- Campo `file` acepta solo PDF (antes xlsx/xls/csv)
- Agrega selector de **turno** (Matutino / Vespertino)
- Mantiene selector de unidad de negocio y fecha

### 5.4 Modal de detalle

Agrega secciones:
- Metodos de pago (montos y propinas por metodo)
- Datos operativos (personas, cuentas, ticket promedio, productos)
- Periodo del reporte (start/end)

---

## 6. Dashboard Reorganizado

### 6.1 Layout (flujo vertical, una sola vista)

**Fila 1 — KPIs principales (3 cards):**
- Total Ventas (con desglose subtotal/IVA)
- Total Gastos
- Utilidad (con % margen, color dinamico verde/rojo)

**Fila 2 — Desglose por categoria (3 cards):**
- Alimentos, Bebidas, Otros

**Fila 3 — Metodos de pago (4 cards):**
- Efectivo (monto + propina)
- Tarjeta Debito (monto + propina)
- Tarjeta Credito (monto + propina)
- Credito Cliente (monto + propina)

**Fila 4 — Operativas + Turnos (2 columnas):**
- Izquierda: Ticket promedio, total personas, total cuentas
- Derecha: Comparativo Turno Matutino vs Vespertino (ventas, personas, ticket)

**Fila 5 — Graficas (2 columnas):**
- Ventas por unidad de negocio (barras)
- Distribucion por metodo de pago (dona)

**Fila 6 — Estado de Resultados:**
- Ingresos, gastos agrupados por tipo, utilidad

### 6.2 Metricas nuevas en SalesDashboard.php

Nuevas propiedades calculadas desde `daily_sales`:
- Sumas de metodos de pago (monto y propina por metodo)
- Total propinas (suma de todas las propinas)
- Numero de personas y cuentas del periodo
- Ticket promedio ponderado
- Datos por turno (ventas, personas, ticket promedio filtrados por turno 1 y 2)

---

## 7. Reportes de Exportacion

### 7.1 Reporte de Ventas (rutas existentes)

- `GET /dashboard/ventas/export/excel`
- `GET /dashboard/ventas/export/pdf`

Contenido actualizado:
- KPIs principales (ventas, gastos, utilidad, margen)
- Desglose por categoria
- Metodos de pago (monto y propina)
- Metricas operativas (personas, cuentas, ticket promedio)
- Comparativo por turno

### 7.2 Reporte Estado de Resultados (rutas nuevas)

- `GET /dashboard/estado-resultados/export/excel`
- `GET /dashboard/estado-resultados/export/pdf`

Contenido:
- Ingreso total (100%)
- Desglose de ingresos por categoria
- Gastos agrupados por tipo de gasto con subcategorias
- Total gastos
- Utilidad neta (monto y %)

Se agregan botones separados en el dashboard para cada reporte.

---

## 8. Limpieza de Codigo

### 8.1 Archivos a eliminar

| Archivo | Razon |
|---------|-------|
| `app/Models/CashExtraction.php` | Tabla unificada en daily_sales |
| `app/Imports/DailySalesImport.php` | Reemplazado por LlamaIndex |
| `app/Services/ExtractService.php` | Reemplazado por LlamaIndexService |
| `resources/views/livewire/modals/cash.blade.php` | Modal sin controller |

### 8.2 Archivos ya eliminados en git staging

- `app/Application/CashExtractions/CashExtractionsQuery.php`
- `app/Application/CashExtractions/SubmitCashExtraction.php`
- `app/Application/CashExtractions/ValidateCashExtraction.php`
- `app/Livewire/CorteController.php`
- `resources/views/livewire/corte-controller.blade.php`

### 8.3 Archivos nuevos

| Archivo | Proposito |
|---------|-----------|
| `app/Services/LlamaIndexService.php` | Cliente HTTP para LlamaIndex API |
| `app/Services/DailySaleExtractionMapper.php` | Mapeo de JSON a campos de daily_sales |
| `app/Jobs/ProcessLlamaExtractionJob.php` | Job en cola para procesar extraccion |
| `app/Http/Controllers/Api/LlamaWebhookController.php` | Receptor del webhook de LlamaIndex |
| `app/Http/Middleware/EnsureIdempotency.php` | Proteccion contra webhooks duplicados |

### 8.4 Archivos a actualizar

- `app/Models/DailySale.php` — nuevos campos, casts, enum status
- `database/factories/DailySaleFactory.php` — campos nuevos para testing
- `app/Livewire/SalesDashboard.php` — metricas nuevas
- `app/Livewire/DailySalesController.php` — flujo PDF + LlamaIndex
- `app/Http/Controllers/DashboardExportController.php` — reportes nuevos
- `resources/views/livewire/sales-dashboard.blade.php` — layout reorganizado
- `resources/views/livewire/daily-sales-controller.blade.php` — columnas y badges
- `resources/views/livewire/modals/form-daily-sales.blade.php` — turno, solo PDF
- `resources/views/livewire/modals/detail-daily-sales.blade.php` — datos nuevos
- `config/services.php` — config LlamaIndex
- `app/Providers/AppServiceProvider.php` — singleton LlamaIndexService
- `routes/web.php` — rutas estado de resultados dentro del grupo auth
- `routes/api.php` — nuevo archivo, ruta del webhook LlamaIndex
- `bootstrap/app.php` — registrar routes/api.php
- `tests/Feature/DailySalesTest.php` — tests actualizados + nuevos
- `README.md` — documentar config LlamaIndex y flujos nuevos

---

## 9. Testing

Los tests existentes de import Excel (`daily sales import sums rows`, `daily sales import updates existing record`) se eliminan ya que prueban `DailySalesImport` que se elimina. Los tests de constraint unico se actualizan para incluir turno. El factory se actualiza con campo `turno` (random 1 o 2) y `status` default `completed`.

Tests a crear/actualizar:
- Upload de PDF crea DailySale con status processing
- Validacion de constraint unico (unidad, fecha, turno)
- Registro failed permite reintento
- Registro completed no permite duplicado
- Webhook extract.success actualiza datos correctamente
- Webhook extract.error marca como failed
- Webhook con job_id inexistente retorna 404
- Idempotencia: webhook duplicado no reprocesa
- DailySaleExtractionMapper parsea JSON correctamente
- Dashboard calcula metricas nuevas correctamente
- Exportaciones incluyen datos nuevos
- Destroy rechaza eliminacion de registros completed (server-side)
