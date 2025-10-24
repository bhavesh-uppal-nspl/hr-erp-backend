<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationUnitTypes extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_unit_types';

    // Define the primary key
    protected $primaryKey = 'organization_unit_type_id';

    // Enable timestamps
    public $timestamps = true;

    // Define the fillable fields for mass-assignment
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'unit_type_name',
        'created_by'

    ];

    // Relationship to the organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function Units()
    {
        return $this->belongsTo(OrganizationUnits::class, 'organization_unit_type_id', 'organization_unit_type_id');
    }



}
