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
            $periodoActualPpd = PeriodoActualPpd::actual();

            $view->with('admision', $admision);
            $view->with('periodoActualPpd', $periodoActualPpd);
        });

        Paginator::useBootstrap();

        View::composer('layouts.docente', function ($view) {
            if (auth()->check()) {
                $docente = Docente::where('user_id', auth()->id())->first();
                $view->with([

                    'docente' => $docente,
                    'periodoActual' => PeriodoActual::actual(),
                ]);
            }
        });

        View::composer('layouts.alumno', function ($view) {
            if (auth()->check()) {
                $alumno = Alumno::where('user_id', auth()->id())->first();
                $view->with([
                    'alumno' => $alumno,
                    'periodoActual' => PeriodoActual::actual(),
                ]);
            }
        });

        view()->composer('layouts.profesionalizacion', function ($view) {
            $alumno = auth()->user()->alumnoB;
            $view->with('alumno', $alumno);
        });

        View::composer('layouts.admin', function ($view) {
            $view->with('periodoActual', PeriodoActual::actual());
        });
        
        View::composer('layouts.admin', function ($view) {

            $periodoAdmisionActivo = AdminPpd::where('estado', true)->exists();
            $view->with('periodoAdmisionActivo', $periodoAdmisionActivo);
        });

    }
}
