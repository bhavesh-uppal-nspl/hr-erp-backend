<?php
namespace App\Models\FunctionalModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionalRoleSpecifization extends Model
{
    use HasFactory;
    protected $table = 'organization_functional_role_specializations';
    protected $primaryKey = 'organization_functional_role_specialization_id';
     public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_functional_role_id',
        'functional_role_specialization_name',
        'functional_role_specialization_code',
        'description',
        'is_active',
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

    

    
    

    
   
}
