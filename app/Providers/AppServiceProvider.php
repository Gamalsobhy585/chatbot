<?php

namespace App\Providers;

use App\Repositories\ChatRepository;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
    }

    public function boot(): void
    {
        //
    }
}