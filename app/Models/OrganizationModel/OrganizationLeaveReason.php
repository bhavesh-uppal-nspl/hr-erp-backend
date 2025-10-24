<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeaveReason extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_reasons';

    // Define the primary key
    protected $primaryKey = 'organization_leave_reason_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_leave_reason_type_id',
        'leave_reason_name',
        'description',
        'organization_entity_id',
        'organization_configuration_template_id',
        'created_by'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function leavereasontype()
    {
        return $this->hasMany(OrganizationLeaveReasonType::class, 'organization_leave_reason_type_id', 'organization_leave_reason_type_id');
    }
   
   
}
