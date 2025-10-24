<?php

namespace App\Models\AttendenceModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceDeviationReason extends Model
{
    use HasFactory;

    protected $table = 'organization_attendance_deviation_reasons';

    protected $primaryKey = 'organization_attendance_deviation_reason_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'organization_attendance_deviation_reason_type_id',
        'attendance_deviation_reason_name',
        'description',
        'is_active'
    ];


   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function DeviationReasonType()
    {
        return $this->belongsTo(AttendenceDeviationReasonType::class, 'organization_attendance_deviation_reason_type_id', 'organization_attendance_deviation_reason_type_id');
    }


}
