<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeaveEntitlement extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_entitlements';

    // Define the primary key
    protected $primaryKey = 'organization_leave_entitlement_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    //  public $timestamps = false;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'organization_entity_id',
        'organization_location_id',
        'organization_department_id',
        'organization_designation_id',
        'organization_employment_type_id',
        'organization_employment_status_id',
        'organization_work_shift_id',
        'organization_work_shift_type_id',
        'organization_business_registration_type_id',
        'organization_business_ownership_type_id',
        'organization_leave_type_id',
        'entitled_days',
        'entitlement_period',
        'carry_forward_days',
        'max_accumulated_days',
        'encashment_allowed',
        'requires_approval',
        'priority_level',
        'is_active',
        'created_by',
        'updated_at',
        'created_at'
    ];



    protected $casts = [
        'entitled_days' => 'integer',
        'carry_forward_days' => 'integer',
        'max_accumulated_days' => 'integer',
        'encashment_allowed' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'priority_level' => 'integer',
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function businessownershiptype()
    {
        return $this->belongsTo(OrganizationBusinessOwnnershipType::class, 'organization_business_ownership_type_id', 'organization_business_ownership_type_id');
    }

    public function businessregtype()
    {
        return $this->belongsTo(OrganizationBusinessRegsitrationType::class, 'organization_business_registration_type_id', 'organization_business_registration_type_id');
    }

    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(OrganizationDesignation::class, 'organization_designation_id', 'organization_designation_id');
    }

    public function empstatus()
    {
        return $this->belongsTo(OrganizationEmployementStatus::class, 'organization_employment_status_id', 'organization_employment_status_id');
    }


    public function emptype()
    {
        return $this->belongsTo(OrganizationEmployementType::class, 'organization_employment_type_id', 'organization_employment_type_id');
    }

      public function leavetype()
    {
        return $this->belongsTo(OrganizationLeaveType::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }

    
      public function location()
    {
        return $this->belongsTo(OrganizationLocation::class, 'organization_location_id', 'organization_location_id');
    }

          public function workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }

    
          public function workshifttype()
    {
        return $this->belongsTo(OrganizationWorkShiftType::class, 'organization_work_shift_type_id', 'organization_work_shift_type_id');
    }
}
