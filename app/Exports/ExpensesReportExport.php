<?php

namespace App\Exports;

use App\Exports\Sheets\ExpensesDetailSheet;
use App\Exports\Sheets\ExpensesSummarySheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class ExpensesReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            new ExpensesSummarySheet($this->data),
            new ExpensesDetailSheet($this->data),
        ];
    }
}
