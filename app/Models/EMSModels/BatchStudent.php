<?php

namespace App\Models\EMSModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class BatchStudent extends Model
{
    protected $table = 'organization_ems_batch_students';
    protected $primaryKey = 'organization_ems_batch_student_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_batch_id',
        'organization_ems_admission_id',
        'organization_ems_student_id',
        'enrollment_date',
        'completion_date',
        'batch_status',
        'remarks'
    ];

    public $timestamps = true;

    public function admission()
    {
        return $this->hasOne(Admission::class , 'organization_ems_admission_id');
    }

    public function batch()
    {
        return $this->hasOne(Batch::class , 'organization_ems_batch_id');
    }
    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }


}
