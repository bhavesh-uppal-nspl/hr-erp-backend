<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_components';
    protected $primaryKey = 'organization_payroll_component_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_payroll_component_type_id',
        'organization_entity_id',
        'organization_id',
        'payroll_component_name',
        'calculation_method',
        'fixed_amount',
        'percentage_of_component',
        'formula_json',
        'taxable',
        'affects_net_pay',
        'rounding_rule',
        'rounding_precision',
        'sort_order',
        'is_active',
        'effective_from',
        'effective_to'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function payrollComponentType()
    {
        return $this->belongsTo(PayrollComponentTypes::class, 'organization_payroll_component_type_id', 'organization_payroll_component_type_id');
    }


}
