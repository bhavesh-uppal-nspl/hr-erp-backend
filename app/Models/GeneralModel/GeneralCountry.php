<?php


namespace App\Models\GeneralModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralCountry extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'general_countries';

    // Primary key
    protected $primaryKey = 'general_country_id';

    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'country_code',
        'country_name',
        'country_phone_code',
        'country_subcode',
        'nationality',
        'capital',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function states()
    {
        return $this->hasMany(GeneralState::class, 'general_country_id', 'general_country_id');
    }


    public function cities()
    {
        return $this->hasMany(GeneralCities::class, 'general_country_id', 'general_country_id');
    }





   
   
}
