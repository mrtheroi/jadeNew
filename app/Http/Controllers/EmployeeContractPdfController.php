<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EmployeeContractPdfController extends Controller
{
    public function show(Employee $employee): Response
    {
        // El contrato es un documento legal en español — forzamos locale es
        // para que Carbon::isoFormat() rinda los meses en español.
        Carbon::setLocale('es');

        $pdf = Pdf::loadView('employees.contract-pdf', [
            'employee' => $employee,
            'company' => $employee->companyData(),
            'dailySalary' => $employee->dailySalary(),
            'dailySalaryInWords' => $employee->dailySalaryInWords(),
        ])->setPaper('letter');

        $filename = sprintf(
            'Contrato_%s_%s.pdf',
            preg_replace('/\s+/', '_', $employee->full_name),
            $employee->employee_number,
        );

        return $pdf->stream($filename);
    }
}
