<?php

namespace App\Models\InterModel;

use App\Models\AttendenceModels\AttendenceBreakTypes;
use App\Models\AttendenceModels\AttendenceSource;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternTimeLogs extends Model
{
    use HasFactory;

    protected $table = 'intern_attendance_timelogs';

    protected $primaryKey = 'intern_attendance_timelog_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'attendance_date',
        'attendance_log_type',
        'attendance_log_time',
        'attendance_break_type_id',
        'attendance_source_type_id',
        'remarks'
    ];




    


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
    public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }

       public function breakType()
    {
        return $this->belongsTo(AttendenceBreakTypes::class, 'attendance_break_type_id', 'attendance_break_type_id');
    }
  public function sourceType()
    {
        return $this->belongsTo(AttendenceSource::class, 'attendance_source_type_id', 'organization_attendance_source_id');
    }


   
}
