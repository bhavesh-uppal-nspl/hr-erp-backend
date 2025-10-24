<?php
namespace App\Models\EmployeesModel;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveMonthlySummary extends Model
{
    use HasFactory;
    protected $table = 'employee_leave_monthly_summaries';
    protected $primaryKey = 'employee_leave_monthly_summary_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_entity_id',
        'year',
        'month',
        'total_leave_days',
        'approved_leave_days',
        'unapproved_leave_days',
        'medical_leaves',
        'earned_leaves', 
        'compensatory_off_leaves',
        'leave_without_pay',
        'leave_with_pay'
    ];


     protected $casts = [
        'leave_type_summary'     => 'array',
        'leave_category_summary' => 'array',
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
}
