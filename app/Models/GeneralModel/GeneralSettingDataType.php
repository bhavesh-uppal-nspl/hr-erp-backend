<?php

namespace App\Models\GeneralModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class GeneralSettingDataType extends Model
{
    use HasFactory;
    protected $table = 'general_setting_data_types';

    protected $primaryKey = 'general_setting_data_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'description',
        'setting_data_type_name',
    ];

}
