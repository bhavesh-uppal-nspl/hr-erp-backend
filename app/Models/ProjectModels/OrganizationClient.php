<?php

namespace App\Models\ProjectModels;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationClient extends Model
{
    protected $table = 'organization_clients';
    protected $primaryKey = 'organization_client_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'client_name',
        'client_short_name',
        'client_code',
        'website',
        'client_since',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'pincode',
        'industry_id',
        'status',
    ];

    protected $casts = [
        'client_since' => 'date',
    ];

    public $timestamps = true;

    // Relationships

    public function contacts(): HasMany
    {
        return $this->hasMany(OrganizationClientContact::class, 'organization_client_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(OrganizationProject::class, 'organization_client_id');
    }

}

