<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeeAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmpResiOwnerType extends Model
{
    use HasFactory;

    protected $table = 'organization_employee_residential_ownership_types';

    // Define the primary key
    protected $primaryKey = 'organization_employee_residential_ownership_type_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_employee_residential_ownership_type_name',
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


  
}
