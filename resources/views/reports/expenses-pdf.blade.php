<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de gastos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#111; }
        .muted { color:#6b7280; }
        h1 { font-size: 16px; margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
<h1>Reporte de gastos</h1>
<div class="muted">
    Unidad: <strong>{{ $meta['business_unit'] }}</strong> |
    Periodo: <strong>{{ $meta['period_key'] ?? '—' }}</strong> |
    Generado: <strong>{{ $meta['generated_at'] }}</strong>
</div>
<div class="muted" style="margin-top:6px;">
    Rango: <strong>{{ $meta['from_date'] ?? '—' }}</strong> a <strong>{{ $meta['to_date'] ?? '—' }}</strong> |
    Ingreso bancario: <strong>{{ $meta['income_amount'] !== null ? '$ '.number_format($meta['income_amount'],2) : '—' }}</strong>
</div>

<table>
    <thead>
    <tr>
        <th>Tipo de gasto</th>
        <th>Categoría</th>
        <th class="right">Gasto</th>
        <th class="right">Ajustes</th>
        <th class="right">Neto</th>
        <th class="right">% sobre ingreso</th>
    </tr>
    </thead>
    <tbody>
    @foreach($summary as $row)
        @php
            $pct = null;
            if(!empty($meta['income_amount']) && (float)$meta['income_amount'] > 0) {
                $pct = (float)$row['net'] / (float)$meta['income_amount'];
            }
        @endphp
        <tr>
            <td>{{ $row['expense_type'] }}</td>
            <td>{{ $row['category'] }}</td>
            <td class="right">$ {{ number_format($row['gross'],2) }}</td>
            <td class="right">$ {{ number_format($row['adjustments'],2) }}</td>
            <td class="right">$ {{ number_format($row['net'],2) }}</td>
            <td class="right">{{ $pct !== null ? number_format($pct*100,2).'%' : '—' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="2" class="right">TOTALES</th>
        <th class="right">$ {{ number_format($totals['gross_total'],2) }}</th>
        <th class="right">$ {{ number_format($totals['adjust_total'],2) }}</th>
        <th class="right">$ {{ number_format($totals['net_total'],2) }}</th>
        <th class="right">{{ $totals['pct_income'] !== null ? number_format($totals['pct_income']*100,2).'%' : '—' }}</th>
    </tr>
    </tfoot>
</table>

<p class="muted" style="margin-top:10px;">
    Nota: El PDF incluye el resumen. El detalle completo se exporta en Excel.
</p>
</body>
</html>
