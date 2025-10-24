<?php

namespace App\Models\EmployeesModel;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmployementExistReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExit extends Model
{
    use HasFactory;

    protected $table = 'employee_exits';

    protected $primaryKey = 'employee_exit_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'organization_id',
        'resignation_date',
        'notice_period_start',
        'organization_entity_id',
        'notice_period_end',
        'last_working_date',
        'relieving_date',
        'organization_employment_exit_reason_id',
        'exit_interview_done',
        'comments',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'notice_period_start' => 'date',
        'notice_period_end' => 'date',
        'last_working_date' => 'date',
        'relieving_date' => 'date',
        'exit_interview_done' => 'boolean',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id', 'employee_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function exitReason()
    {
        return $this->belongsTo(OrganizationEmployementExistReason::class, 'organization_employee_exit_reason_id', 'organization_employment_exit_reason_id');
    }
}
