<?php

namespace App\Models\AttendenceModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceRecord extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_records';

    protected $primaryKey = 'employee_attendance_record_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'employee_id',
        'attendance_date',
        'attendance_status_type_id',
        'clock_in_time',
        'clock_out_time',
        'workshift_total_work_minutes',
        'actual_total_work_minutes',
        'workshift_total_break_minutes',
        'actual_total_break_minutes',
        'overtime_minutes',
        'has_deviations',
        'number_of_deviations',
        'remarks'
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function statusType()
    {
        return $this->belongsTo(AttendenceStatusType::class, 'attendance_status_type_id', 'organization_attendance_status_type_id');
    }

    public function Employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

}
