<?php

namespace App\Models\AttendenceModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendenceStatusType extends Model
{
    use HasFactory;

    protected $table = 'organization_attendance_status_types';

    protected $primaryKey = 'organization_attendance_status_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'attendance_status_type_name',
        'attendance_status_type_code',
        'description',
        'is_active'


    ];


      public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

}
