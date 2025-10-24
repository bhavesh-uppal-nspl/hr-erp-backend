<?php


namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationShiftRotationPattern;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkshiftRotationAssignment extends Model
{
    use HasFactory;

    protected $table = 'employee_work_shift_rotation_assignments';

    protected $primaryKey = 'employee_work_shift_rotation_assignment_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_work_shift_rotation_pattern_id',
        'effective_start_date',
        'effective_end_date',
        'anchor_day_number',
        'is_active ',
        'remarks',
       
    ];

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
      public function RotationPattern()
    {
        return $this->belongsTo(OrganizationShiftRotationPattern::class, 'organization_work_shift_rotation_pattern_id', 'organization_work_shift_rotation_pattern_id');
    }



}
