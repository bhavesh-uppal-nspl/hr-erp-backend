<?php

namespace App\Models\ProjectModels;

use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTemplateTasks extends Model
{
    protected $table = 'organization_project_template_tasks';
    protected $primaryKey = 'organization_project_template_task_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_project_template_id',
        'organization_project_template_milestone_id',
        'organization_project_task_template_id',

        'task_order',
      
        'remarks'
    ];


    public function projectTemplate()
    {
        return $this->belongsTo(OrganizationProjectTemplates::class, 'organization_project_template_id');
    }
    public function projectTemplateMileStone()
    {
        return $this->belongsTo(OrganizationProjectMilestoneTemplate::class, 'organization_project_template_milestone_id');
    }
    public function projectTaskTemplate()
    {
        return $this->belongsTo(OrganizationProjectTaskTemplate::class, 'organization_project_task_template_id');
    }
}
