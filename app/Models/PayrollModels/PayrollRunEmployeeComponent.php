<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRunEmployeeComponent extends Model
{
    use HasFactory;
    protected $table = 'PayrollRunEmployeeComponents';

    protected $primaryKey = 'PayrollRunEmployeeComponent_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'organization_payroll_run_employee_id',
        'organization_payroll_component_id',
        'component_type',
        'amount',
        'remarks',
    ];


    public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollRun()
    {
        return $this->belongsTo(PayrollRun::class, 'organization_payroll_run_id', 'organization_payroll_run_id');
    }
    public function PayrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'organization_payroll_component_id', 'organization_payroll_component_id');
    }

}
