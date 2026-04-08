<?php

namespace App\Providers;

use App\Models\AdminFid;
use App\Models\AdminPpd;
use App\Models\Alumno;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        view()->composer('layouts.admin', function ($view) {
            $admision = AdminFid::where('estado', true)->first();
            $view->with('admision', $admision);
        });
        view()->composer('layouts.admin', function ($view) {
            $admision = AdminFid::where('estado', true)->first();
            $periodoActualPpd = PeriodoActualPpd::where('actual', true)->first();

            $view->with('admision', $admision);
            $view->with('periodoActualPpd', $periodoActualPpd);
        });

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
        
        View::composer('layouts.admin', function ($view) use ($periodoActual) {
            $view->with('periodoActual', $periodoActual);
        });
        
        View::composer('layouts.admin', function ($view) {

            $periodoAdmisionActivo = AdminPpd::where('estado', true)->exists();
            $view->with('periodoAdmisionActivo', $periodoAdmisionActivo);
        });
    }
}
