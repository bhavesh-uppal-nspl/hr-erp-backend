<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollLoanTranscation extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_loan_transactions';

    protected $primaryKey = 'organization_payroll_loan_transaction_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_entity_id',
        'organization_id',
        'organization_payroll_loan_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'payment_mode',
        'reference_no',
        'remarks',

    ];


       public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


        public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

           public function Loan()
    {
        return $this->belongsTo(PayrollLoan::class, 'organization_payroll_loan_id', 'organization_payroll_loan_id');
    }
   
}
