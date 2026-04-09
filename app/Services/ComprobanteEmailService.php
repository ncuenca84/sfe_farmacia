<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ComprobanteEmailService
{
    private const TIPOS_DOC = [
        'factura' => 'FACTURA',
        'nota-credito' => 'NOTA DE CREDITO',
        'nota-debito' => 'NOTA DE DEBITO',
        'retencion' => 'RETENCION',
        'retencion-ats' => 'RETENCION',
        'liquidacion' => 'LIQUIDACION DE COMPRA',
        'guia' => 'GUIA DE REMISION',
        'proforma' => 'PROFORMA',
    ];

    private const PREFIJOS_ARCHIVO = [
        'factura' => 'FAC',
        'nota-credito' => 'NC',
        'nota-debito' => 'ND',
        'retencion' => 'RET',
        'retencion-ats' => 'RET-ATS',
        'liquidacion' => 'LIQ',
        'guia' => 'GR',
        'proforma' => 'PROF',
    ];

    public function enviar(
        Request $request,
        Model $comprobante,
        string $tipoDoc,
        string $pdfView,
        string $varName,
    ): RedirectResponse {
        $request->validate([
            'emails' => 'required|string',
        ]);

        $emails = array_filter(array_map('trim', explode(',', $request->input('emails'))));

        if (empty($emails)) {
            return back()->with('error', 'Debe ingresar al menos un email.');
        }

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', "El email '$email' no es valido.");
            }
        }

        $emisor = $comprobante->emisor;

        // Configurar SMTP del emisor
        if ($emisor->mail_host && $emisor->mail_username) {
            $this->configurarSmtpEmisor($emisor);
        } else {
            return back()->with('error', 'El emisor no tiene configuracion de email SMTP completa. Configure el correo en Configuracion del Emisor.');
        }

        // Logo: primero del establecimiento, si no del emisor (convertir a base64)
        $logoPath = $this->resolverLogoBase64($comprobante->establecimiento->logo_path ?? null)
            ?? $this->resolverLogoBase64($emisor->logo_path ?? null);

        // Barcode y QR
        $claveAcceso = $comprobante->clave_acceso ?? null;
        $barcode = $claveAcceso ? $this->generarBarcode($claveAcceso) : null;
        $qrCode = $claveAcceso ? $this->generarQR($claveAcceso) : null;

        // Generar PDF
        $pdf = Pdf::loadView($pdfView, [
            $varName => $comprobante,
            'logoPath' => $logoPath,
            'barcode' => $barcode,
            'qrCode' => $qrCode,
        ]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);
        $pdfContent = $pdf->output();

        // Datos para el email
        $numeroCompleto = $comprobante->numero_completo ?? $comprobante->id;
        $prefijo = self::PREFIJOS_ARCHIVO[$tipoDoc] ?? strtoupper($tipoDoc);
        $tipoNombre = self::TIPOS_DOC[$tipoDoc] ?? strtoupper($tipoDoc);
        $nombrePdf = "{$prefijo}{$numeroCompleto}.pdf";
        $razonSocial = $comprobante->cliente?->razon_social
            ?? $comprobante->proveedor?->razon_social
            ?? 'Cliente';
        $valorTotal = $comprobante->importe_total ?? $comprobante->total ?? null;

        // Adjuntar XML si existe
        $xmlPath = $comprobante->xml_path ?? null;
        $xmlContent = null;
        $nombreXml = null;
        if ($xmlPath && file_exists($xmlPath)) {
            $xmlContent = file_get_contents($xmlPath);
            $nombreXml = "{$prefijo}{$numeroCompleto}.xml";
        }

        // Cuerpo HTML del email
        $body = $this->construirCuerpoEmail($razonSocial, $emisor->razon_social, $tipoNombre, $numeroCompleto, $valorTotal);

        Mail::html(
            $body,
            function ($message) use ($emails, $pdfContent, $nombrePdf, $xmlContent, $nombreXml, $emisor) {
                $message->to($emails)
                    ->subject('Nuevo Comprobante Electronico')
                    ->attachData($pdfContent, $nombrePdf, ['mime' => 'application/pdf']);

                if ($xmlContent && $nombreXml) {
                    $message->attachData($xmlContent, $nombreXml, ['mime' => 'application/xml']);
                }
            }
        );

        return back()->with('success', 'Email enviado correctamente a: ' . implode(', ', $emails));
    }

    /**
     * Envia automaticamente el comprobante al email del cliente tras autorizacion SRI.
     */
    public function enviarAutomatico(
        Model $comprobante,
        string $tipoDoc,
        string $pdfView,
        string $varName,
    ): void {
        $cliente = $comprobante->cliente ?? $comprobante->proveedor ?? null;
        if (!$cliente || empty($cliente->email)) {
            return;
        }

        $emisor = $comprobante->emisor;
        if (!$emisor || !$emisor->mail_host || !$emisor->mail_username) {
            return;
        }

        try {
            $emails = array_filter(array_map('trim', explode(',', $cliente->email)));
            if (empty($emails)) {
                return;
            }

            $this->configurarSmtpEmisor($emisor);

            $logoPath = $this->resolverLogoBase64($comprobante->establecimiento->logo_path ?? null)
                ?? $this->resolverLogoBase64($emisor->logo_path ?? null);

            $claveAcceso = $comprobante->clave_acceso ?? null;
            $barcode = $claveAcceso ? $this->generarBarcode($claveAcceso) : null;
            $qrCode = $claveAcceso ? $this->generarQR($claveAcceso) : null;

            $pdf = Pdf::loadView($pdfView, [
                $varName => $comprobante,
                'logoPath' => $logoPath,
                'barcode' => $barcode,
                'qrCode' => $qrCode,
            ]);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true]);
            $pdfContent = $pdf->output();

            $numeroCompleto = $comprobante->numero_completo ?? $comprobante->id;
            $prefijo = self::PREFIJOS_ARCHIVO[$tipoDoc] ?? strtoupper($tipoDoc);
            $tipoNombre = self::TIPOS_DOC[$tipoDoc] ?? strtoupper($tipoDoc);
            $nombrePdf = "{$prefijo}{$numeroCompleto}.pdf";
            $razonSocial = $cliente->razon_social ?? 'Cliente';
            $valorTotal = $comprobante->importe_total ?? $comprobante->total ?? null;

            $xmlPath = $comprobante->xml_path ?? null;
            $xmlContent = null;
            $nombreXml = null;
            if ($xmlPath && file_exists($xmlPath)) {
                $xmlContent = file_get_contents($xmlPath);
                $nombreXml = "{$prefijo}{$numeroCompleto}.xml";
            }

            $body = $this->construirCuerpoEmail($razonSocial, $emisor->razon_social, $tipoNombre, $numeroCompleto, $valorTotal);

            Mail::html(
                $body,
                function ($message) use ($emails, $pdfContent, $nombrePdf, $xmlContent, $nombreXml) {
                    $message->to($emails)
                        ->subject('Nuevo Comprobante Electronico')
                        ->attachData($pdfContent, $nombrePdf, ['mime' => 'application/pdf']);

                    if ($xmlContent && $nombreXml) {
                        $message->attachData($xmlContent, $nombreXml, ['mime' => 'application/xml']);
                    }
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error al enviar email automatico: {$e->getMessage()}");
        }
    }

    private function configurarSmtpEmisor(Model $emisor): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $emisor->mail_host,
            'mail.mailers.smtp.port' => $emisor->mail_port ?? 465,
            'mail.mailers.smtp.username' => $emisor->mail_username,
            'mail.mailers.smtp.password' => $emisor->mail_password,
            'mail.mailers.smtp.encryption' => $emisor->mail_encryption ?? 'ssl',
            'mail.from.address' => $emisor->mail_from_address ?? $emisor->mail_username,
            'mail.from.name' => $emisor->mail_from_name ?? $emisor->razon_social,
        ]);
        Mail::purge('smtp');
    }

    private function construirCuerpoEmail(string $cliente, string $emisor, string $tipoDoc, string $numero, ?string $valorTotal): string
    {
        $valorLinea = $valorTotal !== null
            ? '<p style="margin:8px 0;"><strong>Valor Total:</strong> ' . number_format((float) $valorTotal, 2) . '</p>'
            : '';

        return <<<HTML
        <div style="font-family: Arial, sans-serif; font-size: 14px; color: #333; max-width: 600px;">
            <p>Estimado(a),</p>
            <p><strong>{$cliente}</strong></p>
            <p>Esta es una notificacion automatica de un documento tributario electronico emitido por <strong>{$emisor}</strong>.</p>
            <p style="margin:8px 0;"><strong>Tipo de Comprobante:</strong> {$tipoDoc}</p>
            <p style="margin:8px 0;"><strong>Nro de Comprobante:</strong> {$numero}</p>
            {$valorLinea}
            <p>Los detalles generales del comprobante pueden ser consultados en el archivo pdf adjunto en este correo.</p>
            <p style="font-size:12px; color:#666;">Nota: Las tildes han sido omitidas intencionalmente para evitar problemas de lectura.</p>
            <p><strong>Atentamente,</strong></p>
            <p><strong>{$emisor}</strong></p>
        </div>
        HTML;
    }

    private function resolverLogoBase64(?string $logoRaw): ?string
    {
        if (!$logoRaw) return null;

        if (str_starts_with($logoRaw, '/') && file_exists($logoRaw)) {
            $path = $logoRaw;
        } else {
            $path = public_path('storage/' . $logoRaw);
            if (!file_exists($path)) {
                $path = storage_path('app/public/' . $logoRaw);
            }
        }

        if (!file_exists($path)) return null;

        $mime = mime_content_type($path) ?: 'image/png';
        $data = file_get_contents($path);
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    private function generarBarcode(string $data): string
    {
        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($data, $generator::TYPE_CODE_128, 1, 40);
        return 'data:image/png;base64,' . base64_encode($png);
    }

    private function generarQR(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale'           => 4,
            'imageBase64'     => true,
        ]);
        return (new QRCode($options))->render($data);
    }
}
