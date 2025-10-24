<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContact extends Model
{
    use HasFactory;

    protected $table = 'employee_contacts';

    protected $primaryKey = 'employee_contact_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'personal_phone_number',
        'alternate_personal_phone_number',
        'personal_email',
        'alternate_personal_email',
        'preferred_contact_method',
        'emergency_person_phone_number_1',
        'emergency_person_name_1',
        'emergency_person_relation_1',
        'emergency_person_phone_number_2',
        'emergency_person_name_2',
        'emergency_person_relation_2',
        'work_phone_number',
        'work_email',
    ];

    protected $casts = [
        'preferred_contact_method' => 'string',
        'emergency_person_relation_1' => 'string',
        'emergency_person_relation_2' => 'string',
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
