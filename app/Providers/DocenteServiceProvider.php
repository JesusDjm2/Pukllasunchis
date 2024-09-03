<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class DocenteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    public function boot()
    {
        // Compartir el ID del docente solo si el usuario tiene el rol "docente"
        View::composer('layouts.docente', function ($view) {
            if (Auth::check() && Auth::user()->hasRole('docente')) {
                $docenteId = Auth::user()->docente ? Auth::user()->docente->id : null;
                $view->with('docenteId', $docenteId);
            }
        });
    }

    /**
     * Bootstrap services.
     */
}
