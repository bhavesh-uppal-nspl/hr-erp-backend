<?php

namespace App\Models\InterModel;

use App\Models\GeneralModel\GeneralCities;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternAddress extends Model
{
    use HasFactory;

    protected $table = 'intern_addresses';

    protected $primaryKey = 'intern_address_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'address_line1',
        'address_line2',
        'address_line3',
        'general_city_id',
        'postal_code'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function city()
    {
        return $this->belongsTo(GeneralCities::class, 'general_city_id', 'general_city_id');
    }

   
}
