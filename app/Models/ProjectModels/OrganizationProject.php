<?php

namespace App\Models\ProjectModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationProject extends Model
{
    protected $table = 'organization_project_projects';
    protected $primaryKey = 'organization_project_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_client_id',
        'organization_project_category_id',
        'organization_project_subcategory_id',
        'organization_project_type_id',
        'project_name',
        'project_short_name',
        'project_code',
        'project_lead_employee_id',

        'is_billable',
        'billing_model',
        'billing_frequency',
        'total_project_hours',

        'priority',
        'project_status',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'estimated_hours',
        'estimated_hours_period',
        'actual_hours_logged',
        'description',
    ];

    protected $casts = [
        'total_project_hours' => 'float',
        'estimated_hours' => 'float',
        'actual_hours_logged' => 'float',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'is_billable' => 'boolean'
    ];

    public $timestamps = true;

    public function latestTask()
    {
        return $this->hasOne(OrganizationProjectTask::class, 'organization_project_id')
            ->latest('updated_at');
    }
    public function tasks()
    {
        return $this->hasMany(OrganizationProjectTask::class, 'organization_project_id');
    }


    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(OrganizationClient::class, 'organization_client_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employees::class, 'project_lead_employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OrganizationProjectCategory::class, 'organization_project_category_id');
    }
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(OrganizationProjectSubCategory::class, 'organization_project_subcategory_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(OrganizationProjectType::class, 'organization_project_type_id');
    }

}
