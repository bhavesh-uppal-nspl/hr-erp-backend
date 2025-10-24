<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_runs';

    protected $primaryKey = 'organization_payroll_run_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'payroll_cycle_id',
        'period_start_date',
        'period_end_date',
        'status',
        'remarks',

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function PayrollCycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id', 'payroll_cycle_id');
    }

}
