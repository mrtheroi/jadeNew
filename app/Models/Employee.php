<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'full_name',
        'email',
        'phone',
        'curp',
        'birth_date',
        'birth_place',
        'gender',
        'marital_status',
        'nationality',
        'children_count',
        'address',
        'business_unit',
        'department',
        'manager_name',
        'position',
        'salary_gross',
        'salary_net',
        'salary_period',
        'hired_at',
        'is_active',
        'terminated_at',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'beneficiary_name',
        'beneficiary_relationship',
        'beneficiary_phone',
        'beneficiary_percentage',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hired_at' => 'date',
            'terminated_at' => 'date',
            'is_active' => 'boolean',
            'children_count' => 'integer',
            'salary_gross' => 'decimal:2',
            'salary_net' => 'decimal:2',
            'beneficiary_percentage' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Edad calculada desde birth_date. Solo lectura, no se persiste.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    /**
     * Días por periodo según salary_period (convención nómina mexicana).
     */
    public const SALARY_PERIOD_DIVISORS = [
        'Semanal' => 7,
        'Quincenal' => 15,
        'Mensual' => 30,
    ];

    /**
     * Salario diario calculado a partir del bruto y la periodicidad.
     * Necesario para la cláusula novena del contrato (art. 60 LFT).
     */
    public function dailySalary(): ?float
    {
        $gross = $this->salary_gross;
        $period = $this->salary_period;

        if ($gross === null || ! isset(self::SALARY_PERIOD_DIVISORS[$period])) {
            return null;
        }

        return round((float) $gross / self::SALARY_PERIOD_DIVISORS[$period], 2);
    }

    /**
     * Salario diario en letras formato cheque mexicano: "(DOSCIENTOS CUARENTA Y OCHO 93/100)".
     * Útil para el contrato laboral.
     */
    public function dailySalaryInWords(): ?string
    {
        $amount = $this->dailySalary();

        if ($amount === null) {
            return null;
        }

        $integer = (int) floor($amount);
        $cents = (int) round(($amount - $integer) * 100);

        $formatter = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);
        $integerWords = mb_strtoupper($formatter->format($integer));

        return sprintf('(%s %02d/100)', $integerWords, $cents);
    }

    /**
     * Datos de la empresa (razón social, RFC, etc.) según la unidad de negocio del empleado.
     * Lee de config/company.php. Si la unidad no está configurada, retorna array vacío.
     *
     * @return array<string, string>
     */
    public function companyData(): array
    {
        return config('company.units.'.$this->business_unit, []);
    }
}
