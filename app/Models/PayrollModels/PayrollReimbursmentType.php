<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollReimbursmentType extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_reimbursement_types';
    protected $primaryKey = 'organization_payroll_reimbursement_type_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'reimbursement_type_name',
        'description',
        'max_amount',
        'max_frequency',
        'is_active',
    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

}
