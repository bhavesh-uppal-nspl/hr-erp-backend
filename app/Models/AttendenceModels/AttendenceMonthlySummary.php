<?php

namespace App\Models\AttendenceModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceMonthlySummary extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_monthly_summaries';

    protected $primaryKey = 'employee_attendance_monthly_summary_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'year',
        'month',
        'total_days_in_month',
        'workdays_in_month',
        'off_days_in_month',
        'holidays_in_month',
        'weekoff_in_month',
        'working_days',
        'absent_days',
        'leave_days',
        'casual_leaves',
        'medical_leaves',
        'earned_leaves',
        'compensatory_off_leaves',
        'approved_leave_days',
        'unapproved_leave_days',
        'compensatory_off_earned',
        'late_entries',
        'break_time_exceed_entries',
        'early_exits',
        'total_overtime_minutes',
        'expected_shift_minutes',
        'actual_work_minutes'

    ];

}
