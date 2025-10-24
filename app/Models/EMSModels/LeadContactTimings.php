<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class LeadContactTimings extends Model
{
    protected $table = 'organization_ems_lead_contact_timings';
    protected $primaryKey = 'organization_ems_lead_contact_timing_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_lead_id',
        'preferred_contact_mode',
        'preferred_contact_timezone',
        'morning_student_time_start',
        'morning_student_time_end',
        'morning_ist_time_start',
        'morning_ist_time_end',
        'evening_student_time_start',
        'evening_student_time_end',
        'evening_ist_time_start',
        'evening_ist_time_end'
    ];

    public $timestamps = true;

    public function lead()
    {
        return $this->hasOne(Lead::class , 'organization_ems_lead_id');
    }

}
