<?php

namespace App\Models\ProjectModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTaskCategory extends Model
{
    protected $table = 'organization_project_task_categories';
    protected $primaryKey = 'organization_project_task_category_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'task_category_name',
        'task_category_short_name',
        'description',
    ];


}
