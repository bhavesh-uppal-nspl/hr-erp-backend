<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmploymentStages extends Model
{
    use HasFactory;
    protected $table = 'organization_employment_stages';

    // Define the primary key
    protected $primaryKey = 'organization_employment_stage_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'organization_employment_status_id',
        'employment_stage_name',
        'description',
        'created_by'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function Status()
    {
        return $this->belongsTo(OrganizationEmployementStatus::class, 'organization_employment_status_id', 'organization_employment_status_id');
    }


  
}
