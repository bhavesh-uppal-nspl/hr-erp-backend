<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationConfigrationTemplates extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel's convention)
    protected $table = 'organization_configuration_templates';

    // Primary key
    protected $primaryKey = 'organization_configuration_template_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',   
        'template_name',
        'template_code',
        'organization_entity_id',
        'description',
        'scope',
        'general_country_id',
        'general_state_id',
        'created_by'
    ];


 
}
