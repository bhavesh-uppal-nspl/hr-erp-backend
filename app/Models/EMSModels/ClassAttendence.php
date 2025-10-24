<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class ClassAttendence extends Model
{
    protected $table = 'organization_ems_class_attendance';
    protected $primaryKey = 'organization_ems_class_attendance_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_batch_class_id',
        'organization_ems_student_id',
        'attendance_status',
        'remarks'
    ];

    public $timestamps = true;

    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }

    public function batchClass()
    {
        return $this->hasOne(BatchClass::class , 'organization_ems_batch_class_id');
    }

}
