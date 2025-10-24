<?php

namespace App\Models\InterModel;

use App\Models\AttendenceModels\AttendenceStatusType;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternAttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'intern_attendance_records';

    protected $primaryKey = 'intern_attendance_record_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'attendance_date',
        'attendance_status_type_id',
        'clock_in_time',
        'clock_out_time',
        'workshift_total_work_minutes',
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
    public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }

        public function statusType()
    {
        return $this->belongsTo(AttendenceStatusType::class, 'attendance_status_type_id', 'organization_attendance_status_type_id');
    }

   
}
