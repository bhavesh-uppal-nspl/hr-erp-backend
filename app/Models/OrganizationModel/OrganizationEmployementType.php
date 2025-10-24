<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmployementType extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_employment_types';

    // Define the primary key
    protected $primaryKey = 'organization_employment_type_id';

    // Enable timestamps (created_at, updated_at)
    public $timestamps = true;

    // Define the fillable fields to protect against mass-assignment
    protected $fillable = [
        'organization_id',
        'employment_type_name',
        'organization_configuration_template_id',
        'organization_entity_id',
        'created_by'
    ];

    // Define relationship with the Organization model (if exists)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


   

}
