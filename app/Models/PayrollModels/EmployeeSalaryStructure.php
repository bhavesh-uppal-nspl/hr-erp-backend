<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_employee_salary_structures';
    protected $primaryKey = 'organization_payroll_employee_salary_structure_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_configuration_template_id',
        'organization_entity_id',
        'organization_id',
        'employee_id',
        'organization_payroll_cycle_id',
        'salary_basis',
        'hourly_salary_amount',
        'daily_salary_amount',
        'monthly_salary_amount',
        'annual_salary_amount',
        'effective_from',
        'effective_to',
        'is_active',
        'effective_from',
        'effective_to',
        'remarks'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }
    public function PayrollCycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'organization_payroll_cycle_id', 'organization_payroll_cycle_id');
    }


}
