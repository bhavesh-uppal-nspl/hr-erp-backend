<?php
namespace App\Models\OrganizationModel;
use App\Models\ApplicationModels\ApplicationUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ApplicationOrganizationAcive extends Model
{
    use HasFactory;

    protected $table = 'application_organization_active';
    protected $primaryKey = 'application_organization_active_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
       
        'application_user_id',
        'organization_id',
    ];

    
    public function ApplicationUser()
    {
        return $this->belongsTo(ApplicationUsers::class, 'application_user_id', 'application_user_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function Settings()
    {
        return $this->belongsTo(OrganizationSetting::class, 'organization_id', 'organization_id');
    }
 
}
