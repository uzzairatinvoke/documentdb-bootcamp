<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::policy(Document::class, DocumentPolicy::class);
        // Gate = semakan ability am (bukan terikat pada satu rekod model).
        // Gunakan Gate::authorize('nama-ability') dalam controller.

        Gate::define('access-admin', function (User $user): bool {
            return $user->hasRole('admin');
        });

        // Gate::define('view-own-document', function(User $user, Document $document){
        //     return $user->id == $document->user_id;
        // });

        Gate::define('create-document', function (User $user): bool {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('update-document', function (User $user): bool {
            return $user->hasRole('admin');
        });

        Gate::define('delete-document', function (User $user): bool {
            return $user->hasRole('admin');
        });
    }
}
