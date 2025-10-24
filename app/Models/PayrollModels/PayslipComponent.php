<?php

namespace App\Models\PayrollModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipComponent extends Model
{
    use HasFactory;

    protected $table = 'employee_payslip_components';
    protected $primaryKey = 'employee_payslip_component_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'employee_payslip_id',
        'organization_payroll_component_id',
        'component_name',
        'component_type',
        'amount',
        'remarks',
    ];
   
    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'organization_payroll_component_id', 'organization_payroll_component_id');
    }

    public function EmployeePayslip()
    {
        return $this->belongsTo(EmployeePaySlip::class, 'employee_payslip_id', 'employee_payslip_id');
    }

}
