<?php

namespace App\Models\AttendenceModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendenceTimeLog extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_timelogs';

    protected $primaryKey = 'employee_attendance_timelog_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'employee_id',
        'attendance_date',
        'attendance_log_type',
        'attendance_log_time',
        'attendance_break_type_id',
        'attendance_source_type_id',
        'remarks',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function breakType()
    {
        return $this->belongsTo(AttendenceBreakTypes::class, 'organization_attendance_break_type_id ', 'attendance_break_type_id');
    }


     public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function sourceType()
    {
        return $this->belongsTo(AttendenceSource::class, 'attendance_source_type_id', 'organization_attendance_source_id');
    }


}
