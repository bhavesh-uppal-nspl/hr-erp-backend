<?php

namespace App\Models\AttendenceModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceDeviationReasonType extends Model
{
    use HasFactory;

    protected $table = 'organization_attendance_deviation_reason_types';

    protected $primaryKey = 'organization_attendance_deviation_reason_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'deviation_reason_type_name',
        'is_active'
    ];

      public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

}
