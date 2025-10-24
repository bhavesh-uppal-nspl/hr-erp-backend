<?php

namespace App\Models\ProjectModels;

use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectMilestone extends Model
{
    protected $table = 'organization_project_milestones';
    protected $primaryKey = 'organization_project_milestone_id';

    protected $fillable = [
        'organization_project_id',
        'organization_id',
        'organization_entity_id',
        'milestone_title',
        'milestone_code',
        'description',
        'start_date',
        'due_date',
        'completed_date',
        'status',
        'is_billable',
        'is_active',
    ];

    
    protected $casts = [
        'is_billable' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function project()
    {
        return $this->belongsTo(OrganizationProject::class, 'organization_project_id');
    }

    public function entity()
    {
        return $this->belongsTo(OrganizationEntities::class, 'organization_entity_id');
    }
}
