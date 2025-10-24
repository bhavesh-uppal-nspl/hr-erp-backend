<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationHoliday extends Model
{
    use HasFactory;

    protected $table = 'organization_holidays';

    // Define the primary key
    protected $primaryKey = 'organization_holiday_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_holiday_template_id',
        'organization_holiday_calendar_id',
        'organization_holiday_type_id',
        'holiday_date',
        'holiday_name',
        'description',
        'is_recurring',
        'entry_source',
        'created_by'
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }



    public function holidayCalendar()
    {
        return $this->hasMany(OrganizationHolidayCalendar::class, 'organization_holiday_calendar_id', 'organization_holiday_calendar_id');
    }

    public function holidayType()
    { 
        return $this->hasMany(OrganizationHolidayTypes::class, 'organization_holiday_type_id', 'organization_holiday_type_id');
    }



}
