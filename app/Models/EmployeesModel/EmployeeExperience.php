<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExperience extends Model
{
    use HasFactory;
    protected $table = 'employee_experiences';

    protected $primaryKey = 'employee_experience_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_entity_id',
        'organization_id',
        'experience_type',
        'organization_name',
        'general_industry_id',
        'location',
        'work_title',
        'description',
        'work_mode',
        'compensation_status',
        'compensation_payout_model',
        'compensation_amount',
        'currency_code',
        'start_date',
        'end_date',
        'reporting_manager_name',
        'reporting_manager_contact',
        'description',
        'is_verified',
        'verified_by',
        'verification_date',
        'verification_notes'
    ];

    protected $casts=[
        'is_verified'=>'boolean'

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
