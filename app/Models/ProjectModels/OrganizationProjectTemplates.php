<?php
namespace App\Models\ProjectModels;

use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Database\Eloquent\Model;

class OrganizationProjectTemplates extends Model
{
    protected $table = 'organization_project_templates';
    protected $primaryKey = 'organization_project_template_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'template_name',
        'template_description',
        'organization_project_category_id',
        'organization_project_subcategory_id',
        'created_by'
    ];

    public function category()
    {
        return $this->belongsTo(OrganizationProjectCategory::class, 'organization_project_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(OrganizationProjectSubCategory::class, 'organization_project_subcategory_id');
    }

}
