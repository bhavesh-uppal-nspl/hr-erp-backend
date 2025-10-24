<?php
namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationWorkModel extends Model
{
    use HasFactory;
    protected $table = 'organization_work_models';
    protected $primaryKey = 'organization_work_model_id';
    public $timestamps = true;

    protected $fillable = [
        'organization_id',
        'work_model_name',
        'organization_configuration_template_id',
        'organization_entity_id'
    ];
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


   

}
