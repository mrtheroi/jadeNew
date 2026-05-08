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
        'hired_at',
        'is_active',
        'terminated_at',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'hired_at' => 'date',
            'terminated_at' => 'date',
            'is_active' => 'boolean',
            'children_count' => 'integer',
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
}
