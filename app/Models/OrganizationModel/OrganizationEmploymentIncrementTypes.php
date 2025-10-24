<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmploymentIncrementTypes extends Model
{
    use HasFactory;

    protected $table = 'organization_employee_increment_types';

      protected $primaryKey = 'organization_employee_increment_type_id';

  
    public $timestamps = true;


       protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'organization_entity_id',
        'employee_increment_type_name',
        'description',
        'is_active',
        'updated_at',
        'created_at'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

  
}
