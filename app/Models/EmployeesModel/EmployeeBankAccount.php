<?php

namespace App\Models\EmployeesModel;
use App\Models\GeneralModel\GeneralCities;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmpAddType;
use App\Models\OrganizationModel\OrganizationEmpResiOwnerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBankAccount extends Model
{
    use HasFactory;

    protected $table = 'employee_bank_accounts';

    protected $primaryKey = 'employee_bank_account_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'account_holder_name',
        'bank_name',
        'ifsc_code',
        'is_primary',
        'upi_id',
        'account_number',
        'account_type',
        'remarks',
        'qr_code_url'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function addressType()
    {
        return $this->belongsTo(OrganizationEmpAddType::class, 'organization_employee_address_type_id', 'organization_employee_address_type_id');
    }

    public function ownershipType()
    {
        return $this->belongsTo(OrganizationEmpResiOwnerType::class, 'organization_employee_residential_ownership_type_id', 'organization_employee_residential_ownership_type_id');
    }

    public function city()
    {
        return $this->belongsTo(GeneralCities::class, 'general_city_id', 'general_city_id');
    }
}
