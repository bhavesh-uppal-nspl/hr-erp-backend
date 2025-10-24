<?php
namespace App\Models\FunctionalModels;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationFunctionalRoles extends Model
{
    use HasFactory;
    protected $table = 'organization_functional_roles';
    protected $primaryKey = 'organization_functional_role_id';
     public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_department_id',
        'organization_id',
        'functional_role_code',
        'functional_role_name',
        'description',
        'is_active',
    ];

    // Relationships
    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id');
    }

    

    
    

    
   
}
