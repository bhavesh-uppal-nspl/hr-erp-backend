<?php

namespace App\Models\OrganizationModel;
use App\Models\AttendenceModels\AttendenceBreakTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationAttendanceBreak extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'organization_attendance_breaks';

    // Primary key
    protected $primaryKey = 'organization_attendance_break_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       
        'organization_id',
        'organization_entity_id',
        'organization_attendance_break_type_id',
        'attendance_break_name',
        'description',
        'break_duration_minutes',
        'break_start_time',
        'break_end_time',
        'is_paid',
        'created_at',
        'updated_at'

    ];

    // Relationships
   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function BreakType()
    {
        return $this->belongsTo(AttendenceBreakTypes::class, 'organization_attendance_break_type_id', 'organization_attendance_break_type_id');
    }
 
}
