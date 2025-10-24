<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAdvances extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_advances';
    protected $primaryKey = 'organization_payroll_advance_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'advance_date',
        'advance_amount',
        'balance_amount',
        'recovery_months',
        'installment_amount',
        'recovery_start_month',
        'status',
        'remarks',
        'employee_id'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

  
    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }


}
