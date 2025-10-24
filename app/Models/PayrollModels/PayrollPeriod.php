<?php

namespace App\Models\PayrollModels;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_periods';

    protected $primaryKey = 'organization_payroll_period_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'organization_payroll_cycle_id',
        'period_name',
        'period_start',
        'period_end',
        'period_month',
        'period_year',
        'is_closed'

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
