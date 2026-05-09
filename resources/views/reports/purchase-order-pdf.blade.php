<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $oc->oc_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; color: #111827; }
        h2 { font-size: 13px; margin: 0; color: #374151; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 16px; }
        .header-row { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: top; }
        .meta { font-size: 11px; color: #6b7280; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        .badge-closed { background: #ecfdf5; color: #047857; }
        .badge-cancelled { background: #fef2f2; color: #b91c1c; }
        .summary { background: #f9fafb; border: 1px solid #e5e7eb; padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; }
        .summary-row { display: table; width: 100%; }
        .summary-cell { display: table-cell; padding: 4px 8px; }
        .summary-label { color: #6b7280; font-size: 10px; text-transform: uppercase; }
        .summary-value { font-weight: 700; font-size: 12px; }

        .provider-block { border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 12px; }
        .provider-header { background: #eef2ff; color: #3730a3; padding: 6px 10px; font-weight: 700; display: table; width: 100%; border-radius: 6px 6px 0 0; }
        .provider-name { display: table-cell; vertical-align: middle; }
        .provider-subtotal { display: table-cell; vertical-align: middle; text-align: right; font-size: 12px; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items th, table.items td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; text-align: left; font-size: 10px; }
        table.items th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 9px; }
        table.items .num { text-align: right; font-variant-numeric: tabular-nums; }

        .totals { margin-top: 16px; padding: 10px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; display: table; width: 100%; }
        .totals-label { display: table-cell; font-weight: 700; font-size: 13px; color: #047857; }
        .totals-value { display: table-cell; text-align: right; font-weight: 700; font-size: 16px; color: #047857; }

        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 9px; }
        .signature { margin-top: 36px; display: table; width: 100%; }
        .signature-cell { display: table-cell; width: 50%; padding: 0 16px; text-align: center; }
        .signature-line { border-top: 1px solid #1f2937; margin-bottom: 4px; padding-top: 36px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-row">
            <div class="header-cell">
                <h1>Orden de Compra</h1>
                <h2>{{ $oc->oc_number }}</h2>
                <p class="meta">Documento interno de control · Jade {{ $oc->business_unit }}</p>
            </div>
            <div class="header-cell" style="text-align: right;">
                @if($oc->isClosed())
                    <span class="badge badge-closed">Cerrada</span>
                @else
                    <span class="badge badge-cancelled">Anulada</span>
                @endif
            </div>
        </div>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-cell">
                <div class="summary-label">Fecha</div>
                <div class="summary-value">{{ $oc->oc_date->format('Y-m-d') }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Unidad</div>
                <div class="summary-value">{{ $oc->business_unit }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Compras</div>
                <div class="summary-value">{{ $oc->total_items }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Generada</div>
                <div class="summary-value">{{ $oc->closed_at?->format('Y-m-d H:i') ?? '—' }}</div>
            </div>
            <div class="summary-cell">
                <div class="summary-label">Por</div>
                <div class="summary-value">{{ $oc->creator?->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    @foreach($byProvider as $providerName => $items)
        @php $providerSubtotal = (float) $items->sum('amount'); @endphp
        <div class="provider-block">
            <div class="provider-header">
                <span class="provider-name">{{ $providerName }}</span>
                <span class="provider-subtotal">$ {{ number_format($providerSubtotal, 2) }}</span>
            </div>
            <table class="items">
                <thead>
                    <tr>
                        <th>Tipo / Categoría</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="num">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $supply)
                        <tr>
                            <td>
                                <strong>{{ $supply->category?->expenseType?->expense_type_name ?? '—' }}</strong>
                                — {{ $supply->category?->expense_name ?? '—' }}
                            </td>
                            <td>{{ $supply->payment_type ?? '—' }}</td>
                            <td>{{ ucfirst($supply->status ?? '—') }}</td>
                            <td class="num">$ {{ number_format((float) $supply->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="totals">
        <div class="totals-label">TOTAL ORDEN DE COMPRA</div>
        <div class="totals-value">$ {{ number_format((float) $oc->total_amount, 2) }}</div>
    </div>

    @if($oc->notes)
        <div style="margin-top: 12px;">
            <strong>Notas:</strong> {{ $oc->notes }}
        </div>
    @endif

    <div class="signature">
        <div class="signature-cell">
            <div class="signature-line"></div>
            <div>Generó la OC</div>
            <div class="meta">{{ $oc->creator?->name ?? '—' }}</div>
        </div>
        <div class="signature-cell">
            <div class="signature-line"></div>
            <div>Recibió / Aprobó</div>
            <div class="meta">&nbsp;</div>
        </div>
    </div>

    <div class="footer">
        Documento generado el {{ now()->format('Y-m-d H:i') }} desde el sistema Jade.
    </div>
</body>
</html>
