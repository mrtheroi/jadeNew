<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PurchaseOrderPdfController extends Controller
{
    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load([
            'supplies.category.expenseType',
            'creator',
        ]);

        $byProvider = $purchaseOrder->supplies->groupBy(
            fn ($s) => $s->category?->provider_name ?? 'Sin proveedor'
        );

        $pdf = Pdf::loadView('reports.purchase-order-pdf', [
            'oc' => $purchaseOrder,
            'byProvider' => $byProvider,
        ])->setPaper('letter');

        $filename = sprintf('%s.pdf', $purchaseOrder->oc_number);

        return $pdf->stream($filename);
    }
}
