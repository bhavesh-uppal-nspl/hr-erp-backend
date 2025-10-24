<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternLeaveBalances extends Model
{
    use HasFactory;

    protected $table = 'intern_leave_balances';

    protected $primaryKey = 'intern_leave_balance_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'organization_leave_type_id',
        'leave_period_start_date',
        'leave_period_end_date ',
        'entitled_days',
        'carry_forward_days',
        'leave_taken_days',
        'encashed_days',
        'adjusted_days',
        'balance_days'
 

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   

}
