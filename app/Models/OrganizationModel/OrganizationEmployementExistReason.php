<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmployementExistReason extends Model
{
    use HasFactory;

    // Table associated with this model
    protected $table = 'organization_employment_exit_reasons';

    // Primary key
    protected $primaryKey = 'organization_employment_exit_reason_id';

    // Enable timestamps
    public $timestamps = true;

    // Mass-assignable fields
    protected $fillable = [
        'organization_id',
        'employment_exit_reason_name',
        'description',
        'organization_entity_id',
        'organization_configuration_template_id',
        'organization_employment_exit_reason_type_id',
        'created_by'
    ];

    // Relationship with organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function ExitReasonType()
    {
        return $this->belongsTo(OrganizationEmpExitReasonType::class, 'organization_employment_exit_reason_type_id', 'organization_employment_exit_reason_type_id');
    }
}
