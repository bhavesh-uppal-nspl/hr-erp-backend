<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityTransaction extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_security_transactions';

    protected $primaryKey = 'organization_payroll_security_transaction_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_payroll_security_id',
        'organization_id',
        'employee_id',
        'transaction_date',
        'amount',
        'payment_mode',
        'reference_no',
        'remarks'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
    public function PayrollSecurity()
    {
        return $this->belongsTo(PayrollSecurity::class, 'organization_payroll_security_id', 'organization_payroll_security_id');
    }

    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }


}
