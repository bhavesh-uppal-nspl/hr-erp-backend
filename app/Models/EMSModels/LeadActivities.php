<?php

namespace App\Models\EMSModels;
use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Model;

class LeadActivities extends Model
{
    protected $table = 'organization_ems_lead_activities';
    protected $primaryKey = 'organization_ems_lead_activity_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_lead_id',
        'employee_id',
        'activity_type',
        'activity_datetime',
        'activity_summary',
        'remarks',
        'was_within_preferred_time',
        'call_status',
        'email_read_flag',
        'email_response_flag',
        'whatsapp_read_flag',
        'whatsapp_response_flag',
    ];

    public $timestamps = true;

    public function lead()
    {
        return $this->hasOne(Lead::class , 'organization_ems_lead_id');
    }
    public function employee()
    {
        return $this->hasOne(Employees::class , 'employee_id');
    }

}
