<?php

namespace App\Models\GeneralModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralCities extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_cities';

    // Primary key
    protected $primaryKey = 'general_city_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
   
    protected $fillable = [
        'general_country_id',
        'general_state_id',
        'city_name',
        'city_latitude',
        'city_longitude',
    ];

    protected $casts = [
        'city_latitude' => 'decimal:6',
        'city_longitude' => 'decimal:6',
    ];

    public function country()
    {
        return $this->belongsTo(GeneralCountry::class, 'general_country_id');
    }

    public function state()
    {
        return $this->belongsTo(GeneralState::class, 'general_state_id');
    }
   

   
   
}
