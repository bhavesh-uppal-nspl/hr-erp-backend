<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OrganizationHolidayCalendar extends Model
{
    use HasFactory;

    protected $table = 'organization_holiday_calendars';
    protected $primaryKey = 'organization_holiday_calendar_id';
    public $timestamps = true;

    protected $fillable = [
        'organization_id',
        'holiday_calendar_name',
        'organization_entity_id',
        'organization_holiday_template_id',
        'holiday_calendar_year_start_date',
        'holiday_calendar_year_end_date',
        'created_by'
    ];

    // Example relationship (if applicable)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function HolidayCalendar()
    {
        return $this->belongsTo(OrganizationHoliday::class, 'organization_holiday_calendar_id', 'organization_holiday_calendar_id');
    }

    
}
