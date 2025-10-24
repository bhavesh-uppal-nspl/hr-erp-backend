<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLoan extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_loans';

    protected $primaryKey = 'organization_payroll_loan_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_entity_id',
        'organization_id',
        'organization_payroll_loan_type_id',
        'loan_date',
        'loan_amount',
        'balance_amount',
        'interest_rate',
        'emi_amount',
        'total_installments',
        'installments_remaining',
        'repayment_start_month',
        'status',
        'remarks'

    ];


       public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


        public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

           public function LoanType()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }
   
}
