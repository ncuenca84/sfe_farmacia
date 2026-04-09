<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\GuiaDetalle;
use App\Models\LiquidacionCompra;
use App\Models\NotaCredito;
use App\Models\NotaDebito;
use App\Models\Retencion;
use App\Models\Guia;
use App\Models\Proforma;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorPNG;

class PdfService
{
    /**
     * Genera código de barras Code 128 como base64 PNG.
     */
    private function generarBarcode(string $data): string
    {
        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($data, $generator::TYPE_CODE_128, 1, 40);
        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Genera código QR como base64 PNG.
     */
    private function generarQR(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale'           => 4,
            'imageBase64'     => true,
        ]);
        return (new QRCode($options))->render($data);
    }

    /**
     * Resuelve el logo: prioriza el del establecimiento, si no tiene usa el del emisor.
     */
    private function resolverLogoComprobante($comprobante): ?string
    {
        $logoEstablecimiento = $comprobante->establecimiento->logo_path ?? null;
        if ($logoEstablecimiento) {
            $resolved = $this->resolverLogoPath($logoEstablecimiento);
            if ($resolved) return $resolved;
        }

        return $this->resolverLogoPath($comprobante->emisor->logo_path ?? null);
    }

    /**
     * Resuelve la ruta absoluta del logo del emisor.
     */
    private function resolverLogoPath(?string $logoPath): ?string
    {
        if (!$logoPath) return null;

        // Ruta absoluta (nuevo sistema: /home/usuario/documentos/RUC/logos/logo.png)
        if (str_starts_with($logoPath, '/') && file_exists($logoPath)) {
            $path = $logoPath;
        } else {
            // Ruta relativa de Laravel Storage (sistema anterior)
            $path = public_path('storage/' . $logoPath);
            if (!file_exists($path)) {
                $path = storage_path('app/public/' . $logoPath);
            }
        }

        if (!file_exists($path)) return null;

        $mime = mime_content_type($path) ?: 'image/png';
        $data = file_get_contents($path);
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    /**
     * Genera PDF de factura bajo demanda — nunca se guarda en disco.
     */
    public function factura(Factura $factura): Response
    {
        $factura->load([
            'detalles.impuestos', 'cliente', 'emisor',
            'establecimiento', 'ptoEmision',
            'camposAdicionales', 'reembolsos', 'infoGuia',
        ]);

        $barcode = $factura->clave_acceso ? $this->generarBarcode($factura->clave_acceso) : null;
        $qrCode = $factura->clave_acceso ? $this->generarQR($factura->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($factura);

        $pdf = Pdf::loadView('pdf.factura', compact('factura', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'FAC' . $factura->establecimiento->codigo
            . '-' . $factura->ptoEmision->codigo
            . '-' . str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT)
            . '.pdf';

        return $pdf->download($nombre);
    }

    public function notaCredito(NotaCredito $nc): Response
    {
        $nc->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $nc->clave_acceso ? $this->generarBarcode($nc->clave_acceso) : null;
        $qrCode = $nc->clave_acceso ? $this->generarQR($nc->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($nc);

        $pdf = Pdf::loadView('pdf.nota-credito', compact('nc', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'NC' . $nc->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function notaDebito(NotaDebito $nd): Response
    {
        $nd->load(['motivos.impuestoIva', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $nd->clave_acceso ? $this->generarBarcode($nd->clave_acceso) : null;
        $qrCode = $nd->clave_acceso ? $this->generarQR($nd->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($nd);

        $pdf = Pdf::loadView('pdf.nota-debito', compact('nd', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'ND' . $nd->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function retencion(Retencion $ret): Response
    {
        $ret->load(['impuestosRetencion', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $ret->clave_acceso ? $this->generarBarcode($ret->clave_acceso) : null;
        $qrCode = $ret->clave_acceso ? $this->generarQR($ret->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($ret);

        $pdf = Pdf::loadView('pdf.retencion', compact('ret', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'RET' . $ret->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function retencionAts(\App\Models\RetencionAts $ret): Response
    {
        $ret->load(['docSustentos.desgloses', 'docSustentos.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $ret->clave_acceso ? $this->generarBarcode($ret->clave_acceso) : null;
        $qrCode = $ret->clave_acceso ? $this->generarQR($ret->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($ret);

        $pdf = Pdf::loadView('pdf.retencion-ats', compact('ret', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'RET-ATS-' . $ret->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function retencionAtsPos(\App\Models\RetencionAts $ret): Response
    {
        $ret->load(['docSustentos.desgloses', 'docSustentos.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $ret->clave_acceso ? $this->generarBarcode($ret->clave_acceso) : null;
        $qrCode = $ret->clave_acceso ? $this->generarQR($ret->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($ret);

        $nombre = 'POS-RET-ATS-' . ($ret->numero_completo ?? $ret->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.retencion-ats', compact('ret', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function liquidacion(LiquidacionCompra $liq): Response
    {
        $liq->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $liq->clave_acceso ? $this->generarBarcode($liq->clave_acceso) : null;
        $qrCode = $liq->clave_acceso ? $this->generarQR($liq->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($liq);

        $pdf = Pdf::loadView('pdf.liquidacion', compact('liq', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'LIQ' . $liq->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function guia(Guia $guia): Response
    {
        $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);

        $barcode = $guia->clave_acceso ? $this->generarBarcode($guia->clave_acceso) : null;
        $qrCode = $guia->clave_acceso ? $this->generarQR($guia->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($guia);

        $pdf = Pdf::loadView('pdf.guia', compact('guia', 'barcode', 'qrCode', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'GR' . $guia->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    public function proforma(Proforma $proforma): Response
    {
        $proforma->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision']);

        $logoPath = $this->resolverLogoComprobante($proforma);

        $pdf = Pdf::loadView('pdf.proforma', compact('proforma', 'logoPath'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);

        $nombre = 'PROF' . $proforma->numero_completo . '.pdf';
        return $pdf->download($nombre);
    }

    // ─── POS (Ticket) PDF Methods ────────────────────────────────────────────────

    private function generarPdfPos(string $view, array $data, string $nombre): Response
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm width, long roll
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);
        return $pdf->download($nombre);
    }

    public function facturaPos(Factura $factura): Response
    {
        $factura->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $factura->clave_acceso ? $this->generarBarcode($factura->clave_acceso) : null;
        $qrCode = $factura->clave_acceso ? $this->generarQR($factura->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($factura);

        $nombre = 'POS-FAC' . $factura->establecimiento->codigo . '-' . $factura->ptoEmision->codigo . '-' . str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) . '.pdf';
        return $this->generarPdfPos('pdf.pos.factura', compact('factura', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function notaCreditoPos(NotaCredito $nc): Response
    {
        $nc->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $nc->clave_acceso ? $this->generarBarcode($nc->clave_acceso) : null;
        $qrCode = $nc->clave_acceso ? $this->generarQR($nc->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($nc);

        $nombre = 'POS-NC' . ($nc->numero_completo ?? $nc->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.nota-credito', compact('nc', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function notaDebitoPos(NotaDebito $nd): Response
    {
        $nd->load(['motivos.impuestoIva', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $nd->clave_acceso ? $this->generarBarcode($nd->clave_acceso) : null;
        $qrCode = $nd->clave_acceso ? $this->generarQR($nd->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($nd);

        $nombre = 'POS-ND' . ($nd->numero_completo ?? $nd->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.nota-debito', compact('nd', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function retencionPos(Retencion $ret): Response
    {
        $ret->load(['impuestosRetencion', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $ret->clave_acceso ? $this->generarBarcode($ret->clave_acceso) : null;
        $qrCode = $ret->clave_acceso ? $this->generarQR($ret->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($ret);

        $nombre = 'POS-RET' . ($ret->numero_completo ?? $ret->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.retencion', compact('ret', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function liquidacionPos(LiquidacionCompra $liq): Response
    {
        $liq->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $liq->clave_acceso ? $this->generarBarcode($liq->clave_acceso) : null;
        $qrCode = $liq->clave_acceso ? $this->generarQR($liq->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($liq);

        $nombre = 'POS-LIQ' . ($liq->numero_completo ?? $liq->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.liquidacion', compact('liq', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function guiaPos(Guia $guia): Response
    {
        $guia->load(['detalles', 'emisor', 'establecimiento', 'ptoEmision', 'camposAdicionales']);
        $barcode = $guia->clave_acceso ? $this->generarBarcode($guia->clave_acceso) : null;
        $qrCode = $guia->clave_acceso ? $this->generarQR($guia->clave_acceso) : null;
        $logoPath = $this->resolverLogoComprobante($guia);

        $nombre = 'POS-GR' . ($guia->numero_completo ?? $guia->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.guia', compact('guia', 'barcode', 'qrCode', 'logoPath'), $nombre);
    }

    public function proformaPos(Proforma $proforma): Response
    {
        $proforma->load(['detalles.impuestos', 'cliente', 'emisor', 'establecimiento', 'ptoEmision']);
        $logoPath = $this->resolverLogoComprobante($proforma);

        $nombre = 'POS-PROF' . ($proforma->numero_completo ?? $proforma->id) . '.pdf';
        return $this->generarPdfPos('pdf.pos.proforma', compact('proforma', 'logoPath'), $nombre);
    }
}
