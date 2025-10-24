<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeaveType extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_types';

    // Define the primary key
    protected $primaryKey = 'organization_leave_type_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'leave_type_name',
        'description',
        'max_days_allowed',
        'organization_entity_id',
        'organization_configuration_template_id',
        'carry_forward',
        'requires_approval',
        'leave_compensation_type',
        'compensation_code',
        'is_active',
        'created_by',
        'leave_type_code'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function leavereason()
    {
        return $this->hasMany(OrganizationLeaveReason::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }

     public function leaveType()
    {
        return $this->hasMany(OrganizationLeaveReason::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }

      public function leavereasonType()
    {
        return $this->belongsTo(OrganizationLeaveReasonType::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }
}
