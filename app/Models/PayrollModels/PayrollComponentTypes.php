<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollComponentTypes extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_component_types';

    protected $primaryKey = 'organization_payroll_component_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_configuration_template_id',
        'organization_entity_id',
        'organization_id',
        'payroll_component_type_name',
        'description',
        'is_active'
    ];


       public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
