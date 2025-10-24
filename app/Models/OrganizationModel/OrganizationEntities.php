<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEntities extends Model
{
    use HasFactory;

    protected $table = 'organization_entities';

    // Define the primary key
    protected $primaryKey = 'organization_entity_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'organization_entity_type_id',
        'organization_location_id',
        'entity_name',
        'general_entity_type_id',
        'entity_short_name'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


  
}
