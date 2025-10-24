<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationProfileSection extends Model
{
    use HasFactory;
    protected $table = 'organization_employee_profile_sections';
    protected $primaryKey = 'organization_employee_profile_section_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'organization_entity_id',
        'employee_profile_section_name',
        'is_applicable',
        'created_by'
    ];
    
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


  
}
