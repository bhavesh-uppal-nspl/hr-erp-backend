<?php

namespace App\Providers;

use App\Models\EmployeesModel\EmployeeLeaves;
use App\Models\OrganizationModel\OrganizationLeaveEntitlement;
use App\Observers\EmployeeLeavesObserver;
use App\Observers\OrganizationLeaveEntitlementObserver;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
 public function boot()
{
    OrganizationLeaveEntitlement::observe(OrganizationLeaveEntitlementObserver::class);

    EmployeeLeaves::observe(EmployeeLeavesObserver::class);
}





}
