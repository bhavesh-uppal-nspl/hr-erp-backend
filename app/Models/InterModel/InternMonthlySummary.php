<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternMonthlySummary extends Model
{
    use HasFactory;

    protected $table = 'intern_attendance_monthly_summaries';

    protected $primaryKey = 'intern_attendance_monthly_summary_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'organization_language_id',
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
        'approved_leave_days',
        'unapproved_leave_days',
        'late_entries',
        'early_exits',
        'break_time_exceed_entries',
        'total_overtime_minutes',
        'expected_shift_minutes',
        'actual_work_minutes'
 

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   

}
