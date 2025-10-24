<?php

namespace App\Models\ProjectModels;


use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTaskType extends Model
{
    protected $table = 'organization_project_task_types';
    protected $primaryKey = 'organization_project_task_type_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'task_type_name',
        'task_type_short_name',
        'description',
        'is_active',
    ];

    public function entity()
    {
        return $this->belongsTo(OrganizationEntities::class, 'organization_entity_id');
    }
}
