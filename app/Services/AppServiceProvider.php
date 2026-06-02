<?php

namespace App\Services;

use App\Models\Dog;
use App\Models\Like;
use App\Policies\DogPolicy;
use App\Policies\LikePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        // Policies
        Gate::policy(Dog::class, DogPolicy::class);
        Gate::policy(Like::class, LikePolicy::class);
    }
}
