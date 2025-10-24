<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationWorkShift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkshiftAssignment extends Model
{
    use HasFactory;

    protected $table = 'employee_work_shift_assignments';

    protected $primaryKey = 'employee_work_shift_assignment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_work_shift_id',
        'assignment_date',
        'is_override',
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

      public function Workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }



}
