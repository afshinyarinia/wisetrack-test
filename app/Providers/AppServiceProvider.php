<?php

namespace App\Providers;

use App\Modules\Analytics\Repositories\EloquentPostAnalyticsRepository;
use App\Modules\Analytics\Repositories\EloquentPostViewRepository;
use App\Modules\Analytics\Repositories\PostAnalyticsRepositoryInterface;
use App\Modules\Analytics\Repositories\PostViewRepositoryInterface;
use App\Modules\Shared\Storage\ImageStorageInterface;
use App\Modules\Shared\Storage\LaravelImageStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageStorageInterface::class, LaravelImageStorage::class);
        $this->app->bind(PostViewRepositoryInterface::class, EloquentPostViewRepository::class);
        $this->app->bind(PostAnalyticsRepositoryInterface::class, EloquentPostAnalyticsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
