<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });
        
        Gate::define('manage-user', function($user){
            if($user->hasRole('Superadmin')){
                return true;
            }
            return false;
        });

        Gate::define('view-supplier', function($user){
            if($user->hasPermissionTo('view supplier')){
                return true;
            }
            return false;
        });

        Gate::define('view-mitra', function($user){
            if($user->hasPermissionTo('view mitra')){
                return true;
            }
            return false;
        });

        Gate::define('view-group', function($user){
            if($user->hasPermissionTo('view group')){
                return true;
            }
            return false;
        });

        Gate::define('view-zone', function($user){
            if($user->hasPermissionTo('view zone')){
                return true;
            }
            return false;
        });

        Gate::define('view-convert', function($user){
            if($user->hasPermissionTo('convert price and stock supplier')){
                return true;
            }
            return false;
        });

        Gate::define('view-stock-item', function($user){
            if($user->hasPermissionTo('view stock item')){
                return true;
            }
            return false;
        });

        Gate::define('view-stock-sales', function($user){
            if($user->hasPermissionTo('view stock sales')){
                return true;
            }
            return false;
        });

        Gate::define('view-buy-order', function($user){
            if($user->hasPermissionTo('view buy order')){
                return true;
            }
            return false;
        });

        Gate::define('view-sell-order', function($user){
            if($user->hasPermissionTo('view sell order')){
                return true;
            }
            return false;
        });

        Gate::define('edit-sell-order', function($user){
            if($user->hasPermissionTo('edit sell order')){
                return true;
            }
            return false;
        });

        Gate::define('view-all-master', function($user){
            if($user->hasPermissionTo('view all master')){
                return true;
            }
            return false;
        });

        Gate::define('view-list-item', function($user){
            if($user->hasPermissionTo('view list item')){
                return true;
            }
            return false;
        });
    }
}
