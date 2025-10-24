<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationWorkShiftBreak extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'organization_work_shift_breaks';

    // Primary key
    protected $primaryKey = 'organization_work_shift_break_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       
        'organization_id',
        'organization_entity_id',
        'organization_work_shift_id',
        'organization_attendance_break_id',
        'created_at',
        'updated_at'

    ];

    // Relationships
   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }



     public function workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }

      public function AttendanceBreak()
    {
        return $this->belongsTo(OrganizationAttendanceBreak::class, 'organization_attendance_break_id', 'organization_attendance_break_id');
    }

 
}
