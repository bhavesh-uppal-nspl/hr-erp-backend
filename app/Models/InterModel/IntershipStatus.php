<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntershipStatus extends Model
{
    use HasFactory;

    protected $table = 'organization_internship_statuses';

    protected $primaryKey = 'organization_internship_status_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_configuration_template_id',
        'internship_status_name'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
