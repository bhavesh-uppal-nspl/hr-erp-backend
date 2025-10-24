<?php

namespace App\Models\PayrollModels;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustmentType extends Model
{
    use HasFactory;
    protected $table = 'organization_payroll_adjustment_types';
    protected $primaryKey = 'organization_payroll_adjustment_type_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'organization_entity_id',
        'organization_id',
        'adjustment_type_name',
        'adjustment_direction',
        'description',
        'is_active',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

}
