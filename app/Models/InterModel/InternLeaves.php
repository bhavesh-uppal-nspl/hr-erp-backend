<?php

namespace App\Models\InterModel;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveCategory;
use App\Models\OrganizationModel\OrganizationLeaveReason;
use App\Models\OrganizationModel\OrganizationLeaveReasonType;
use App\Models\OrganizationModel\OrganizationLeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternLeaves extends Model
{
    use HasFactory;

    protected $table = 'intern_leaves';

    protected $primaryKey = 'intern_leave_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'organization_leave_type_id',
        'organization_leave_category_id',
        'organization_leave_reason_id',
        'leave_duration_type',
        'total_leave_days',
        'leave_start_date',
        'leave_end_date',
        'intern_remarks',
        'leave_start_time',
        'leave_end_time',
        'leave_status',
        'approved_by',
        'approval_date',
        'supporting_document_url',
        'leave_rejection_reason'
 

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }



     public function RejectedBy()
    {
        return $this->belongsTo(Employees::class, 'rejected_by', 'employee_id');
    }
     public function ApprovedBy()
    {
        return $this->belongsTo(Employees::class, 'approved_by', 'employee_id');
    }

    public function leaveReason()
    {
        return $this->belongsTo(OrganizationLeaveReason::class, 'organization_leave_reason_id', 'organization_leave_reason_id');
    }
   

     public function leaveReasonType()
    {
        return $this->belongsTo(OrganizationLeaveReasonType::class, 'organization_leave_reason_type_id', 'organization_leave_reason_type_id');
    }

   

    public function leaveType()
    {
        return $this->belongsTo(OrganizationLeaveType::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }

    


    public function leaveCategory()
    {
        return $this->belongsTo(OrganizationLeaveCategory::class, 'organization_leave_category_id', 'organization_leave_category_id');
    }

   

    public function approver()
    {
        return $this->belongsTo(Employees::class, 'approved_by', 'employee_id');
    }

}
