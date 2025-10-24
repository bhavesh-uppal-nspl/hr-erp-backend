<?php

namespace App\Models\OrganizationModel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationWorkShift extends Model
{
    use HasFactory;

    protected $table = 'organization_work_shifts';

    protected $primaryKey = 'organization_work_shift_id';


    protected $fillable = [
        'organization_id',
        'organization_work_shift_type_id',
        'organization_location_id',
        'organization_entity_id',
        'work_shift_name',
        'work_duration_minutes',
        'work_shift_start_time',
        'work_shift_end_time',
        'break_duration_minutes',
        'is_active',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    

        public function shiftType()
    {
        return $this->belongsTo(OrganizationWorkShiftType::class, 'organization_work_shift_type_id', 'organization_work_shift_type_id');
    }

    

        public function location()
    {
        return $this->belongsTo(OrganizationLocation::class, 'organization_location_id', 'organization_location_id');
    }


     public function getTotalWorkMinutesAttribute()
    {
        $start = Carbon::parse($this->work_shift_start_time);
        $end   = Carbon::parse($this->work_shift_end_time);

        // Handle overnight shift (e.g. 10PM → 6AM next day)
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $end->diffInMinutes($start);

        return $totalMinutes - ($this->break_duration_minutes ?? 0);
    }

    public function getTotalBreakMinutesAttribute()
    {
        return $this->break_duration_minutes ?? 0;
    }

}
