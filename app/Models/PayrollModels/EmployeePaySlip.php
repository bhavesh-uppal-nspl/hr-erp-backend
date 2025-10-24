<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePaySlip extends Model
{
    use HasFactory;
    protected $table = 'employee_payslips';
    protected $primaryKey = 'employee_payslip_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'employee_id',
        'payroll_run_employee_id',
        'payslip_number',
        'period_start_date',
        'period_end_date',
        'net_pay',
        'gross_pay',
        'deductions_total',
        'status',
        'remarks',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollRunEmployee()
    {
        return $this->belongsTo(PayrollRunEmployee::class, 'payroll_run_employee_id', 'payroll_run_employee_id');
    }
    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }


}
