<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveCategory;
use App\Models\OrganizationModel\OrganizationLeaveReason;
use App\Models\OrganizationModel\OrganizationLeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMedical extends Model
{
    use HasFactory;

    protected $table = 'employee_medicals';

    protected $primaryKey = 'employee_medical_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'blood_group',
        'diseases',
        'disability_status',
        'is_fit_for_duty',
        'disability_description',
        'last_health_check_date',
        'medical_notes', 
        'allergies',
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

    public function leaveCategory()
    {
        return $this->belongsTo(OrganizationLeaveCategory::class, 'organization_leave_category_id', 'organization_leave_category_id');
    }

    public function leaveReason()
    {
        return $this->belongsTo(OrganizationLeaveReason::class, 'organization_leave_reason_id', 'organization_leave_reason_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employees::class, 'approved_by', 'employee_id');
    }
}
