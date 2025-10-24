<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternAttendanceDeviationRecord extends Model
{
    use HasFactory;

    protected $table = 'intern_attendance_deviation_records';

    protected $primaryKey = 'intern_attendance_deviation_record_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'intern_attendance_timelog_id',
        'reference_point',
        'deviation_reason_type_id',
        'deviation_reason_id',
        'expected_time',
        'actual_time',
        'deviation_minutes',
        'remarks',
        'attendance_date'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
