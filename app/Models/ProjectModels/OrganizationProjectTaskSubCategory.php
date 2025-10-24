<?php

namespace App\Models\ProjectModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTaskSubCategory extends Model
{
    protected $table = 'organization_project_task_subcategories';
    protected $primaryKey = 'organization_project_task_subcategory_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_project_task_category_id',
        'task_subcategory_name',
        'task_subcategory_short_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(OrganizationProjectTaskCategory::class, 'organization_project_task_category_id');
    }
}
