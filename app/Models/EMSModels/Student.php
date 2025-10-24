<?php

namespace App\Models\EMSModels;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'organization_ems_students';
    protected $primaryKey = 'organization_ems_student_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'student_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'profile_image_url',
        'email',
        'phone',
        'alternate_phone',
        'address',

        'country_id',
        'state_id',
        'city_id',

        'certificate_name',
        'student_status',
        'remarks'
    ];

    public $timestamps = true;

    public function getProfileImageUrlAttribute($value)
    {
        return $value ? asset('storage/app/public/students' . $value) : null;
    }

    public function country()
    {
        return $this->hasOne(GeneralCountry::class, 'general_country_id', 'country_id');
    }

    public function state()
    {
        return $this->hasOne(GeneralState::class, 'general_state_id', 'state_id');
    }

    public function city()
    {
        return $this->hasOne(GeneralCities::class, 'general_city_id', 'city_id');
    }
}
