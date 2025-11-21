<?php

namespace App\Providers;

use App\Models\Alumno;
use App\Models\Docente;
use App\Models\PeriodoActual;
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
        $periodoActual = PeriodoActual::where('actual', true)->first();

        View::composer('layouts.docente', function ($view) use ($periodoActual) {             
            if (auth()->check()) {
                $docente = Docente::where('user_id', auth()->id())->first();
                $view->with([
                    
                    'docente' => $docente,
                    'periodoActual' => $periodoActual,
                ]);
            }
        });
        // 🔹 Layout Alumno
        View::composer('layouts.alumno', function ($view) use ($periodoActual) {
            if (auth()->check()) {
                $alumno = Alumno::where('user_id', auth()->id())->first();
                $view->with([
                    'alumno' => $alumno,
                    'periodoActual' => $periodoActual,
                ]);
            }
        });
        view()->composer('layouts.profesionalizacion', function ($view) {
            $alumno = auth()->user()->alumnoB;
            $view->with('alumno', $alumno);
        });

        // 🔹 Layout Admin
        View::composer('layouts.admin', function ($view) use ($periodoActual) {
            $view->with('periodoActual', $periodoActual);
        });
    }
}
