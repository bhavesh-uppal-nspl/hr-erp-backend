<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class OrganizationHolidayTemplates extends Model
{
    use HasFactory;

    protected $table = 'organization_holiday_templates';
    protected $primaryKey = 'organization_holiday_template_id';
    public $timestamps = true;

    protected $fillable = [
        'organization_id',
        'holiday_template_name',
        'holiday_template_code',
        'description',
        'organization_entity_id',
        'scope',
        'general_country_id',
        'general_state_id',
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
