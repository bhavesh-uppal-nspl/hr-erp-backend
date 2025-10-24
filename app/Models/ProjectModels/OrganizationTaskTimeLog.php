<?php

namespace App\Models\ProjectModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class OrganizationTaskTimeLog extends Model
{
    protected $table = 'organization_project_task_time_logs';
    protected $primaryKey = 'organization_project_task_time_log_id';

    protected $fillable = [
        'organization_project_task_id',
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_project_task_type_id',
        'log_date',
        'start_time',
        'end_time',
        'total_minutes',
        'remarks'
    ];

    protected $casts = [
        'log_date' => 'date',
        'start_time' => 'date',
        'end_time' => 'date',
        'hours_logged' => 'float',
    ];

    public function task()
    {
        return $this->belongsTo(OrganizationProjectTask::class, 'organization_project_task_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}
