<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountMapping extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_account_mappings';
    protected $primaryKey = 'organization_payroll_account_mapping_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'organization_payroll_component_id',
        'account_code',
        'account_name',
        'posting_type',
        'remarks',
 
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class, 'organization_payroll_component_id', 'organization_payroll_component_id');
    }
    

}
