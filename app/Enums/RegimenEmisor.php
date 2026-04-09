<?php

namespace App\Enums;

enum RegimenEmisor: string
{
    case GENERAL = 'GENERAL';
    case RIMPE = 'RIMPE';
    case NEGOCIO_POPULAR = 'NEGOCIO_POPULAR';
    case EPS = 'EPS';

    public function nombre(): string
    {
        return match ($this) {
            self::GENERAL => 'RÉGIMEN GENERAL',
            self::RIMPE => 'RIMPE EMPRENDEDOR',
            self::NEGOCIO_POPULAR => 'RIMPE NEGOCIO POPULAR',
            self::EPS => 'RÉGIMEN EPS',
        };
    }

    /**
     * Valor exacto para el elemento <contribuyenteRimpe> del XML SRI.
     * Solo aplica para RIMPE y NEGOCIO_POPULAR. Retorna null para los demás.
     */
    public function leyendaRimpeXml(): ?string
    {
        return match ($this) {
            self::RIMPE => 'CONTRIBUYENTE RÉGIMEN RIMPE',
            self::NEGOCIO_POPULAR => 'CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE',
            default => null,
        };
    }

    public function esRimpe(): bool
    {
        return in_array($this, [self::RIMPE, self::NEGOCIO_POPULAR]);
    }
}
