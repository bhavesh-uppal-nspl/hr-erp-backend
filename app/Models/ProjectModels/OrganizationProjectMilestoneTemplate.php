<?php

namespace App\Models\ProjectModels;

use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectMilestoneTemplate extends Model
{
    protected $table = 'organization_project_template_milestones';
    protected $primaryKey = 'organization_project_template_milestone_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_project_template_id',
        'milestone_title',
        'milestone_description',
        'milestone_order',
        'expected_completion_days'
    ];

    
    protected $casts = [
        'is_billable' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function projectTemplate()
    {
        return $this->belongsTo(OrganizationProjectTemplates::class, 'organization_project_template_id');
    }
}
