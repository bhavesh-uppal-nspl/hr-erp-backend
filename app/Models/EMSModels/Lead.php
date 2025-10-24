<?php

namespace App\Models\EMSModels;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'organization_ems_leads';
    protected $primaryKey = 'organization_ems_lead_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'lead_datetime',
        'lead_source_id',
        'person_full_name',
        'email',
        'phone',
        'alternate_phone',
        'country_id',
        'state_id',
        'city_id',
        'training_program_id',
        'interested_program_remarks',
        'remarks',
        'lead_stage_id',
        'is_spam',
        'spam_reason'
    ];

    public $timestamps = true;

     public function country()
    {
        return $this->hasOne(GeneralCountry::class , 'general_country_id' , 'country_id');
    }

    public function state()
    {
        return $this->hasOne(GeneralState::class , 'general_state_id' , 'state_id');
    }
    
    public function city()
    {
        return $this->hasOne(GeneralCities::class , 'general_city_id' , 'city_id');
    }

    public function leadSource()
    {
        return $this->hasOne(LeadSource::class ,'organization_ems_lead_source_id', 'lead_source_id');
    }

    public function leadStage()
    {
        return $this->hasOne(LeadStage::class , 'organization_ems_lead_stage_id','lead_stage_id');
    }
    
    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class , 'organization_ems_training_program_id','training_program_id');
    }
}
