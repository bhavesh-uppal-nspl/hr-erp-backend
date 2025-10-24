<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeeAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEntityTypes extends Model
{
    use HasFactory;

    protected $table = 'organization_entity_types';

    // Define the primary key
    protected $primaryKey = 'organization_entity_type_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'entity_type_name',
        'description',
        'created_by',
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


  
}
