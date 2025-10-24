<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollReimbursement extends Model
{
    use HasFactory;
    protected $table = 'PayrollReimbursements';

    protected $primaryKey = 'organization_payroll_reimbursement_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'employee_id',
        'organization_payroll_reimbursement_type_id',
        'claim_date',
        'claim_amount',
        'approved_amount',
        'status',
        'remarks'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollReimbursementType()
    {
        return $this->belongsTo(PayrollReimbursmentType::class, 'organization_payroll_reimbursement_type_id', 'organization_payroll_reimbursement_type_id');
    }
    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

}
