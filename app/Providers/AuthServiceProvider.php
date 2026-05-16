<?php

namespace App\Providers;

use App\Models\Document;
use App\Policies\DocumentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Daftar policy yang terdaftar di aplikasi.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}