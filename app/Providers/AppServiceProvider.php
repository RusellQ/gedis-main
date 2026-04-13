<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Expediente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
   
public function boot()
{
    View::composer('*', function ($view) {
        if (!Auth::check()) {
            $view->with('expedientesVencidos', collect())
                 ->with('expedientesPorVencer', collect());
            return;
        }

        $user = Auth::user();

        // Asegurar que el usuario tenga asignación válida
        if (!$user->id_dependencia || !$user->id_departamento) {
            $view->with('expedientesVencidos', collect())
                 ->with('expedientesPorVencer', collect());
            return;
        }

        $hoy = Carbon::today();
        $seisMesesDespues = $hoy->copy()->addMonths(6);

        $vencidos = Expediente::where('id_dependencia', $user->id_dependencia)
            ->where('id_departamento', $user->id_departamento)
            ->whereNotNull('fecha_cierre')
            ->where('fecha_cierre', '<=', $hoy)
            ->get();

        $porVencer = Expediente::where('id_dependencia', $user->id_dependencia)
            ->where('id_departamento', $user->id_departamento)
            ->whereNotNull('fecha_cierre')
            ->whereBetween('fecha_cierre', [$hoy->copy()->addDay(), $seisMesesDespues])
            ->get();

        $view->with('expedientesVencidos', $vencidos)
             ->with('expedientesPorVencer', $porVencer);
    });
}
}
