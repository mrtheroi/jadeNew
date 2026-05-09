<?php

namespace App\Livewire\HumanResources\Forms;

use App\Models\Employee;
use Illuminate\Validation\Rule as VRule;
use Livewire\Form;

class EmployeeForm extends Form
{
    public ?int $selected_id = null;

    // Identificación
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

    public ?string $hired_at = null;

    public bool $is_active = true;

    public ?string $terminated_at = null;

    // Contacto de emergencia
    public ?string $emergency_contact_name = null;

    public ?string $emergency_contact_phone = null;

    public ?string $emergency_contact_relationship = null;

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
            'hired_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'terminated_at' => [
                'nullable', 'date',
                VRule::when(! $this->is_active, ['required']),
            ],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
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
        $this->hired_at = $employee->hired_at?->format('Y-m-d');
        $this->is_active = (bool) $employee->is_active;
        $this->terminated_at = $employee->terminated_at?->format('Y-m-d');
        $this->emergency_contact_name = $employee->emergency_contact_name;
        $this->emergency_contact_phone = $employee->emergency_contact_phone;
        $this->emergency_contact_relationship = $employee->emergency_contact_relationship;
    }

    public function isEditing(): bool
    {
        return $this->selected_id !== null;
    }
}
