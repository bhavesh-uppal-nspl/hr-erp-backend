<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationDepartment extends Model
{
    protected $table = 'organization_departments';

    // Primary key
    protected $primaryKey = 'organization_department_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
        'department_name',
        'department_short_name',
        'description',
        'organization_entity_id',
        'department_mail_id',
        'department_employees_count',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function departmentlocation()
    {
        return $this->hasMany(OrganizationDepartmentLocation::class, 'organization_department_id', 'organization_department_id');
    }

    public function designations()
    {
        return $this->hasMany(OrganizationDesignation::class, 'organization_department_id', 'organization_department_id');
    }

    public function personalData()
    {
        return $this->hasMany(PersonnelMaster::class, 'department_id', 'department_id');
    }



    public function employees()
{
    return $this->hasManyThrough(
        Employees::class,
        OrganizationDepartmentLocation::class,
        'organization_department_id',           // FK on department_locations
        'organization_department_location_id',  // FK on employees
        'organization_department_id',           // PK on department
        'organization_department_location_id'   // PK on department_locations
    );
}




}