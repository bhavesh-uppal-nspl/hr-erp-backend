<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSalaryComponent extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_employee_salary_structure_components';
    protected $primaryKey = 'organization_payroll_employee_salary_structure_component_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_configuration_template_id',
        'organization_entity_id',
        'organization_id',
        'organization_payroll_employee_salary_structure_id',
        'organization_payroll_component_id',
        'calculation_method',
        'fixed_amount',
        'percentage_value',
        'percentage_of_component',
        'custom_formula_json',
        'sort_order',
        'is_active',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'organization_payroll_component_id', 'organization_payroll_component_id');
    }

    public function PayrollSlaryStructure()
    {
        return $this->belongsTo(EmployeeSalaryStructure::class, 'organization_payroll_employee_salary_structure_id', 'organization_payroll_employee_salary_structure_id');
    }


}
