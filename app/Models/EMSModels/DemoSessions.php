<?php

namespace App\Models\EMSModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class DemoSessions extends Model
{
    protected $table = 'organization_ems_demo_sessions';
    protected $primaryKey = 'organization_ems_demo_session_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'trainer_employee_id',
        'organization_ems_training_program_id',
        'demo_date',
        'start_time_ist',
        'end_time_ist',
        'start_time_client',
        'end_time_client',
        'client_timezone',
        'demo_notes',
        'demo_mode',
        'trainer_location',
        'meeting_link',
        'student_remarks',
        'trainer_remarks',
        'counsellor_remarks',
        'demo_duration_minutes',
        'status',
    ];

    public $timestamps = true;

    public function trainer()
    {
        return $this->hasOne(Employees::class, 'trainer_employee_id');
    }
    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class);
    }

}
