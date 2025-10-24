<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalances extends Model
{
    use HasFactory;

    protected $table = 'employee_leave_balances';

    protected $primaryKey = 'employee_leave_balance_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_leave_type_id',
        'leave_period_start_date',
        'leave_period_end_date',
        'entitled_days',
        'carry_forward_days',
        'leave_taken_days',
        'encashed_days',
        'adjusted_days',
        'balance_days',
        'last_updated_at'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(OrganizationLeaveType::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }



}
