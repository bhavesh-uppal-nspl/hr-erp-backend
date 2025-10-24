<?php
namespace App\Models\FunctionalModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFunctionRole extends Model
{
    use HasFactory;
    protected $table = 'employee_functional_roles';
    protected $primaryKey = 'employee_functional_role_id';
     public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_functional_role_id',
        'organization_functional_role_specialization_id',
        'is_primary',
        'is_active',
        'assigned_on',
        'unassigned_on',
        'organization_id',
        'organization_entity_id'
    ];

    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function functionRole()
    {
        return $this->belongsTo(OrganizationFunctionalRoles::class, 'organization_functional_role_id', 'organization_functional_role_id');
    }


      public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }


        public function RoleSpecialization()
    {
        return $this->belongsTo(FunctionalRoleSpecifization::class, 'organization_functional_role_specialization_id', 'organization_functional_role_specialization_id');
    }

    

    
    

    
   
}
