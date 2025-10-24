<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    protected $table = 'cities';
    protected $primaryKey = 'id';
    protected $fillable = [
       'cityName',
        'city',
        'lat',
        'lng',
        'population',
        'cat',
        'admin_name',
        'postCode',
        'country',
        'country_code'
    ];

    public $timestamps = true;

    /**
     * Relationships (Optional: Define these based on foreign keys and your table structure)
     */
  
}
