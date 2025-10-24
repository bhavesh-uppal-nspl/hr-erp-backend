<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationUserType extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_user_types';

    // Define the primary key
    protected $primaryKey = 'organization_user_type_id';

    // Enable timestamps
    public $timestamps = true;

    // Define the fillable fields for mass-assignment
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'user_type_name',
        'user_type_code',
        'description',
        'created_by'
    ];

    // Relationship to the organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   


}
