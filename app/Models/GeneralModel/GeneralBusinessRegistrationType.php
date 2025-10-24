<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class GeneralBusinessRegistrationType extends Model
{
    use HasFactory;
    protected $table = 'general_business_registration_types';

    protected $primaryKey = 'general_business_registration_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'business_registration_type_name',
        'business_registration_type_short_name',
    ];

}
