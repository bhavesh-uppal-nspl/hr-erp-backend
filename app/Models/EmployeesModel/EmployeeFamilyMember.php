<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    use HasFactory;
    protected $table = 'employee_family_members';

    protected $primaryKey = 'employee_family_member_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'name',
        'relationship',
        'marital_status',
        'current_status',
        'education_details',
        'occupation_details',
        'description',
        'email',
        'date_of_birth',
        'phone',
        'is_dependent',
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
}
