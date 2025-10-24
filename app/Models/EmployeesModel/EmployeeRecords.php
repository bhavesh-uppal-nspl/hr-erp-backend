<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartment;
use App\Models\OrganizationModel\OrganizationDesignation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRecords extends Model
{
    use HasFactory;

    protected $table = 'employee_employment_records';
    protected $primaryKey = 'employee_employment_record_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_designation_id',
        'organization_department_id',
        'employee_increment_id',
        'start_date',
        'end_date',
        'change_reason',
        'remarks', 
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

   
   
    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(OrganizationDesignation::class, 'organization_designation_id', 'organization_designation_id');
    }
    public function EmployeeIncrement()
    {
        return $this->belongsTo(EmployeeIncrements::class, 'employee_increment_id', 'employee_increment_id');
    }
}
