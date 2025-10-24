<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeaveReasonType extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_reason_types';

    protected $primaryKey = 'organization_leave_reason_type_id';

    public $timestamps = true;

    protected $fillable = [
        'organization_id',
        'organization_leave_type_id',
        'leave_reason_type_name',
        'description',
        'organization_entity_id',
        'created_by',
        'organization_configuration_template_id'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function leavetype()
    {
        return $this->hasMany(OrganizationLeaveType::class, 'organization_leave_type_id', 'organization_leave_type_id');
    }

        public function leavereason()
    {
        return $this->belongsTo(OrganizationLeaveReason::class, 'organization_leave_reason_type_id', 'organization_leave_reason_type_id');
    }
   
}
