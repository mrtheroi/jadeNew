# Plan de Refactorización Completa — Proyecto Jade

**Fecha:** 2026-03-21
**Estado:** Aprobado
**Scope:** Arquitectura + Código duplicado + Performance + UI/UX + Tests

---

## Contexto

El proyecto Jade es una app Laravel 12 + Livewire 3 de gestión de restaurantes (ventas diarias, gastos, reconciliación, dashboard). Tiene 3,798 líneas de código, 46 archivos PHP, 57 blade views, 15 rutas, 11 tablas DB.

### Problemas identificados (auditoría completa):
- Livewire components son "God Components" — hacen queries, cálculos, validación y presentación
- ~150 líneas de queries duplicadas entre SalesDashboard y DashboardExportController
- Patrones CRUD (create/edit/closeModal/delete) repetidos en 5 controllers
- Business units hardcodeadas en 4 vistas en vez de usar el enum
- Color matching duplicado en 3 vistas
- Models sin scopes ni relaciones inversas
- UI inconsistente: mezcla de Flux, HTML raw, Font Awesome, SVG
- Toast notification solo soporta success
- Sin tests para 6 de los módulos principales

### Decisiones de diseño tomadas:
- **Arquitectura:** Service Layer Pattern
- **UI Components:** Blade components anónimos (sin clase PHP)
- **Iconos:** SVG inline puro (eliminar Font Awesome)
- **Flux UI:** Se mantiene SOLO en layout base (sidebar, navigation, `<flux:main>`). Todo lo nuevo en Tailwind puro
- **Modals:** Cierre con backdrop click + Escape + botón X
- **Tablas:** Desktop tal cual, móvil ocultar columnas de baja prioridad con `hidden md:table-cell`
- **Mobile sidebar:** No invertir tiempo, la app es primariamente desktop
- **Notificaciones:** 4 variantes, stackeable, progress bar, Alpine.js puro

---

## FASE 1 — Fundación: Modelos y Scopes

**Objetivo:** Base limpia para construir los servicios encima.
**Riesgo:** Bajo
**Dependencias:** Ninguna

### 1.1 Agregar scopes a modelos

**DailySale:**
```php
public function scopeCompleted(Builder $query): Builder
{
    return $query->where('status', 'completed');
}

public function scopeFailed(Builder $query): Builder
{
    return $query->where('status', 'failed');
}

public function scopeProcessing(Builder $query): Builder
{
    return $query->where('status', 'processing');
}

public function scopeInPeriod(Builder $query, string $from, string $to): Builder
{
    return $query->whereBetween('operation_date', [$from, $to]);
}

public function scopeByUnit(Builder $query, ?string $unit): Builder
{
    return $unit ? $query->where('business_unit', $unit) : $query;
}
```

**Supply:**
```php
public function scopeNotCancelled(Builder $query): Builder
{
    return $query->where('status', '!=', 'cancelado');
}

public function scopeInPeriod(Builder $query, string $from, string $to): Builder
{
    return $query->whereBetween('payment_date', [$from, $to]);
}
```

**Category y ExpenseType:**
```php
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

### 1.2 Completar relaciones faltantes

**Category:**
```php
public function supplies(): HasMany
{
    return $this->hasMany(Supply::class);
}
```

**ExpenseType:**
```php
public function categories(): HasMany
{
    return $this->hasMany(Category::class);
}
```

### 1.3 Reemplazar queries inline

Buscar y reemplazar en todo el proyecto:
- `->where('status', 'completed')` → `->completed()`
- `->where('status', '!=', 'cancelado')` → `->notCancelled()`
- `->where('is_active', true)` → `->active()`
- `->whereBetween('operation_date', [$from, $to])` → `->inPeriod($from, $to)`
- `->whereBetween('payment_date', [$from, $to])` → `->inPeriod($from, $to)`

### 1.4 Limpieza menor

- Eliminar `Log::info('llego la notificación')` de `app/Livewire/Notification.php:18`

### 1.5 Tests

- Tests unitarios para cada scope nuevo
- Verificar que tests existentes siguen pasando

---

## FASE 2 — Service Layer

**Objetivo:** Extraer lógica de negocio de controllers a servicios. Eliminar duplicación de queries.
**Riesgo:** Medio
**Dependencias:** Fase 1

### 2.1 Crear DashboardService

**Archivo:** `app/Services/DashboardService.php`

Extrae lógica de:
- `SalesDashboard::salesQuery()` (líneas 93-106)
- `SalesDashboard::expensesQuery()` (líneas 108-122)
- `SalesDashboard::buildChartData()` (líneas 124-229)
- `SalesDashboard::buildExpenseGroups()` (líneas 231-273)
- `DashboardExportController::buildSalesData()` (líneas 29-118)
- `DashboardExportController::buildEstadoResultadosData()` (líneas 120-196)

**Métodos del servicio:**
```php
class DashboardService
{
    public function getSalesTotals(?string $unit, string $periodKey): object
    public function getExpensesTotals(?string $unit, string $periodKey): float
    public function getTurnoBreakdown(?string $unit, string $periodKey): array
    public function getSalesByUnit(?string $unit, string $periodKey): array
    public function getPaymentMethodTotals(?string $unit, string $periodKey): array
    public function getExpenseGroups(?string $unit, string $periodKey): array
    public function getFullDashboardData(?string $unit, string $periodKey): array
}
```

**Resultado en SalesDashboard:**
```php
public function render()
{
    $data = app(DashboardService::class)
        ->getFullDashboardData($this->business_unit, $this->period_key);

    $this->dispatch('chart-data-updated', data: $data['charts']);

    return view('livewire.sales-dashboard', $data);
}
```

**Resultado en DashboardExportController:**
```php
public function excel(Request $request, DashboardService $dashboard): StreamedResponse
{
    $data = $dashboard->getFullDashboardData(
        $request->input('business_unit'),
        $request->input('period_key')
    );
    // solo formateo de export
}
```

### 2.2 Crear DailySalesQuery

**Archivo:** `app/Application/DailySales/DailySalesQuery.php`

Similar a `SuppliesQuery` que ya existe:
```php
class DailySalesQuery
{
    public function __construct(
        private ?string $search,
        private ?string $businessUnit,
        private ?string $periodKey,
    ) {}

    public function base(): Builder
    public function totals(): object
}
```

### 2.3 Adelgazar controllers

- `SalesDashboard`: de ~283 líneas a ~40 líneas
- `DashboardExportController`: de ~328 líneas a ~80 líneas (solo formateo)
- `DailySalesController::render()`: usa `DailySalesQuery` en vez de queries inline

### 2.4 Tests

- Tests para `DashboardService` (todos los métodos)
- Tests para `DailySalesQuery`
- Verificar que tests existentes siguen pasando

---

## FASE 3 — Código duplicado: Traits y Enum cleanup

**Objetivo:** Eliminar ~200 líneas de código repetido.
**Riesgo:** Bajo
**Dependencias:** Fase 1

### 3.1 Crear trait HasModalCrud

**Archivo:** `app/Livewire/Concerns/HasModalCrud.php`

Extrae de 5 controllers:
```php
trait HasModalCrud
{
    public bool $open = false;

    public function create(): void
    {
        $this->form->reset();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetValidation();
    }
}
```

**Aplicar en:** `CategoryController`, `ExpenseTypeController`, `UserController`, `SuppliesController`, `DailySalesController`

### 3.2 Crear trait HasSearchFilter

**Archivo:** `app/Livewire/Concerns/HasSearchFilter.php`

```php
trait HasSearchFilter
{
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}
```

### 3.3 Agregar método color() al enum BusinessUnit

**Archivo:** `app/Domain/BusinessUnit.php`

```php
public function badgeClasses(): string
{
    return match($this) {
        self::Jade => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
        self::FuegoAmbar => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
        self::Kin => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-300',
    };
}
```

> **Nota:** Las clases de Tailwind deben ser strings completos, no interpolados. Tailwind CSS purga clases que no encuentra como strings literales en el código.

### 3.4 Reemplazar business units hardcodeadas

En estas 4 vistas, reemplazar `<option>` hardcodeados por:
```blade
@foreach(\App\Domain\BusinessUnit::cases() as $bu)
    <option value="{{ $bu->value }}">{{ $bu->value }}</option>
@endforeach
```

**Archivos:**
- `resources/views/livewire/modals/form-incomes.blade.php`
- `resources/views/livewire/category-controller.blade.php`
- `resources/views/livewire/daily-sales-controller.blade.php`
- `resources/views/livewire/supplies-controller.blade.php`

### 3.5 Eliminar color match duplicado en 3 vistas

Reemplazar los `match()` de colores en las 3 vistas por uso del enum:
```blade
<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
    {{ \App\Domain\BusinessUnit::from($unit)->badgeClasses() }}">
    {{ $unit }}
</span>
```

**Archivos:**
- `resources/views/livewire/category-controller.blade.php` (líneas 154-159)
- `resources/views/livewire/daily-sales-controller.blade.php` (líneas 171-176)
- `resources/views/livewire/supplies-controller.blade.php` (líneas 300-305)

### 3.6 Estandarizar naming

- `DailySalesController`: renombrar `$filterBusinessUnit` → `$business_unit` para consistencia con el resto

### 3.7 Fix form-incomes

- Cambiar `wire:model.defer="business_unit"` → `wire:model.defer="form.business_unit"`
- Cambiar `@error('business_unit')` → `@error('form.business_unit')`
- Aplicar a todas las propiedades del modal

### 3.8 Tests

- Verificar que tests existentes siguen pasando tras aplicar traits

---

## FASE 4 — Blade Components (Tailwind puro)

**Objetivo:** Crear componentes anónimos reutilizables para eliminar repetición en vistas.
**Riesgo:** Bajo
**Dependencias:** Ninguna (puede ejecutarse en paralelo con Fases 1-3)

### 4.1 Componente `<x-card>`

**Archivo:** `resources/views/components/card.blade.php`

```blade
@props(['class' => ''])

<div {{ $attributes->merge(['class' => "rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 $class"]) }}>
    {{ $slot }}
</div>
```

**Uso:** `<x-card class="p-4">Contenido</x-card>`

### 4.2 Componente `<x-badge>`

**Archivo:** `resources/views/components/badge.blade.php`

```blade
@props(['variant' => 'default'])

@php
$classes = match($variant) {
    'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300',
    'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
    'danger'  => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-300',
    'info'    => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-300',
    'indigo'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-900/30 dark:text-indigo-300',
    default   => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900/30 dark:text-gray-200',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset $classes"]) }}>
    {{ $slot }}
</span>
```

### 4.3 Componente `<x-stat-card>`

**Archivo:** `resources/views/components/stat-card.blade.php`

```blade
@props(['label', 'value', 'subtitle' => null, 'variant' => 'default'])

@php
$valueClass = match($variant) {
    'success' => 'text-emerald-600 dark:text-emerald-400',
    'danger'  => 'text-rose-600 dark:text-rose-400',
    'warning' => 'text-amber-600 dark:text-amber-400',
    'info'    => 'text-blue-600 dark:text-blue-400',
    default   => 'text-gray-900 dark:text-white',
};
@endphp

<x-card class="p-4">
    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <p class="mt-2 text-xl font-semibold {{ $valueClass }}">{{ $value }}</p>
    @if($subtitle)
        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
    @endif
</x-card>
```

### 4.4 Componente `<x-modal>`

**Archivo:** `resources/views/components/modal.blade.php`

Props: `name`, `maxWidth` (sm, md, lg, xl, 2xl)

```blade
@props(['name', 'maxWidth' => 'lg'])

@php
$maxWidthClass = match($maxWidth) {
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
};
@endphp

<div
    x-data="{ open: @entangle($attributes->wire('model')).live }"
    x-show="open"
    x-cloak
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-on:click.stop
            x-trap.noscroll="open"
            class="relative w-full {{ $maxWidthClass }} rounded-xl bg-white shadow-xl dark:bg-gray-900"
        >
            {{-- Close button --}}
            <button x-on:click="open = false"
                class="absolute right-3 top-3 rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
```

Comportamiento:
- `x-on:click="open = false"` en backdrop → cierre con click fuera
- `x-on:keydown.escape.window` → cierre con tecla Escape
- Botón X → cierre explícito
- `x-trap.noscroll` → focus trap (Alpine Focus plugin, incluido con Livewire)
- `x-on:click.stop` en panel → previene que clicks dentro cierren el modal

### 4.5 Componente `<x-form-field>`

**Archivo:** `resources/views/components/form-field.blade.php`

```blade
@props(['label', 'name', 'error' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
    <div class="mt-1">
        {{ $slot }}
    </div>
    @error($error ?? $name)
        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
```

### 4.6 Componente `<x-empty-state>`

**Archivo:** `resources/views/components/empty-state.blade.php`

Props: `icon` (SVG slot), `title`, `description`
Slot para action buttons.

### 4.7 Componente `<x-tooltip>`

**Archivo:** `resources/views/components/tooltip.blade.php`

Tooltip hover con pattern `group` + `group-hover:opacity-100`, posición configurable.

### 4.8 Componente `<x-data-table>`

**Archivo:** `resources/views/components/data-table.blade.php`

Props: `title`, `count`, `loading-target`
Slots: `header` (thead), default (tbody), `pagination`
Incluye: loading overlay, sticky header, scroll container, dark mode.

---

## FASE 5 — Sistema de Notificaciones

**Objetivo:** Toast mejorado con 4 variantes, stackeable, progress bar.
**Riesgo:** Bajo
**Dependencias:** Ninguna (puede ejecutarse en paralelo)

### 5.1 Reescribir Notification component

**Backend (`app/Livewire/Notification.php`):**
- Eliminar `Log::info`
- Mantener interfaz `dispatch('notify', message: '...', type: '...')`

**Frontend (Alpine.js puro):**

Variantes:
| Tipo | Color | Icono SVG | Duración |
|------|-------|-----------|----------|
| success | emerald | checkmark circle | 4s |
| error | rose | x circle | 8s |
| warning | amber | exclamation triangle | 6s |
| info | blue | information circle | 4s |

Características:
- Stackeable (hasta 3 visibles, las nuevas empujan las viejas)
- Progress bar sutil en la parte inferior que muestra tiempo restante
- Posición: top-right consistente
- Botón X para cerrar manualmente
- Dark mode: `bg-white/90 dark:bg-gray-800/90 backdrop-blur`
- Transiciones: slide-in desde derecha, fade-out

---

## FASE 6 — Estandarización UI

**Objetivo:** Aplicar componentes nuevos + limpiar inconsistencias en todas las vistas.
**Riesgo:** Medio
**Dependencias:** Fases 4 y 5

### 6.1 Reemplazar iconos Font Awesome por SVG

Buscar todos los `<i class="fa-` en blade views y reemplazar por SVG inline equivalente.
Usar iconos de Heroicons (MIT license) como base SVG.

### 6.2 Estandarizar botones

Definir 4 variantes de botón como clases Tailwind consistentes:
- **Primary:** `bg-indigo-600 hover:bg-indigo-500 text-white`
- **Secondary:** `border border-gray-200 hover:bg-gray-50 text-gray-800`
- **Danger:** `bg-rose-600 hover:bg-rose-500 text-white`
- **Icon-only:** `rounded-md p-2 hover:bg-[color]-50` + tooltip

### 6.3 Estandarizar wire:model

Criterio claro:
- `wire:model.live.debounce.300ms` → para filtros y search
- `wire:model` (defer por defecto en Livewire 3) → para formularios

### 6.4 Aplicar componentes a vistas existentes

Para cada vista principal:
- Reemplazar divs de card por `<x-card>`
- Reemplazar badges de status por `<x-badge>`
- Reemplazar estructura de tabla por `<x-data-table>`
- Reemplazar modals por `<x-modal>`
- Reemplazar form fields por `<x-form-field>`
- Reemplazar empty states por `<x-empty-state>`
- Reemplazar KPI cards por `<x-stat-card>`

**Vistas a actualizar:**
1. `sales-dashboard.blade.php`
2. `supplies-controller.blade.php`
3. `daily-sales-controller.blade.php`
4. `users.blade.php`
5. `category-controller.blade.php`
6. `expense-type.blade.php`
7. Todos los modals en `livewire/modals/`

### 6.5 Fixes puntuales

- Fix colspan incorrecto en empty state de Daily Sales (10 → 13)
- Agregar loading state a filtros del Dashboard
- Agregar empty state al Dashboard cuando no hay datos
- Tablas: agregar `hidden md:table-cell` a columnas de baja prioridad

---

## FASE 7 — Tests de Cobertura

**Objetivo:** Red de seguridad completa.
**Riesgo:** Bajo
**Dependencias:** Fases 1-3 (para testear servicios y traits)

### 7.1 Tests por módulo

| Módulo | Archivo | Qué testear |
|--------|---------|-------------|
| SalesDashboard | `tests/Feature/SalesDashboardTest.php` | Render, filtros, cálculos |
| DashboardExport | `tests/Feature/DashboardExportTest.php` | Excel, PDF, CSV downloads |
| CategoryController | `tests/Feature/CategoryTest.php` | CRUD completo, validación |
| ExpenseTypeController | `tests/Feature/ExpenseTypeTest.php` | CRUD completo, validación |
| UserController | `tests/Feature/UserCrudTest.php` | CRUD, roles, soft delete |
| DashboardService | `tests/Unit/DashboardServiceTest.php` | Todos los métodos del servicio |
| ExpensesReportService | `tests/Feature/ExpensesReportTest.php` | Report generation |

### 7.2 Pest datasets

Usar datasets para validación rules donde aplique (emails, nombres, business units).

---

## Orden de ejecución y paralelismo

```
Semana 1:  [FASE 1: Modelos]  [FASE 4: Components]  [FASE 5: Notifications]
                  │                    │                      │
Semana 2:  [FASE 2: Services] ←───────┘                      │
           [FASE 3: Traits]                                   │
                  │                                           │
Semana 3:  [FASE 6: Estandarización UI] ←─────────────────────┘
                  │
Semana 4:  [FASE 7: Tests]
```

Fases 1, 4 y 5 son independientes y pueden ejecutarse en paralelo.
Fases 2 y 3 requieren Fase 1.
Fase 6 requiere Fases 4 y 5.
Fase 7 al final como verificación.

---

## Criterios de éxito

- [ ] Cero queries duplicadas entre SalesDashboard y DashboardExportController
- [ ] Controllers Livewire < 80 líneas cada uno (excepto SuppliesController < 120)
- [ ] Cero business units hardcodeadas en vistas
- [ ] Cero iconos Font Awesome (todo SVG)
- [ ] Todos los modals usan `<x-modal>` con focus trap
- [ ] Todos los forms usan `<x-form-field>` con labels asociados
- [ ] Toast notification funciona con 4 variantes
- [ ] Tests cubren todos los módulos principales
- [ ] `vendor/bin/pint --dirty` pasa sin errores
- [ ] `php artisan test` pasa al 100%
