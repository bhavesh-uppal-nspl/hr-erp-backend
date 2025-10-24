<?php

namespace App\Models\AttendenceModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceBreakTypes extends Model
{
    use HasFactory;

    protected $table = 'organization_attendance_break_types';

    protected $primaryKey = 'organization_attendance_break_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'attendance_break_type_name',
        'description',
        'is_active'


    ];

}
