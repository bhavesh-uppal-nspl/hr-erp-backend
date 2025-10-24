<?php

namespace App\Models\ProjectModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTaskRecurrence extends Model
{
    protected $table = 'organization_project_task_recurrences';
    protected $primaryKey = 'organization_project_task_recurrence_id';

    protected $fillable = [
        'organization_project_task_id',
        'organization_id',
        'recurrence_pattern',
        'recurrence_days',
        'recurrence_interval',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function task()
    {
        return $this->belongsTo(OrganizationProjectTask::class, 'organization_project_task_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
