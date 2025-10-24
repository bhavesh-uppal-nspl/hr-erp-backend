<?php

namespace App\Models\EMSModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class BatchClass extends Model
{
    protected $table = 'organization_ems_batch_classes';
    protected $primaryKey = 'organization_ems_batch_class_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_batch_id',
        'trainer_employee_id',
        'class_date',
        'start_time',
        'end_time',
        'topic',
        'class_status',
        'remarks'
    ];

    public $timestamps = true;

    public function trainer()
    {
        return $this->hasOne(Employees::class , 'employee_id','trainer_employee_id');
    }

    public function batch()
    {
        return $this->hasOne(Batch::class , 'organization_ems_batch_id');
    }


}
