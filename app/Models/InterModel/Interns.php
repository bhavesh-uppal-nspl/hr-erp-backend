<?php
namespace App\Models\InterModel;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use App\Models\OrganizationModel\OrganizationUnits;
use App\Models\OrganizationModel\OrganizationWorkShift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interns extends Model
{
    use HasFactory;

    protected $table = 'interns';

    protected $primaryKey = 'intern_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'profile_image_url',
        'organization_unit_id',
        'organization_department_location_id',
        'organization_internship_type_id',
        'organization_internship_stage_id',
        'organization_internship_status_id',
        'internship_start_date',
        'internship_end_date',
        'stipend_amount',
        'mentor_employee_id',
        'organization_user_id',
        'organization_work_shift_id',
        'is_paid'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


        public function getProfileImageUrlAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //   for online       

    //    public function getProfileImageUrlAttribute($value)
    // {
    //     return $value ? asset('storage/app/public/employees/' . $value) : null;
    // }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'organization_unit_id', 'organization_unit_id');
    }

     public function address()
    {
        return $this->belongsTo(InternAddress::class, 'intern_id', 'intern_id');
    }


     public function departmentLocation()
    {
        return $this->hasMany(OrganizationDepartmentLocation::class, 'organization_department_location_id', 'organization_department_location_id');
    }

      public function IntershipType()
    {
        return $this->belongsTo(IntershipTypes::class, 'organization_internship_type_id', 'organization_internship_type_id');
    }


       public function Status()
    {
        return $this->belongsTo(IntershipStatus::class, 'organization_internship_status_id', 'organization_internship_status_id');
    }

      public function workShift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }

       public function Mentor()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'mentor_employee_id');
    }

       public function Education()
    {
        return $this->belongsTo(InternEducation::class, 'intern_id', 'intern_id');
    }

       public function contact()
    {
        return $this->belongsTo(InternContact::class, 'intern_id', 'intern_id');
    }

   
}
