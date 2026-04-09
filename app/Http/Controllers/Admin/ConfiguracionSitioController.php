<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSitio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionSitioController extends Controller
{
    public function index(): View
    {
        $logoExists = Storage::disk('public')->exists('site/logo.png');

        $config = [
            'nombre_sitio' => ConfiguracionSitio::obtener('nombre_sitio', config('app.name', 'SistemSFE')),
            'mail_host' => ConfiguracionSitio::obtener('mail_host'),
            'mail_port' => ConfiguracionSitio::obtener('mail_port', '587'),
            'mail_username' => ConfiguracionSitio::obtener('mail_username'),
            'mail_encryption' => ConfiguracionSitio::obtener('mail_encryption', 'tls'),
            'mail_from_address' => ConfiguracionSitio::obtener('mail_from_address'),
            'mail_from_name' => ConfiguracionSitio::obtener('mail_from_name'),
        ];

        return view('admin.configuracion-sitio', compact('logoExists', 'config'));
    }

    public function guardarGeneral(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre_sitio' => 'nullable|string|max:100',
        ]);

        ConfiguracionSitio::guardar('nombre_sitio', $request->nombre_sitio);

        return back()->with('success', 'Configuración general guardada.');
    }

    public function guardarMail(Request $request): RedirectResponse
    {
        $request->validate([
            'mail_host' => 'nullable|string|max:150',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:ssl,tls',
            'mail_from_address' => 'nullable|email|max:150',
            'mail_from_name' => 'nullable|string|max:150',
        ]);

        $campos = ['mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name'];

        foreach ($campos as $campo) {
            ConfiguracionSitio::guardar($campo, $request->input($campo));
        }

        if ($request->filled('mail_password')) {
            ConfiguracionSitio::guardar('mail_password', encrypt($request->mail_password));
        }

        return back()->with('success', 'Configuración de correo guardada.');
    }

    public function probarMail(Request $request): RedirectResponse
    {
        $request->validate(['email_prueba' => 'required|email']);

        try {
            $this->configurarMailDesdeBD();

            \Illuminate\Support\Facades\Mail::raw(
                'Este es un correo de prueba desde ' . ConfiguracionSitio::nombreSitio() . '.',
                function ($message) use ($request) {
                    $message->to($request->email_prueba)
                            ->subject('Correo de prueba - ' . ConfiguracionSitio::nombreSitio());
                }
            );

            return back()->with('success', 'Correo de prueba enviado a ' . $request->email_prueba);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar: ' . $e->getMessage());
        }
    }

    public function guardarLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ]);

        Storage::disk('public')->makeDirectory('site');
        $request->file('logo')->storeAs('site', 'logo.png', 'public');

        return back()->with('success', 'Logo actualizado correctamente.');
    }

    public function eliminarLogo(): RedirectResponse
    {
        Storage::disk('public')->delete('site/logo.png');
        return back()->with('success', 'Logo eliminado.');
    }

    private function configurarMailDesdeBD(): void
    {
        $host = ConfiguracionSitio::obtener('mail_host');
        if (!$host) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) ConfiguracionSitio::obtener('mail_port', '587'),
            'mail.mailers.smtp.username' => ConfiguracionSitio::obtener('mail_username'),
            'mail.mailers.smtp.password' => $this->obtenerMailPassword(),
            'mail.mailers.smtp.encryption' => ConfiguracionSitio::obtener('mail_encryption', 'tls'),
            'mail.from.address' => ConfiguracionSitio::obtener('mail_from_address'),
            'mail.from.name' => ConfiguracionSitio::obtener('mail_from_name', ConfiguracionSitio::nombreSitio()),
        ]);
    }

    private function obtenerMailPassword(): ?string
    {
        $encrypted = ConfiguracionSitio::obtener('mail_password');
        if (!$encrypted) {
            return null;
        }

        try {
            return decrypt($encrypted);
        } catch (\Exception $e) {
            return $encrypted;
        }
    }
}
