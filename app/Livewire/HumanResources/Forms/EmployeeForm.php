<?php

namespace App\Livewire\HumanResources\Forms;

use App\Models\Employee;
use Illuminate\Validation\Rule as VRule;
use Livewire\Form;

class EmployeeForm extends Form
{
    public ?int $selected_id = null;

    // Identificación
    // Para empleados nuevos se asigna automáticamente vía EmployeeNumberGenerator (WYB-XXXX).
    // Para edición conserva el valor original.
    public string $employee_number = '';

    public string $full_name = '';

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $curp = null;

    // Datos personales
    public ?string $birth_date = null;

    public ?string $birth_place = null;

    public ?string $gender = null;

    public ?string $marital_status = null;

    public string $nationality = 'Mexicana';

    public int $children_count = 0;

    public ?string $address = null;

    // Datos laborales
    public string $business_unit = '';

    public ?string $department = null;

    public ?string $manager_name = null;

    public ?string $position = null;

    public ?string $salary_gross = null;

    public ?string $salary_net = null;

    public ?string $salary_period = null;

    public ?string $hired_at = null;

    public bool $is_active = true;

    public ?string $terminated_at = null;

    // Contacto de emergencia
    public ?string $emergency_contact_name = null;

    public ?string $emergency_contact_phone = null;

    public ?string $emergency_contact_relationship = null;

    // Beneficiario (LFT art. 501)
    public ?string $beneficiary_name = null;

    public ?string $beneficiary_relationship = null;

    public ?string $beneficiary_phone = null;

    public string $beneficiary_percentage = '100';

    public function rules(): array
    {
        return [
            'employee_number' => [
                'required', 'string', 'max:50',
                VRule::unique('employees', 'employee_number')->ignore($this->selected_id),
            ],
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'curp' => ['nullable', 'string', 'size:18'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', VRule::in(['Masculino', 'Femenino', 'Otro'])],
            'marital_status' => ['nullable', VRule::in(['Soltero', 'Casado', 'Union libre', 'Divorciado', 'Viudo'])],
            'nationality' => ['required', 'string', 'max:100'],
            'children_count' => ['required', 'integer', 'min:0', 'max:99'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_unit' => ['required', VRule::in(['Jade', 'Jade Orgánico', 'KIN'])],
            'department' => ['nullable', 'string', 'max:100'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:255'],
            'salary_gross' => ['nullable', 'numeric', 'min:0'],
            'salary_net' => ['nullable', 'numeric', 'min:0'],
            'salary_period' => ['nullable', VRule::in(['Semanal', 'Quincenal', 'Mensual'])],
            'hired_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'terminated_at' => [
                'nullable', 'date',
                VRule::when(! $this->is_active, ['required']),
            ],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'beneficiary_relationship' => ['nullable', 'string', 'max:50'],
            'beneficiary_phone' => ['nullable', 'string', 'max:30'],
            'beneficiary_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_number.unique' => 'Ya existe un empleado con ese número.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'terminated_at.required' => 'Si el empleado está dado de baja, hay que indicar la fecha de terminación.',
        ];
    }

    public function fillFromModel(Employee $employee): void
    {
        $this->selected_id = $employee->id;
        $this->employee_number = $employee->employee_number;
        $this->full_name = $employee->full_name;
        $this->email = $employee->email;
        $this->phone = $employee->phone;
        $this->curp = $employee->curp;
        $this->birth_date = $employee->birth_date?->format('Y-m-d');
        $this->birth_place = $employee->birth_place;
        $this->gender = $employee->gender;
        $this->marital_status = $employee->marital_status;
        $this->nationality = $employee->nationality;
        $this->children_count = (int) $employee->children_count;
        $this->address = $employee->address;
        $this->business_unit = $employee->business_unit;
        $this->department = $employee->department;
        $this->manager_name = $employee->manager_name;
        $this->position = $employee->position;
        $this->salary_gross = $employee->salary_gross !== null ? (string) $employee->salary_gross : null;
        $this->salary_net = $employee->salary_net !== null ? (string) $employee->salary_net : null;
        $this->salary_period = $employee->salary_period;
        $this->hired_at = $employee->hired_at?->format('Y-m-d');
        $this->is_active = (bool) $employee->is_active;
        $this->terminated_at = $employee->terminated_at?->format('Y-m-d');
        $this->emergency_contact_name = $employee->emergency_contact_name;
        $this->emergency_contact_phone = $employee->emergency_contact_phone;
        $this->emergency_contact_relationship = $employee->emergency_contact_relationship;
        $this->beneficiary_name = $employee->beneficiary_name;
        $this->beneficiary_relationship = $employee->beneficiary_relationship;
        $this->beneficiary_phone = $employee->beneficiary_phone;
        $this->beneficiary_percentage = $employee->beneficiary_percentage !== null ? (string) $employee->beneficiary_percentage : '100';
    }

    public function isEditing(): bool
    {
        return $this->selected_id !== null;
    }
}
