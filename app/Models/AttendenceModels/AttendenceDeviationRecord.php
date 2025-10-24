<?php

namespace App\Models\AttendenceModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceDeviationRecord extends Model
{
    use HasFactory;
    protected $table = 'employee_attendance_deviation_records';
    protected $primaryKey = 'employee_attendance_deviation_record_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_id',
        'employee_attendance_timelog_id',
        'employee_id',
        'organization_entity_id',
        'attendance_date',
        'deviation_reason_type_id',
        'deviation_reason_id',
        'expected_time',
        'actual_time',
        'expected_time',
        'deviation_minutes',
        'reference_point',
        'remarks'
    ];

}
