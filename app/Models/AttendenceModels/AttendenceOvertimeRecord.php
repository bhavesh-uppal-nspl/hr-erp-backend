<?php

namespace App\Models\AttendenceModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceOvertimeRecord extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_overtime_records';

    protected $primaryKey = 'employee_attendance_overtime_record_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'employee_attendance_record_id',
        'organization_id',
        'organization_entity_id',
        'overtime_date',
        'start_time',
        'end_time',
        'overtime_minutes',
        'deviation_reason_id',
        'compensation_type',
        'is_approved',
        'approved_by_employee_id',
        'approved_at',
        'remarks'
    ];

}
