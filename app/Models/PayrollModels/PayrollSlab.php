<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollSlab extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_component_slabs';

    protected $primaryKey = 'organization_payroll_component_slab_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_payroll_component_id',
        'organization_id',
        'slab_min',
        'slab_max',
        'slab_value_type',
        'organization_entity_id',
        'slab_value',
    ];
    
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'organization_payroll_component_id', 'organization_payroll_component_id');
    }

}
