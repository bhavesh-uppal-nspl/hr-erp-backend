<?php

namespace App\Models\OrganizationModel;

use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralIndustry;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralLocationOwnershipType;

use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLocation extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel's convention)
    protected $table = 'organization_locations';

    // Primary key
    protected $primaryKey = 'organization_location_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
        'location_name',
        'organization_location_ownership_type_id',
        'organization_entity_id',
        'location_latitude',
        'location_longitude',
        'addressline1',
        'addressline2',
        'general_city_id',
        'postal_code',
        'number_of_floors',
        'area_sq_ft',
        'general_state_id',
        'general_country_id'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function city()
    {
        return $this->hasOne(GeneralCities::class, 'general_city_id', 'general_city_id'); // Replace 'City' with actual model if different
    }

     public function state()
    {
        return $this->hasOne(GeneralState::class, 'general_state_id', 'general_state_id'); // Replace 'City' with actual model if different
    }

     public function country()
    {
        return $this->hasOne(GeneralCountry::class, 'general_country_id', 'general_country_id'); // Replace 'City' with actual model if different
    }

      public function locationOwnershiptype()
    {
        return $this->hasMany(OrganizationLocationOwnershipType::class, 'organization_location_ownership_type_id', 'organization_location_ownership_type_id'); // Replace 'City' with actual model if different
    }
}
