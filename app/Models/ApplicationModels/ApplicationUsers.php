<?php

namespace App\Models\ApplicationModels;

use App\Models\ClientModels\Client;
use App\Models\OrganizationModel\ApplicationOrganizationAcive;
use App\Models\OrganizationModel\OrganizationUser;
use App\Models\OrganizationModel\OrganizationUserRoleAssignment;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ApplicationUsers extends Authenticatable implements JWTSubject
{
    protected $table = 'application_users';
    protected $primaryKey = 'application_user_id';
    public $timestamps = true;

    protected $fillable = [
        'full_name',
        'password',
        'email',
        'password_hash',
        'is_active',
        'last_login_at',
        'created_at',
        'client_id',
        'updated_at',
        'otp',
        'otp_verified',
        'otp_created_at',
        'phone_number',
        'country_phone_code',
        'account_created'
    ];

    // Cast these fields to their proper data types
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

     public function Client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

     public function RoleAssignment()
    {
        return $this->belongsTo(OrganizationUserRoleAssignment::class, 'application_user_id', 'application_user_id');
    }


      public function OrganizationUsers()
    {
        return $this->belongsTo(OrganizationUser::class, 'application_user_id', 'application_user_id');
    }

      public function UserPermission()
    {
        return $this->belongsTo(ApplicationUserPermission::class, 'application_user_id', 'application_user_id');
    }

      public function Errorlogs()
    {
        return $this->belongsTo(ApplicationErrorLogs::class, 'application_user_id', 'application_user_id');
    }

      public function userlogin()
    {
        return $this->belongsTo(Applicationuserloginlogs::class, 'application_user_id', 'application_user_id');
    }

       public function RoleAssignmentLogs()
    {
        return $this->belongsTo(ApplicationUserRoleAssignmentLog::class, 'application_user_id', 'application_user_id');
    }


      public function PermissionAuditLogs()
    {
        return $this->belongsTo(ApplicationUserPermissionAuditLogs::class, 'application_user_id', 'application_user_id');
    }

    public function UserActiveOrganization()
    {
        return $this->hasOne(ApplicationOrganizationAcive::class, 'application_user_id', 'application_user_id');
    }



    //    public function Client()
    // {
    //     return $this->hasMany(Client::class, 'client_id', 'client_id');
    // }
      public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
