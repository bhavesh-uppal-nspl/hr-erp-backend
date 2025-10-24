<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmploymentIncrementTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncrements extends Model
{
    use HasFactory;

    protected $table = 'employee_increments';

    protected $primaryKey = 'employee_increment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_employee_increment_type_id',
        'increment_date',
        'effective_date',
        'remarks',
        'new_ctc_amount',
        'increment_amount', 
        'increment_percentage',
        'previous_ctc_amount'
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
   

    public function IncrementType()
    {
        return $this->belongsTo(OrganizationEmploymentIncrementTypes::class, 'organization_employee_increment_type_id', 'organization_employee_increment_type_id');
    }

   
}
