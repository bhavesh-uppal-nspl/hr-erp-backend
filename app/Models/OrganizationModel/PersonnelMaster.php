<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelMaster extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'personnel_master';

    // Define the primary key (optional if it follows Laravel's default)
    protected $primaryKey = 'id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'organization_id',
        'personnel_type_id',
        'department_id',
        'reporting_manager_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'disability_status',
        'profile_image_url',
        'designation_id',
        'date_of_joining',
        'employment_status',
        'work_mode',
        'is_active',
    ];

    // Define the relationship with the OrganizationMaster model
    public function organization()
    {
        return $this->belongsTo(OrganizationMaster::class, 'organization_id', 'organization_id');
    }

    // Define the relationship with the PersonnelType model
    public function personnelType()
    {
        return $this->belongsTo(PersonnelType::class, 'personnel_type_id', 'personnel_type_id');
    }

    // Define the relationship with the OrganizationDepartment model
    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'department_id', 'department_id');
    }

    // Define the relationship with the Reporting Manager (same personnel_master table)
    public function reportingManager()
    {
        return $this->belongsTo(PersonnelMaster::class, 'reporting_manager_id', 'id');
    }

    // Optional: Add any other methods or custom logic as needed
}
