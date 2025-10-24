<?php

namespace App\Models\ProjectModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTask extends Model
{
    protected $table = 'organization_project_tasks';
    protected $primaryKey = 'organization_project_task_id';

    protected $fillable = [
        'organization_project_id',
        'organization_id',
        'organization_entity_id',
        'organization_project_task_template_id',
         'organization_project_task_category_id',
        'organization_project_task_subcategory_id',
        'parent_task_id',
        'generated_from_recurrence_id',
        'task_title',
        'task_short_name',
        'task_level',
        'description',
        'task_remarks',
        'complexity_level',
        'priority',
        'assigned_employee_id',
        'assignment_type',
        'assigned_date',
        'assigned_time',
        'scheduled_date',
        'scheduled_time',
        'started_date',
        'started_time',
        'ended_date',
        'ended_time',
        'approved_date',
        'approved_time',
        'status',
        'status_remarks',
        'quantity',
        'estimated_minutes',
        'total_estimated_minutes',
        'actual_minutes_taken',
        'is_backlog',
        'task_order',
        'organization_project_task_type_id',
        'organization_project_milestone_id'
    ];

    protected $casts = [
       'is_backlog' => 'boolean'
    ];

    public function project()
    {
        return $this->belongsTo(OrganizationProject::class, 'organization_project_id');
    }

    public function assignedEmployee()
{
    return $this->belongsTo(Employees::class, 'assigned_employee_id');
}

    public function parentTask()
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    public function subTasks()
    {
        return $this->hasMany(self::class, 'parent_task_id');
    }

    public function template()
    {
        return $this->belongsTo(OrganizationProjectTaskTemplate::class, 'organization_project_task_template_id');
    }

     public function category()
    {
        return $this->belongsTo(OrganizationProjectTaskCategory::class, 'organization_project_task_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(OrganizationProjectTaskSubCategory::class, 'organization_project_task_subcategory_id');
    }
}
