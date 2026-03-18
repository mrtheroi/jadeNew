<?php

namespace App\Http\Controllers;

use App\Models\DailySale;
use App\Models\ExpenseType;
use App\Models\Supply;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardExportController extends Controller
{
    private function periodRange(Request $request): array
    {
        $pk = $request->input('period_key', now()->format('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $pk)) {
            $pk = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('Y-m', $pk)->startOfMonth()->toDateString();
        $end = Carbon::createFromFormat('Y-m', $pk)->endOfMonth()->toDateString();

        return [$start, $end, $pk];
    }

    private function salesQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        [$from, $to] = $this->periodRange($request);
        $businessUnit = $request->input('business_unit');

        $q = DailySale::query()
            ->where('status', 'completed')
            ->whereBetween('operation_date', [$from, $to]);

        if ($businessUnit) {
            $q->where('business_unit', $businessUnit);
        }

        return $q;
    }

    private function buildSalesData(Request $request): array
    {
        [$from, $to, $periodKey] = $this->periodRange($request);
        $businessUnit = $request->input('business_unit');
        $salesQuery = $this->salesQuery($request);

        $salesTotals = (clone $salesQuery)->selectRaw('
            COALESCE(SUM(total), 0) as total_sales,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(iva), 0) as total_iva,
            COALESCE(SUM(alimentos), 0) as total_alimentos,
            COALESCE(SUM(bebidas), 0) as total_bebidas,
            COALESCE(SUM(otros), 0) as total_otros,
            COALESCE(SUM(efectivo_monto), 0) as total_efectivo_monto,
            COALESCE(SUM(efectivo_propina), 0) as total_efectivo_propina,
            COALESCE(SUM(debito_monto), 0) as total_debito_monto,
            COALESCE(SUM(debito_propina), 0) as total_debito_propina,
            COALESCE(SUM(credito_monto), 0) as total_credito_monto,
            COALESCE(SUM(credito_propina), 0) as total_credito_propina,
            COALESCE(SUM(credito_cliente_monto), 0) as total_credito_cliente_monto,
            COALESCE(SUM(credito_cliente_propina), 0) as total_credito_cliente_propina,
            COALESCE(SUM(numero_personas), 0) as total_personas,
            COALESCE(SUM(numero_cuentas), 0) as total_cuentas
        ')->first();

        $totalSales = (float) $salesTotals->total_sales;
        $totalPersonas = (int) $salesTotals->total_personas;

        // Expenses
        $expensesQuery = Supply::query()
            ->join('categories', 'supplies.category_id', '=', 'categories.id')
            ->whereBetween('supplies.payment_date', [$from, $to])
            ->where('supplies.status', '!=', 'cancelado');

        if ($businessUnit) {
            $expensesQuery->where('categories.business_unit', $businessUnit);
        }

        $totalExpenses = (float) (clone $expensesQuery)
            ->selectRaw('COALESCE(SUM(ABS(supplies.amount)), 0) as total')
            ->value('total');

        // Turno data
        $turnoData = (clone $salesQuery)
            ->selectRaw('turno, COALESCE(SUM(total), 0) as total_ventas, COALESCE(SUM(numero_personas), 0) as total_personas')
            ->groupBy('turno')
            ->get()
            ->keyBy('turno');

        $turno1Ventas = (float) ($turnoData[1]->total_ventas ?? 0);
        $turno1Personas = (int) ($turnoData[1]->total_personas ?? 0);
        $turno2Ventas = (float) ($turnoData[2]->total_ventas ?? 0);
        $turno2Personas = (int) ($turnoData[2]->total_personas ?? 0);

        $profit = $totalSales - $totalExpenses;

        return [
            'periodKey' => $periodKey,
            'from' => $from,
            'to' => $to,
            'businessUnit' => $businessUnit ?: 'Todas',
            'totalSales' => $totalSales,
            'totalSubtotal' => (float) $salesTotals->total_subtotal,
            'totalIva' => (float) $salesTotals->total_iva,
            'totalAlimentos' => (float) $salesTotals->total_alimentos,
            'totalBebidas' => (float) $salesTotals->total_bebidas,
            'totalOtros' => (float) $salesTotals->total_otros,
            'efectivoMonto' => (float) $salesTotals->total_efectivo_monto,
            'efectivoPropina' => (float) $salesTotals->total_efectivo_propina,
            'debitoMonto' => (float) $salesTotals->total_debito_monto,
            'debitoPropina' => (float) $salesTotals->total_debito_propina,
            'creditoMonto' => (float) $salesTotals->total_credito_monto,
            'creditoPropina' => (float) $salesTotals->total_credito_propina,
            'creditoClienteMonto' => (float) $salesTotals->total_credito_cliente_monto,
            'creditoClientePropina' => (float) $salesTotals->total_credito_cliente_propina,
            'totalPropinas' => (float) $salesTotals->total_efectivo_propina + (float) $salesTotals->total_debito_propina + (float) $salesTotals->total_credito_propina + (float) $salesTotals->total_credito_cliente_propina,
            'totalPersonas' => $totalPersonas,
            'totalCuentas' => (int) $salesTotals->total_cuentas,
            'ticketPromedio' => $totalPersonas > 0 ? round($totalSales / $totalPersonas, 2) : 0,
            'totalExpenses' => $totalExpenses,
            'profit' => $profit,
            'profitPercent' => $totalSales > 0 ? round(($profit / $totalSales) * 100, 2) : 0,
            'turno1Ventas' => $turno1Ventas,
            'turno1Personas' => $turno1Personas,
            'turno1Ticket' => $turno1Personas > 0 ? round($turno1Ventas / $turno1Personas, 2) : 0,
            'turno2Ventas' => $turno2Ventas,
            'turno2Personas' => $turno2Personas,
            'turno2Ticket' => $turno2Personas > 0 ? round($turno2Ventas / $turno2Personas, 2) : 0,
        ];
    }

    private function buildEstadoResultadosData(Request $request): array
    {
        [$from, $to, $periodKey] = $this->periodRange($request);
        $businessUnit = $request->input('business_unit');

        $salesQuery = $this->salesQuery($request);

        $salesTotals = (clone $salesQuery)->selectRaw('
            COALESCE(SUM(total), 0) as total_sales,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(iva), 0) as total_iva,
            COALESCE(SUM(alimentos), 0) as total_alimentos,
            COALESCE(SUM(bebidas), 0) as total_bebidas,
            COALESCE(SUM(otros), 0) as total_otros
        ')->first();

        $totalSales = (float) $salesTotals->total_sales;

        $expenseTypes = ExpenseType::query()
            ->where('is_active', true)
            ->orderBy('expense_type_name')
            ->get();

        $groups = [];
        $grandTotalExpenses = 0;

        foreach ($expenseTypes as $et) {
            $categorySums = Supply::query()
                ->join('categories', 'supplies.category_id', '=', 'categories.id')
                ->where('categories.expense_type_id', $et->id)
                ->whereBetween('supplies.payment_date', [$from, $to])
                ->where('supplies.status', '!=', 'cancelado')
                ->when($businessUnit, fn ($q) => $q->where('categories.business_unit', $businessUnit))
                ->selectRaw('categories.expense_name, COALESCE(SUM(ABS(supplies.amount)), 0) as total_amount')
                ->groupBy('categories.expense_name')
                ->orderBy('categories.expense_name')
                ->get();

            $groupTotal = $categorySums->sum('total_amount');
            $grandTotalExpenses += $groupTotal;

            $categories = $categorySums->map(fn ($row) => [
                'name' => $row->expense_name,
                'amount' => (float) $row->total_amount,
                'percent' => $totalSales > 0 ? round(((float) $row->total_amount / $totalSales) * 100, 2) : 0,
            ])->toArray();

            $groups[] = [
                'expense_type' => $et->expense_type_name,
                'categories' => $categories,
                'total' => $groupTotal,
                'percent' => $totalSales > 0 ? round(($groupTotal / $totalSales) * 100, 2) : 0,
            ];
        }

        $profit = $totalSales - $grandTotalExpenses;

        return [
            'periodKey' => $periodKey,
            'from' => $from,
            'to' => $to,
            'businessUnit' => $businessUnit ?: 'Todas',
            'totalSales' => $totalSales,
            'totalSubtotal' => (float) $salesTotals->total_subtotal,
            'totalIva' => (float) $salesTotals->total_iva,
            'totalAlimentos' => (float) $salesTotals->total_alimentos,
            'totalBebidas' => (float) $salesTotals->total_bebidas,
            'totalOtros' => (float) $salesTotals->total_otros,
            'groups' => $groups,
            'grandTotalExpenses' => $grandTotalExpenses,
            'profit' => $profit,
            'profitPercent' => $totalSales > 0 ? round(($profit / $totalSales) * 100, 2) : 0,
        ];
    }

    public function excel(Request $request): StreamedResponse
    {
        $data = $this->buildSalesData($request);

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
        $data = $this->buildSalesData($request);

        $pdf = \PDF::loadView('exports.dashboard-ventas-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'reporte_ventas_'.$data['periodKey'].'_'.now()->format('His').'.pdf';

        return $pdf->download($fileName);
    }

    public function estadoResultadosExcel(Request $request): StreamedResponse
    {
        $data = $this->buildEstadoResultadosData($request);

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

            foreach ($data['groups'] as $group) {
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
        $data = $this->buildEstadoResultadosData($request);

        $pdf = \PDF::loadView('exports.dashboard-ventas-pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'estado_resultados_'.$data['periodKey'].'_'.now()->format('His').'.pdf';

        return $pdf->download($fileName);
    }
}
