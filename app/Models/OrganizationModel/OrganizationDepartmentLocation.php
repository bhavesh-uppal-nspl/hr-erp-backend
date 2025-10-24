<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationDepartmentLocation extends Model
{
    use HasFactory;
    protected $table = 'organization_department_locations';

    protected $primaryKey = 'organization_department_location_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_location_id',
        'organization_department_id',
        'organization_entity_id'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function location()
    {
        return $this->hasOne(OrganizationLocation::class, 'organization_location_id', 'organization_location_id');
    }

    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id');
    }

    public function departmentLocations()
    {
        return $this->hasMany(OrganizationDepartmentLocation::class, 'organization_location_id', 'organization_location_id');
    }


       public function employees()
    {
        return $this->belongsTo(Employees::class, 'organization_location_department_id', 'organization_location_department_id');
    }




}
