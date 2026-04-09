<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paginas_legales', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('titulo', 255);
            $table->longText('contenido');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        DB::table('paginas_legales')->insert([
            [
                'slug' => 'terminos-y-condiciones',
                'titulo' => 'Términos y Condiciones',
                'contenido' => '<p>Contenido de los Términos y Condiciones.</p>',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'politica-proteccion-datos',
                'titulo' => 'Política de Protección de Datos',
                'contenido' => '<h2>Política de Tratamiento de Datos Personales</h2>
<h3>Plataforma de Facturación Electrónica</h3>

<h4>1. Introducción</h4>
<p>La presente Política de Tratamiento de Datos Personales regula el tratamiento de la información personal realizado en la plataforma de facturación electrónica, en cumplimiento de la Ley Orgánica de Protección de Datos Personales del Ecuador (LOPDP).</p>
<p>Esta política es aplicable a todos los usuarios que contratan y utilizan los planes de facturación electrónica distribuidos por la Responsable del Tratamiento, permitiendo la emisión, gestión y almacenamiento técnico de comprobantes electrónicos conforme a la normativa del Servicio de Rentas Internas (SRI).</p>

<h4>2. Responsable del Tratamiento</h4>
<ul>
<li>Nombre: NIXON MIGUEL CUENCA JIMA</li>
<li>RUC: 1793190705001</li>
<li>Domicilio: Pichincha / Quito / Cotocollao</li>
<li>Correo electrónico para derechos ARCO: info@exxalink.com</li>
</ul>

<h4>3. Rol en el Tratamiento de Datos</h4>
<p>La Responsable actúa bajo dos modalidades:</p>
<ul>
<li>Como Responsable del Tratamiento, respecto de los datos de sus propios clientes.</li>
<li>Como Encargada del Tratamiento, respecto de los datos personales que los clientes ingresan en la plataforma para emitir comprobantes electrónicos a terceros.</li>
</ul>

<h4>4. Datos Personales Tratados</h4>
<ul>
<li>Datos de identificación: nombres, apellidos, RUC o cédula.</li>
<li>Datos de contacto: correo electrónico, dirección y teléfono.</li>
<li>Datos comerciales y tributarios: información de facturación y comprobantes electrónicos.</li>
<li>Datos técnicos: dirección IP, registros de acceso, fecha y hora de conexión.</li>
<li>Datos financieros: información necesaria para la facturación y pagos.</li>
</ul>

<h4>5. Finalidades del Tratamiento</h4>
<ul>
<li>Prestación del servicio de facturación electrónica.</li>
<li>Emisión, autorización y almacenamiento técnico de comprobantes.</li>
<li>Gestión de planes, pagos y soporte técnico.</li>
<li>Cumplimiento de obligaciones legales ante el SRI.</li>
<li>Atención de solicitudes, reclamos y soporte.</li>
</ul>

<h4>6. Conservación de los Datos</h4>
<p>Los datos se conservarán mientras exista una relación contractual y durante los plazos exigidos por la normativa tributaria y de protección de datos. Posteriormente, serán eliminados, anonimizados o bloqueados.</p>

<h4>7. Cesión y Transferencia de Datos</h4>
<p>Los datos podrán ser alojados en infraestructura tecnológica propia o de terceros, incluso fuera del Ecuador, garantizando niveles adecuados de seguridad conforme a la LOPDP. No se comercializan datos personales.</p>

<h4>8. Derechos del Titular</h4>
<p>El titular podrá ejercer los derechos de acceso, rectificación, actualización, eliminación, oposición, portabilidad y suspensión del tratamiento, mediante solicitud al correo info@exxalink.com. El plazo de respuesta será de hasta quince (15) días.</p>

<h4>9. Responsabilidad del Cliente</h4>
<p>El cliente es responsable del tratamiento de los datos personales de terceros que ingrese en la plataforma y de contar con las bases legales correspondientes.</p>

<h4>10. Datos de Menores de Edad</h4>
<p>La plataforma no está dirigida a menores de edad. En caso excepcional, se requerirá consentimiento válido del representante legal.</p>

<h4>11. Medidas de Seguridad</h4>
<p>Se aplican medidas técnicas, organizativas y administrativas para proteger la información contra accesos no autorizados, pérdida o alteración.</p>

<h4>12. Modificaciones</h4>
<p>La presente política podrá ser actualizada en cualquier momento. Las modificaciones serán publicadas en la plataforma.</p>

<p><strong>Fecha de última actualización: Enero 2026</strong></p>',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('paginas_legales');
    }
};
