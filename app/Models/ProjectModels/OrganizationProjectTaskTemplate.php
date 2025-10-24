<?php

namespace App\Models\ProjectModels;
use App\Models\OrganizationModel\OrganizationDesignation;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTaskTemplate extends Model
{
    protected $table = 'organization_project_task_templates';
    protected $primaryKey = 'organization_project_task_template_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_project_task_category_id',
        'organization_project_task_subcategory_id',
        'task_title',
        'description',
        'complexity_level',
        'task_instructions',
        'applicable_organization_designation_id',
        'estimated_minutes',
        'is_task_time_quantity_based',
        'quantity_unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_task_time_quantity_based' => 'boolean',
    ];

    public function tasks()
    {
        return $this->hasMany(OrganizationProjectTask::class, 'organization_project_task_template_id');
    }

    public function designation()
    {
        return $this->belongsTo(OrganizationDesignation::class, 'applicable_organization_designation_id');
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
