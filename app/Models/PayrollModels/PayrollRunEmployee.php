<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRunEmployee extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_run_employees';

    protected $primaryKey = 'organization_payroll_run_employee_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'organization_payroll_run_id',
        'employee_id',
        'gross_earnings',
        'total_deductions',
        'net_pay',
        'status',
        'remarks'

    ];


       public function Organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function PayrollRun()
    {
        return $this->belongsTo(PayrollRun::class, 'organization_payroll_run_id', 'organization_payroll_run_id');
    }

}
