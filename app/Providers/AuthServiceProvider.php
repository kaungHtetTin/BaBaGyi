<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('my-course',function($user,$course){
            return $user->id === $course->instructor->user->id;
        });

        Gate::define('my-blog',function($user,$blog){
            return $user->id === $blog->user_id;
        });

        Gate::define('my-answer', function($user,$answer){
            return $user->id === $answer->user_id;
        });

        Gate::define('my-conversation',function($user,$conversation){
            return $user->id === $conversation->my_id;
        });

    }
}
