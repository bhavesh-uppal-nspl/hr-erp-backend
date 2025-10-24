<?php

namespace App\Models\EMSModels;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Model;

class RecruitmentAgency extends Model
{
    protected $table = 'organization_ems_recruitment_agencies';
    protected $primaryKey = 'organization_ems_recruitment_agency_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'agency_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'status',
        'remarks'
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
}
