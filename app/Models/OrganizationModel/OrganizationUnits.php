<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationUnits extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_units';

    // Define the primary key
    protected $primaryKey = 'organization_unit_id';

    // Enable timestamps
    public $timestamps = true;

    // Define the fillable fields for mass-assignment
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_unit_type_id',
        'unit_name',
        'unit_short_name',
        'parent_unit_id'
    ];

    // Relationship to the organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function UnitTypes()
    {
        return $this->hasMany(OrganizationUnitTypes::class, 'organization_unit_type_id', 'organization_unit_type_id');
    }

}
