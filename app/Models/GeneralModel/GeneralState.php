<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralState extends Model
{
    use HasFactory;
    protected $table = 'general_states';

    protected $primaryKey = 'general_state_id';

    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'general_country_id',
        'state_name',
        'state_code',
    ];


    public function cities()
    {
        return $this->hasMany(GeneralCities::class, 'general_state_id', 'general_state_id');
    }



}
