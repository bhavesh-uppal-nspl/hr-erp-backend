<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationWorkshiftRotationDays extends Model
{
    use HasFactory;
    protected $table = 'organization_work_shift_rotation_days';
    protected $primaryKey = 'organization_work_shift_rotation_day_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [

        'organization_id',
        'organization_entity_id',
        'organization_work_shift_rotation_pattern_id',
        'day_number',
        'organization_work_shift_id',
        'is_off_day',
        'created_at',
        'updated_at'

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }


    public function WorkshiftPattern()
    {
        return $this->belongsTo(OrganizationShiftRotationPattern::class, 'organization_work_shift_rotation_pattern_id', 'organization_work_shift_rotation_pattern_id');
    }



}
