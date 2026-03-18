<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Resultados</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 20px; }
        h1 { font-size: 16px; margin-bottom: 2px; color: #111827; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #374151; border-bottom: 2px solid #d1d5db; }
        td { padding: 4px 8px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .group-header td { background: #f9fafb; font-weight: bold; font-size: 10px; padding-top: 8px; border-bottom: 1px solid #d1d5db; }
        .group-total td { font-weight: bold; border-top: 1px solid #9ca3af; background: #f3f4f6; }
        .category td { padding-left: 20px; font-size: 9px; }
        .ingreso td { background: #ecfdf5; font-weight: bold; font-size: 11px; border-bottom: 2px solid #10b981; }
        .grand-total td { background: #fef2f2; font-weight: bold; font-size: 10px; border-top: 2px solid #ef4444; }
        .profit td { background: #eef2ff; font-weight: bold; font-size: 11px; border-top: 2px solid #6366f1; }
        .profit.positive td { color: #059669; }
        .profit.negative td { color: #dc2626; }
        .summary-box { margin-top: 20px; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; }
        .summary-box h3 { font-size: 11px; margin: 0 0 8px 0; color: #374151; }
        .summary-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 9px; }
        .sub-detail { font-size: 8px; color: #6b7280; padding-left: 20px; }
    </style>
</head>
<body>

<h1>Estado de Resultados</h1>
<p class="meta">
    Unidad: {{ $businessUnit }} &middot;
    Periodo: {{ $from }} &rarr; {{ $to }}
    &middot; Generado: {{ now()->format('Y-m-d H:i') }}
</p>

<table>
    <thead>
    <tr>
        <th style="width: 55%">Concepto</th>
        <th class="text-right" style="width: 25%">Monto</th>
        <th class="text-right" style="width: 20%">% Ingreso</th>
    </tr>
    </thead>
    <tbody>

    {{-- INGRESO --}}
    <tr class="ingreso">
        <td>INGRESO</td>
        <td class="text-right">$ {{ number_format($totalSales, 2) }}</td>
        <td class="text-right">100%</td>
    </tr>
    <tr class="sub-detail">
        <td style="padding-left: 20px; font-size: 8px; color: #6b7280;">
            Alimentos: ${{ number_format($totalAlimentos, 2) }} |
            Bebidas: ${{ number_format($totalBebidas, 2) }} |
            Otros: ${{ number_format($totalOtros, 2) }}
        </td>
        <td class="text-right" style="font-size: 8px; color: #6b7280;">Subtotal: ${{ number_format($totalSubtotal, 2) }}</td>
        <td class="text-right" style="font-size: 8px; color: #6b7280;">IVA: ${{ number_format($totalIva, 2) }}</td>
    </tr>

    @php $grandExpensePercent = $totalSales > 0 ? round(($grandTotalExpenses / $totalSales) * 100, 2) : 0; @endphp

    {{-- EXPENSE GROUPS --}}
    @foreach($groups as $group)
        <tr class="group-header">
            <td colspan="3">{{ strtoupper($group['expense_type']) }}</td>
        </tr>

        @foreach($group['categories'] as $cat)
            <tr class="category">
                <td>{{ $cat['name'] }}</td>
                <td class="text-right">$ {{ number_format($cat['amount'], 2) }}</td>
                <td class="text-right">{{ $cat['percent'] }}%</td>
            </tr>
        @endforeach

        <tr class="group-total">
            <td>TOTAL {{ strtoupper($group['expense_type']) }}</td>
            <td class="text-right">$ {{ number_format($group['total'], 2) }}</td>
            <td class="text-right">{{ $group['percent'] }}%</td>
        </tr>
    @endforeach

    {{-- GRAND TOTAL EXPENSES --}}
    <tr class="grand-total">
        <td>TOTAL GASTOS</td>
        <td class="text-right">$ {{ number_format($grandTotalExpenses, 2) }}</td>
        <td class="text-right">{{ $grandExpensePercent }}%</td>
    </tr>

    {{-- PROFIT --}}
    <tr class="profit {{ $profit >= 0 ? 'positive' : 'negative' }}">
        <td>UTILIDAD</td>
        <td class="text-right">$ {{ number_format($profit, 2) }}</td>
        <td class="text-right">{{ $profitPercent }}%</td>
    </tr>

    </tbody>
</table>

</body>
</html>
