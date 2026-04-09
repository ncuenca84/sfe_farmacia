<?php

namespace App\Services;

use App\Enums\Ambiente;
use App\Exceptions\LimiteComprobanteException;
use App\Exceptions\PlanVencidoException;
use App\Models\Emisor;
use App\Models\EmisorSuscripcion;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuscripcionService
{
    /**
     * Asigna un plan al emisor calculando fecha_fin automáticamente.
     */
    public function asignarPlan(Emisor $emisor, Plan $plan, Carbon $inicio): EmisorSuscripcion
    {
        $fin = match ($plan->tipo_periodo->value) {
            'MENSUAL' => $inicio->copy()->addMonth()->subDay(),
            'ANUAL' => $inicio->copy()->addYear()->subDay(),
            'DIAS' => $inicio->copy()->addDays($plan->dias)->subDay(),
        };

        $this->vencerSuscripcionActual($emisor);

        return EmisorSuscripcion::create([
            'emisor_id' => $emisor->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'comprobantes_usados' => 0,
            'estado' => 'ACTIVA',
        ]);
    }

    /**
     * Asigna un plan con fechas explícitas (usado por WHMCS).
     */
    public function asignarPlanConFechas(
        Emisor $emisor,
        Plan $plan,
        Carbon $inicio,
        Carbon $fin
    ): EmisorSuscripcion {
        $this->vencerSuscripcionActual($emisor);

        return EmisorSuscripcion::create([
            'emisor_id' => $emisor->id,
            'plan_id' => $plan->id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'comprobantes_usados' => 0,
            'estado' => 'ACTIVA',
        ]);
    }

    /**
     * Verifica el límite e incrementa el contador de forma atómica.
     * Usa lockForUpdate para evitar race conditions entre verificar y contar.
     *
     * @throws PlanVencidoException|LimiteComprobanteException
     */
    public function verificarEIncrementar(Emisor $emisor): void
    {
        if ($emisor->ambiente === Ambiente::PRUEBAS) {
            return;
        }

        DB::transaction(function () use ($emisor) {
            $suscripcion = EmisorSuscripcion::where('emisor_id', $emisor->id)
                ->where('estado', 'ACTIVA')
                ->lockForUpdate()
                ->latest()
                ->first();

            if (!$suscripcion) {
                throw new PlanVencidoException('No tiene plan activo.');
            }

            if ($suscripcion->fecha_fin < today()) {
                $suscripcion->update(['estado' => 'VENCIDA']);
                throw new PlanVencidoException(
                    'Su plan venció el ' . $suscripcion->fecha_fin->format('d/m/Y')
                );
            }

            $limite = $suscripcion->plan->cant_comprobante;
            if ($limite > 0 && $suscripcion->comprobantes_usados >= $limite) {
                throw new LimiteComprobanteException(
                    'Alcanzó el límite de comprobantes del plan.'
                );
            }

            $suscripcion->increment('comprobantes_usados');
        });
    }

    /**
     * Verifica que el emisor puede emitir comprobantes (solo lectura).
     *
     * @throws PlanVencidoException|LimiteComprobanteException
     */
    public function verificarLimite(Emisor $emisor): void
    {
        // No verificar límites en ambiente de pruebas
        if ($emisor->ambiente === Ambiente::PRUEBAS) {
            return;
        }

        $suscripcion = $emisor->suscripcionActiva;

        if (!$suscripcion) {
            throw new PlanVencidoException('No tiene plan activo.');
        }

        if ($suscripcion->fecha_fin < today()) {
            $suscripcion->update(['estado' => 'VENCIDA']);
            throw new PlanVencidoException(
                'Su plan venció el ' . $suscripcion->fecha_fin->format('d/m/Y')
            );
        }

        if ($suscripcion->plan->cant_comprobante > 0 &&
            $suscripcion->comprobantes_usados >= $suscripcion->plan->cant_comprobante) {
            throw new LimiteComprobanteException(
                'Alcanzó el límite de comprobantes del plan.'
            );
        }
    }

    /**
     * Incrementa el contador de comprobantes usados.
     */
    public function incrementarContador(Emisor $emisor): void
    {
        // No contabilizar comprobantes en ambiente de pruebas
        if ($emisor->ambiente === Ambiente::PRUEBAS) {
            return;
        }

        $emisor->suscripcionActiva?->increment('comprobantes_usados');
    }

    /**
     * Decrementa el contador de comprobantes usados.
     */
    public function decrementarContador(Emisor $emisor): void
    {
        // No decrementar en ambiente de pruebas (nunca se incrementó)
        if ($emisor->ambiente === Ambiente::PRUEBAS) {
            return;
        }

        $suscripcion = $emisor->suscripcionActiva;

        if ($suscripcion && $suscripcion->comprobantes_usados > 0) {
            $suscripcion->decrement('comprobantes_usados');
        }
    }

    /**
     * Vence la suscripción actual del emisor.
     */
    private function vencerSuscripcionActual(Emisor $emisor): void
    {
        $emisor->suscripciones()
            ->where('estado', 'ACTIVA')
            ->update(['estado' => 'VENCIDA']);
    }
}
