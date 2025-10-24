<?php

namespace App\Models\EMSModels;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'organization_ems_companies';
    protected $primaryKey = 'organization_ems_company_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'company_name',
        'industry',
        'contact_person',
        'email',
        'phone',
        'address',
        'status',
    ];

    public $timestamps = true;


}
