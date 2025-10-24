<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationDesignation extends Model
{
    use HasFactory;

    // Table name (optional if it follows convention)
    protected $table = 'organization_designations';

    // Primary key
    protected $primaryKey = 'organization_designation_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       'organization_id',
        'designation_name',
        'designation_short_name',
        'organization_department_id',
        'organization_entity_id'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
    public function department()
    {
        return $this->belongsTo(OrganizationDepartment::class, 'organization_department_id', 'organization_department_id'); // Adjust if model/table name differs
    }


}
