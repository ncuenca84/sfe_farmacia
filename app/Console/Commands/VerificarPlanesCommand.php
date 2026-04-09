<?php

namespace App\Console\Commands;

use App\Models\EmisorSuscripcion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarPlanesCommand extends Command
{
    protected $signature = 'sistemSFE:verificar-planes';
    protected $description = 'Verifica planes vencidos, envía alertas y marca suscripciones expiradas';

    public function handle(): int
    {
        $this->info('Verificando planes de emisores...');

        // Marcar como VENCIDA las suscripciones expiradas
        $vencidas = EmisorSuscripcion::where('estado', 'ACTIVA')
            ->where('fecha_fin', '<', today())
            ->get();

        foreach ($vencidas as $suscripcion) {
            $suscripcion->update(['estado' => 'VENCIDA']);
            $this->warn("Vencida: Emisor #{$suscripcion->emisor_id} - Plan venció el {$suscripcion->fecha_fin->format('d/m/Y')}");
        }

        $this->info("Suscripciones vencidas: {$vencidas->count()}");

        // Alertar emisores que vencen en 7 días
        $porVencer = EmisorSuscripcion::where('estado', 'ACTIVA')
            ->where('fecha_fin', '<=', now()->addDays(7))
            ->where('fecha_fin', '>=', today())
            ->with(['emisor', 'plan'])
            ->get();

        foreach ($porVencer as $suscripcion) {
            $dias = $suscripcion->diasRestantes();
            $this->info("Por vencer: {$suscripcion->emisor->razon_social} - {$dias} días restantes");
            // TODO: enviar email de alerta al emisor
        }

        $this->info("Emisores por vencer (7 días): {$porVencer->count()}");

        Log::info("sistemSFE:verificar-planes - Vencidas: {$vencidas->count()}, Por vencer: {$porVencer->count()}");

        return Command::SUCCESS;
    }
}
