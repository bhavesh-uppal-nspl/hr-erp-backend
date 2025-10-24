<?php

namespace App\Models\OrganizationModel;

use App\Models\EmployeesModel\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationBusinessUnit extends Model
{
    use HasFactory;

    protected $table = 'organization_business_units';

    protected $primaryKey = 'organization_business_unit_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',   
        'business_unit_name',
        'business_unit_short_name',
        'description',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function location()
    {
        return $this->belongsTo(OrganizationLocation::class, 'location_id', 'location_id');
    }

    public function divisions()
    {
        return $this->hasMany(OrganizationBusinessDivision::class, 'organization_business_unit_id', 'organization_business_unit_id');
    }

    
    public function employees()
    {
        return $this->hasMany(Employees::class, 'organization_business_unit_id', 'organization_business_unit_id');
    }

}
