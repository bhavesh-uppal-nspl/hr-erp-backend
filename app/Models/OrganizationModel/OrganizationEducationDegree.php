<?php
namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEducationDegree extends Model
{
    use HasFactory;
    protected $table = 'organization_education_degrees';

    protected $primaryKey = 'organization_education_degree_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id ',
        'organization_education_level_id',
        'organization_education_degree_id',
        'education_degree_name',
        'education_degree_short_name',
        'description',
        'sort_order',
        'is_active',
        'organization_entity_id'
    ];
    // Relationships
   

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function levels()
    {
        return $this->hasMany(OrganizationEducationLevel::class, 'organization_education_level_id', 'organization_education_level_id');
    }
}
