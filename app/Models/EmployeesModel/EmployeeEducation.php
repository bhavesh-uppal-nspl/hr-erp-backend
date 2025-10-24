<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    use HasFactory;
    protected $table = 'employee_educations';

    protected $primaryKey = 'employee_education_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'organization_education_level_id',
        'organization_education_degree_id',
        'organization_education_stream_id',
        'organization_education_level_degree_stream_id',
        'institute_name',
        'marks_percentage',
        'board_university_name',
        'year_of_passing',
        'is_pursuing',
        'is_active',
    ];

   

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function degree()
    {
        return $this->hasMany(OrganizationEducationDegree::class, 'organization_education_degree_id', 'organization_education_degree_id');
    }
}
