<?php

namespace App\Providers;

use App\Models\Docente;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrap();
        /* View::composer('layouts.docente', function ($view) {
            $docente = auth()->user();
            $view->with('docente', $docente);
        }); */
        View::composer('layouts.docente', function ($view) {
            if (auth()->check()) {
                $docente = Docente::where('user_id', auth()->id())->first();
                $view->with('docente', $docente);
            }
        });
    }
}
