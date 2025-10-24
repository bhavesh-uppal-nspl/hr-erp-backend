<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class GeneralSettingType extends Model
{
    use HasFactory;
    protected $table = 'general_setting_types';

    protected $primaryKey = 'general_setting_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'setting_type_name',
    ];

}
