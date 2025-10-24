<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollCycle extends Model
{
    use HasFactory;

    protected $table = 'organization_payroll_cycles';

    protected $primaryKey = 'organization_payroll_cycle_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'payroll_cycle_name',
        'organization_entity_id',
        'organization_id',
        'pay_frequency',
        'monthly_period_start_day',
        'week_start_day',
    ];


       public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
