<?php

namespace App\Http\Controllers;

use App\Domain\BusinessUnit;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardExportController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    private function validateExportInput(Request $request): array
    {
        return $request->validate([
            'business_unit' => ['nullable', 'string', new Enum(BusinessUnit::class)],
            'period_key' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);
    }

    public function excel(Request $request): StreamedResponse
    {
        $validated = $this->validateExportInput($request);
        $data = $this->dashboard->getFullDashboardData(
            $validated['business_unit'] ?? null,
            $validated['period_key'] ?? null,
        );

        $fileName = 'reporte_ventas_'.$data['periodKey'].'_'.now()->format('His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Reporte de Ventas - '.$data['businessUnit']]);
            fputcsv($handle, ['Periodo: '.$data['from'].' a '.$data['to']]);
            fputcsv($handle, []);

            fputcsv($handle, ['KPIs Principales']);
            fputcsv($handle, ['Total Ventas', number_format($data['totalSales'], 2)]);
            fputcsv($handle, ['Total Gastos', number_format($data['totalExpenses'], 2)]);
            fputcsv($handle, ['Utilidad', number_format($data['profit'], 2), $data['profitPercent'].'%']);
            fputcsv($handle, []);

            fputcsv($handle, ['Desglose por Categoria']);
            fputcsv($handle, ['Alimentos', number_format($data['totalAlimentos'], 2)]);
            fputcsv($handle, ['Bebidas', number_format($data['totalBebidas'], 2)]);
            fputcsv($handle, ['Otros', number_format($data['totalOtros'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['Metodos de Pago', 'Monto', 'Propina']);
            fputcsv($handle, ['Efectivo', number_format($data['efectivoMonto'], 2), number_format($data['efectivoPropina'], 2)]);
            fputcsv($handle, ['T. Debito', number_format($data['debitoMonto'], 2), number_format($data['debitoPropina'], 2)]);
            fputcsv($handle, ['T. Credito', number_format($data['creditoMonto'], 2), number_format($data['creditoPropina'], 2)]);
            fputcsv($handle, ['Credito Cliente', number_format($data['creditoClienteMonto'], 2), number_format($data['creditoClientePropina'], 2)]);
            fputcsv($handle, ['Total Propinas', '', number_format($data['totalPropinas'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['Metricas Operativas']);
            fputcsv($handle, ['Total Personas', $data['totalPersonas']]);
            fputcsv($handle, ['Total Cuentas', $data['totalCuentas']]);
            fputcsv($handle, ['Ticket Promedio', number_format($data['ticketPromedio'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['Comparativo por Turno', 'Ventas', 'Personas', 'Ticket Promedio']);
            fputcsv($handle, ['Matutino', number_format($data['turno1Ventas'], 2), $data['turno1Personas'], number_format($data['turno1Ticket'], 2)]);
            fputcsv($handle, ['Vespertino', number_format($data['turno2Ventas'], 2), $data['turno2Personas'], number_format($data['turno2Ticket'], 2)]);

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function pdf(Request $request)
    {
        $validated = $this->validateExportInput($request);
        $data = $this->dashboard->getFullDashboardData(
            $validated['business_unit'] ?? null,
            $validated['period_key'] ?? null,
        );

        $pdf = \PDF::loadView('exports.dashboard-ventas-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'reporte_ventas_'.$data['periodKey'].'_'.now()->format('His').'.pdf';

        return $pdf->download($fileName);
    }

    public function estadoResultadosExcel(Request $request): StreamedResponse
    {
        $validated = $this->validateExportInput($request);
        $data = $this->dashboard->getFullDashboardData(
            $validated['business_unit'] ?? null,
            $validated['period_key'] ?? null,
        );

        $fileName = 'estado_resultados_'.$data['periodKey'].'_'.now()->format('His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Estado de Resultados - '.$data['businessUnit']]);
            fputcsv($handle, ['Periodo: '.$data['from'].' a '.$data['to']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Concepto', 'Monto', '% del Ingreso']);
            fputcsv($handle, []);

            fputcsv($handle, ['INGRESO', number_format($data['totalSales'], 2), '100%']);
            fputcsv($handle, ['  Subtotal', number_format($data['totalSubtotal'], 2)]);
            fputcsv($handle, ['  IVA', number_format($data['totalIva'], 2)]);
            fputcsv($handle, ['  Alimentos', number_format($data['totalAlimentos'], 2)]);
            fputcsv($handle, ['  Bebidas', number_format($data['totalBebidas'], 2)]);
            fputcsv($handle, ['  Otros', number_format($data['totalOtros'], 2)]);
            fputcsv($handle, []);

            foreach ($data['expenseGroups'] as $group) {
                fputcsv($handle, [strtoupper($group['expense_type'])]);

                foreach ($group['categories'] as $cat) {
                    fputcsv($handle, ['  '.$cat['name'], number_format($cat['amount'], 2), $cat['percent'].'%']);
                }

                fputcsv($handle, ['TOTAL '.strtoupper($group['expense_type']), number_format($group['total'], 2), $group['percent'].'%']);
                fputcsv($handle, []);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL GASTOS', number_format($data['grandTotalExpenses'], 2), $data['totalSales'] > 0 ? round(($data['grandTotalExpenses'] / $data['totalSales']) * 100, 2).'%' : '0%']);
            fputcsv($handle, ['UTILIDAD', number_format($data['profit'], 2), $data['profitPercent'].'%']);

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    public function estadoResultadosPdf(Request $request)
    {
        $validated = $this->validateExportInput($request);
        $data = $this->dashboard->getFullDashboardData(
            $validated['business_unit'] ?? null,
            $validated['period_key'] ?? null,
        );

        $pdf = \PDF::loadView('exports.dashboard-ventas-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'estado_resultados_'.$data['periodKey'].'_'.now()->format('His').'.pdf';

        return $pdf->download($fileName);
    }
}
