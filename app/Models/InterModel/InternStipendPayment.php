<?php

namespace App\Models\InterModel;

use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use App\Models\OrganizationModel\OrganizationUnits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternStipendPayment extends Model
{
    use HasFactory;

    protected $table = 'intern_stipend_payments';

    protected $primaryKey = 'intern_stipend_payment_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_stipend_id ',
        'intern_id',
        'payment_date',
        'payment_cycle',
        'payment_amount',
        'currency_code',
        'payment_method',
        'transaction_reference',
        'payment_status',
        'remarks',
      
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'organization_unit_id', 'organization_unit_id');
    }

      public function DepartmentLocation()
    {
        return $this->belongsTo(OrganizationDepartmentLocation::class, 'organization_department_location_id', 'organization_department_location_id');
    }

      public function IntershipType()
    {
        return $this->belongsTo(IntershipTypes::class, 'organization_internship_type_id', 'organization_internship_type_id');
    }


       public function Status()
    {
        return $this->belongsTo(IntershipStatus::class, 'organization_internship_status_id', 'organization_internship_status_id');
    }

       public function Mentor()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'mentor_employee_id');
    }

   
}
