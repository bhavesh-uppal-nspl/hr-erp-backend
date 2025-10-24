<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeeAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmpAddType extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'organization_employee_address_types';

    // Primary key
    protected $primaryKey = 'organization_employee_address_type_id';

    public $timestamps = true;

    // Mass-assignable fields
    protected $fillable = [
        'organization_id',
        'employee_address_type_name',
        'organization_configuration_template_id',
        'created_by',
        'organization_entity_id'
    ];

    // Relationship to organization (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

 
}
