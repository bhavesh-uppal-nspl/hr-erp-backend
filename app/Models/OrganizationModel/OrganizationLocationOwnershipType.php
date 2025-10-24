<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLocationOwnershipType extends Model
{
    use HasFactory;

    // Optional: Set the custom table name
    protected $table = 'organization_location_ownership_types';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'organization_location_ownership_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'location_ownership_type_name',
        'description'

    ];

    // Define the relationship with OrganizationMaster
    public function organization()
    {
        return $this->hasMany(Organization::class, 'organization_id', 'organization_id');
    }


    public function Locations()
    {
        return $this->belongsTo(OrganizationLocation::class, 'organization_location_ownership_type_id', 'organization_location_ownership_type_id');
    }
    
}
