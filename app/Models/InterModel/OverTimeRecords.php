<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverTimeRecords extends Model
{
    use HasFactory;

    protected $table = 'intern_attendance_overtime_records';

    protected $primaryKey = 'intern_attendance_overtime_record_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_attendance_timelog_id',
        'intern_id',
        'attendance_date',
        'start_time',
        'end_time',
        'overtime_minutes',
        'deviation_reason_id',
        'compensation_type',
        'is_approved',
        'approved_by_intern_manager_id',
        'approved_at',
        'remarks'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
