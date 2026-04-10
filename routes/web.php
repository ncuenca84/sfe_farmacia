<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaginaLegalPublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Emisor;
use Illuminate\Support\Facades\Route;

// Redirigir raíz a login o dashboard según autenticación
Route::get('/', HomeController::class);

// Logo del sitio (servido por Laravel para evitar restricciones de symlinks en Nginx)
Route::get('/site/logo', function () {
    $path = storage_path('app/public/site/logo.png');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
})->name('site.logo');

// Páginas legales públicas
Route::get('/legal/{slug}', [PaginaLegalPublicController::class, 'show'])->name('legal.show');

// ─── Panel Admin /admin/* ─────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'role:ROLE_ADMIN'])
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // CRUD Emisores
        Route::resource('emisores', Admin\EmisorController::class)->parameters(['emisores' => 'emisor']);
        Route::patch('emisores/{emisor}/activar', [Admin\EmisorController::class, 'activar'])->name('emisores.activar');
        Route::post('emisores/{emisor}/eliminar', [Admin\EmisorController::class, 'eliminarPermanente'])->name('emisores.eliminar');

        // Impersonar emisor (acceder al panel de facturacion como soporte)
        Route::post('emisores/{emisor}/impersonar', [Admin\EmisorController::class, 'impersonar'])->name('emisores.impersonar');
        Route::post('emisores/dejar-impersonar', [Admin\EmisorController::class, 'dejarImpersonar'])->name('emisores.dejar-impersonar');

        // CRUD Planes
        Route::resource('planes', Admin\PlanController::class)->parameters(['planes' => 'plan']);

        // CRUD Usuarios
        Route::resource('usuarios', Admin\UsuarioController::class)->parameters(['usuarios' => 'usuario']);

        // Establecimientos y Puntos de Emisión
        Route::resource('establecimientos', Admin\EstablecimientoController::class)->parameters(['establecimientos' => 'establecimiento']);
        Route::resource('puntos-emision', Admin\PtoEmisionController::class)->parameters(['puntos-emision' => 'puntosEmision']);

        // Configuración del Sitio
        Route::get('configuracion-sitio', [Admin\ConfiguracionSitioController::class, 'index'])->name('configuracion-sitio.index');
        Route::post('configuracion-sitio/general', [Admin\ConfiguracionSitioController::class, 'guardarGeneral'])->name('configuracion-sitio.guardar-general');
        Route::post('configuracion-sitio/mail', [Admin\ConfiguracionSitioController::class, 'guardarMail'])->name('configuracion-sitio.guardar-mail');
        Route::post('configuracion-sitio/mail/probar', [Admin\ConfiguracionSitioController::class, 'probarMail'])->name('configuracion-sitio.probar-mail');
        Route::post('configuracion-sitio/logo', [Admin\ConfiguracionSitioController::class, 'guardarLogo'])->name('configuracion-sitio.guardar-logo');
        Route::delete('configuracion-sitio/logo', [Admin\ConfiguracionSitioController::class, 'eliminarLogo'])->name('configuracion-sitio.eliminar-logo');

        // Catálogos de impuestos
        Route::resource('impuesto-ivas', Admin\ImpuestoIvaController::class);
        Route::resource('impuesto-ices', Admin\ImpuestoIceController::class);
        Route::resource('impuesto-irbpnrs', Admin\ImpuestoIrbpnrController::class);
        Route::resource('codigos-retencion', Admin\CodigoRetencionController::class);

        // API AJAX Admin
        Route::get('api/consultar-ruc/{ruc}', [Admin\EmisorController::class, 'consultarRuc'])->name('api.consultar-ruc');

        // Páginas Legales
        Route::resource('paginas-legales', Admin\PaginaLegalController::class)->parameters(['paginas-legales' => 'paginaLegale']);

        // CRM
        Route::prefix('crm')->name('crm.')->group(function () {
            Route::get('/', [Admin\CrmController::class, 'index'])->name('index');
            Route::get('/clientes', [Admin\CrmController::class, 'clientes'])->name('clientes');
            Route::get('/firmas', [Admin\CrmController::class, 'firmas'])->name('firmas');
            Route::get('/suscripciones', [Admin\CrmController::class, 'suscripciones'])->name('suscripciones');
            Route::post('/suscripciones/{suscripcion}/suspender', [Admin\CrmController::class, 'suspender'])->name('suspender');
            Route::post('/suscripciones/{suscripcion}/reactivar', [Admin\CrmController::class, 'reactivar'])->name('reactivar');
            Route::get('/notificaciones', [Admin\CrmController::class, 'notificaciones'])->name('notificaciones');
            Route::get('/notificaciones/crear', [Admin\CrmController::class, 'crearNotificacion'])->name('notificaciones.crear');
            Route::post('/notificaciones/enviar', [Admin\CrmController::class, 'enviarNotificacion'])->name('notificaciones.enviar');
            Route::get('/notificaciones/{notificacion}', [Admin\CrmController::class, 'verNotificacion'])->name('notificaciones.ver');
            Route::get('/emisor/{emisor}/historial', [Admin\CrmController::class, 'historialEmisor'])->name('emisor-historial');
            Route::post('/emisor/{emisor}/nota', [Admin\CrmController::class, 'agregarNota'])->name('agregar-nota');

            // Firmas Electrónicas CRUD
            Route::prefix('firmas-electronicas')->name('firmas-electronicas.')->group(function () {
                Route::get('/', [Admin\FirmaElectronicaController::class, 'index'])->name('index');
                Route::get('/crear', [Admin\FirmaElectronicaController::class, 'create'])->name('create');
                Route::post('/', [Admin\FirmaElectronicaController::class, 'store'])->name('store');
                Route::get('/{firma}/editar', [Admin\FirmaElectronicaController::class, 'edit'])->name('edit');
                Route::put('/{firma}', [Admin\FirmaElectronicaController::class, 'update'])->name('update');
                Route::delete('/{firma}', [Admin\FirmaElectronicaController::class, 'destroy'])->name('destroy');
                Route::post('/leer-p12', [Admin\FirmaElectronicaController::class, 'leerP12'])->name('leer-p12');
                Route::post('/importar-excel', [Admin\FirmaElectronicaController::class, 'importarExcel'])->name('importar');
                Route::get('/descargar-plantilla', [Admin\FirmaElectronicaController::class, 'descargarPlantilla'])->name('plantilla');
                Route::get('/exportar', [Admin\FirmaElectronicaController::class, 'exportar'])->name('exportar');
            });
        });

        // WHMCS
        Route::prefix('whmcs')->name('whmcs.')->group(function () {
            Route::get('/configuracion', [Admin\WhmcsController::class, 'configuracion'])->name('configuracion');
            Route::post('/configuracion', [Admin\WhmcsController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
            Route::post('/generar-api-key', [Admin\WhmcsController::class, 'generarApiKey'])->name('generar-api-key');
            Route::get('/servicios', [Admin\WhmcsController::class, 'servicios'])->name('servicios');
            Route::get('/planes', [Admin\WhmcsController::class, 'planes'])->name('planes');
            Route::post('/planes/{plan}/mapear', [Admin\WhmcsController::class, 'mapearPlan'])->name('planes.mapear');
        });
    });

// ─── Panel Facturación / ───────────────────────────────────────────────────────
Route::middleware(['auth', 'emisor.activo', 'role:ROLE_ADMIN,ROLE_EMISOR_ADMIN,ROLE_EMISOR'])
    ->name('emisor.')
    ->group(function () {
        Route::get('/dashboard', [Emisor\DashboardController::class, 'index'])->name('dashboard');

        // Comprobantes
        Route::prefix('comprobantes')->name('comprobantes.')->group(function () {
            Route::resource('facturas', Emisor\FacturaController::class);
            Route::post('facturas/{factura}/procesar', [Emisor\FacturaController::class, 'procesar'])->name('facturas.procesar');
            Route::get('facturas/{factura}/pdf', [Emisor\FacturaController::class, 'pdf'])->name('facturas.pdf');
            Route::get('facturas/{factura}/pdf-pos', [Emisor\FacturaController::class, 'pdfPos'])->name('facturas.pdf-pos');
            Route::get('facturas/{factura}/xml', [Emisor\FacturaController::class, 'xml'])->name('facturas.xml');
            Route::post('facturas/{factura}/email', [Emisor\FacturaController::class, 'email'])->name('facturas.email');
            Route::post('facturas/{factura}/anular', [Emisor\FacturaController::class, 'anular'])->name('facturas.anular');
            Route::post('facturas/{factura}/clonar', [Emisor\FacturaController::class, 'clonar'])->name('facturas.clonar');
            Route::post('facturas/{factura}/consultar-estado', [Emisor\FacturaController::class, 'consultarEstado'])->name('facturas.consultar-estado');
            Route::get('facturas/{factura}/crear-guia', [Emisor\GuiaController::class, 'crearDesdeFactura'])->name('facturas.crear-guia');

            Route::resource('notas-credito', Emisor\NotaCreditoController::class)->parameters(['notas-credito' => 'notaCredito']);
            Route::post('notas-credito/{notaCredito}/procesar', [Emisor\NotaCreditoController::class, 'procesar'])->name('notas-credito.procesar');
            Route::get('notas-credito/{notaCredito}/pdf', [Emisor\NotaCreditoController::class, 'pdf'])->name('notas-credito.pdf');
            Route::get('notas-credito/{notaCredito}/pdf-pos', [Emisor\NotaCreditoController::class, 'pdfPos'])->name('notas-credito.pdf-pos');
            Route::get('notas-credito/{notaCredito}/xml', [Emisor\NotaCreditoController::class, 'xml'])->name('notas-credito.xml');
            Route::post('notas-credito/{notaCredito}/consultar-estado', [Emisor\NotaCreditoController::class, 'consultarEstado'])->name('notas-credito.consultar-estado');
            Route::post('notas-credito/{notaCredito}/clonar', [Emisor\NotaCreditoController::class, 'clonar'])->name('notas-credito.clonar');
            Route::post('notas-credito/{notaCredito}/email', [Emisor\NotaCreditoController::class, 'email'])->name('notas-credito.email');
            Route::post('notas-credito/{notaCredito}/anular', [Emisor\NotaCreditoController::class, 'anular'])->name('notas-credito.anular');

            Route::resource('notas-debito', Emisor\NotaDebitoController::class)->parameters(['notas-debito' => 'notaDebito']);
            Route::post('notas-debito/{notaDebito}/procesar', [Emisor\NotaDebitoController::class, 'procesar'])->name('notas-debito.procesar');
            Route::get('notas-debito/{notaDebito}/pdf', [Emisor\NotaDebitoController::class, 'pdf'])->name('notas-debito.pdf');
            Route::get('notas-debito/{notaDebito}/pdf-pos', [Emisor\NotaDebitoController::class, 'pdfPos'])->name('notas-debito.pdf-pos');
            Route::get('notas-debito/{notaDebito}/xml', [Emisor\NotaDebitoController::class, 'xml'])->name('notas-debito.xml');
            Route::post('notas-debito/{notaDebito}/consultar-estado', [Emisor\NotaDebitoController::class, 'consultarEstado'])->name('notas-debito.consultar-estado');
            Route::post('notas-debito/{notaDebito}/clonar', [Emisor\NotaDebitoController::class, 'clonar'])->name('notas-debito.clonar');
            Route::post('notas-debito/{notaDebito}/email', [Emisor\NotaDebitoController::class, 'email'])->name('notas-debito.email');
            Route::post('notas-debito/{notaDebito}/anular', [Emisor\NotaDebitoController::class, 'anular'])->name('notas-debito.anular');

            Route::resource('retenciones', Emisor\RetencionController::class)->parameters(['retenciones' => 'retencion']);
            Route::post('retenciones/{retencion}/procesar', [Emisor\RetencionController::class, 'procesar'])->name('retenciones.procesar');
            Route::get('retenciones/{retencion}/pdf', [Emisor\RetencionController::class, 'pdf'])->name('retenciones.pdf');
            Route::get('retenciones/{retencion}/pdf-pos', [Emisor\RetencionController::class, 'pdfPos'])->name('retenciones.pdf-pos');
            Route::get('retenciones/{retencion}/xml', [Emisor\RetencionController::class, 'xml'])->name('retenciones.xml');
            Route::post('retenciones/{retencion}/consultar-estado', [Emisor\RetencionController::class, 'consultarEstado'])->name('retenciones.consultar-estado');
            Route::post('retenciones/{retencion}/clonar', [Emisor\RetencionController::class, 'clonar'])->name('retenciones.clonar');
            Route::post('retenciones/{retencion}/email', [Emisor\RetencionController::class, 'email'])->name('retenciones.email');
            Route::post('retenciones/{retencion}/anular', [Emisor\RetencionController::class, 'anular'])->name('retenciones.anular');

            Route::resource('retenciones-ats', Emisor\RetencionAtsController::class)->parameters(['retenciones-ats' => 'retencionAt']);
            Route::post('retenciones-ats/{retencionAt}/procesar', [Emisor\RetencionAtsController::class, 'procesar'])->name('retenciones-ats.procesar');
            Route::get('retenciones-ats/{retencionAt}/xml', [Emisor\RetencionAtsController::class, 'xml'])->name('retenciones-ats.xml');
            Route::post('retenciones-ats/{retencionAt}/consultar-estado', [Emisor\RetencionAtsController::class, 'consultarEstado'])->name('retenciones-ats.consultar-estado');
            Route::get('retenciones-ats/{retencionAt}/pdf', [Emisor\RetencionAtsController::class, 'pdf'])->name('retenciones-ats.pdf');
            Route::get('retenciones-ats/{retencionAt}/pdf-pos', [Emisor\RetencionAtsController::class, 'pdfPos'])->name('retenciones-ats.pdf-pos');
            Route::post('retenciones-ats/{retencionAt}/clonar', [Emisor\RetencionAtsController::class, 'clonar'])->name('retenciones-ats.clonar');
            Route::post('retenciones-ats/{retencionAt}/email', [Emisor\RetencionAtsController::class, 'email'])->name('retenciones-ats.email');

            Route::resource('liquidaciones', Emisor\LiquidacionCompraController::class)->parameters(['liquidaciones' => 'liquidacion']);
            Route::post('liquidaciones/{liquidacion}/procesar', [Emisor\LiquidacionCompraController::class, 'procesar'])->name('liquidaciones.procesar');
            Route::get('liquidaciones/{liquidacion}/pdf', [Emisor\LiquidacionCompraController::class, 'pdf'])->name('liquidaciones.pdf');
            Route::get('liquidaciones/{liquidacion}/pdf-pos', [Emisor\LiquidacionCompraController::class, 'pdfPos'])->name('liquidaciones.pdf-pos');
            Route::get('liquidaciones/{liquidacion}/xml', [Emisor\LiquidacionCompraController::class, 'xml'])->name('liquidaciones.xml');
            Route::post('liquidaciones/{liquidacion}/consultar-estado', [Emisor\LiquidacionCompraController::class, 'consultarEstado'])->name('liquidaciones.consultar-estado');
            Route::post('liquidaciones/{liquidacion}/clonar', [Emisor\LiquidacionCompraController::class, 'clonar'])->name('liquidaciones.clonar');
            Route::post('liquidaciones/{liquidacion}/email', [Emisor\LiquidacionCompraController::class, 'email'])->name('liquidaciones.email');
            Route::post('liquidaciones/{liquidacion}/anular', [Emisor\LiquidacionCompraController::class, 'anular'])->name('liquidaciones.anular');

            Route::resource('guias', Emisor\GuiaController::class);
            Route::post('guias/{guia}/procesar', [Emisor\GuiaController::class, 'procesar'])->name('guias.procesar');
            Route::get('guias/{guia}/pdf', [Emisor\GuiaController::class, 'pdf'])->name('guias.pdf');
            Route::get('guias/{guia}/pdf-pos', [Emisor\GuiaController::class, 'pdfPos'])->name('guias.pdf-pos');
            Route::get('guias/{guia}/xml', [Emisor\GuiaController::class, 'xml'])->name('guias.xml');
            Route::post('guias/{guia}/consultar-estado', [Emisor\GuiaController::class, 'consultarEstado'])->name('guias.consultar-estado');
            Route::post('guias/{guia}/clonar', [Emisor\GuiaController::class, 'clonar'])->name('guias.clonar');
            Route::post('guias/{guia}/email', [Emisor\GuiaController::class, 'email'])->name('guias.email');
            Route::post('guias/{guia}/anular', [Emisor\GuiaController::class, 'anular'])->name('guias.anular');

            Route::resource('proformas', Emisor\ProformaController::class);
            Route::get('proformas/{proforma}/pdf', [Emisor\ProformaController::class, 'pdf'])->name('proformas.pdf');
            Route::get('proformas/{proforma}/pdf-pos', [Emisor\ProformaController::class, 'pdfPos'])->name('proformas.pdf-pos');
            Route::post('proformas/{proforma}/email', [Emisor\ProformaController::class, 'email'])->name('proformas.email');
            Route::post('proformas/{proforma}/clonar', [Emisor\ProformaController::class, 'clonar'])->name('proformas.clonar');
            Route::post('proformas/{proforma}/facturar', [Emisor\ProformaController::class, 'convertirAFactura'])->name('proformas.facturar');
        });

        // Compras (XML upload)
        Route::prefix('compras')->name('compras.')->group(function () {
            Route::get('/', [Emisor\CompraController::class, 'index'])->name('index');
            Route::get('/create', [Emisor\CompraController::class, 'create'])->name('create');
            Route::post('/preview', [Emisor\CompraController::class, 'preview'])->name('preview');
            Route::get('/preview', fn () => redirect()->route('emisor.compras.create'));
            Route::post('/', [Emisor\CompraController::class, 'store'])->name('store');
            Route::get('/{compra}', [Emisor\CompraController::class, 'show'])->name('show');
            Route::delete('/{compra}', [Emisor\CompraController::class, 'destroy'])->name('destroy');
        });

        // Farmacia
        Route::prefix('farmacia')->name('farmacia.')->group(function () {
            Route::get('/dashboard', [Emisor\FarmaciaController::class, 'dashboard'])->name('dashboard');
            Route::get('/vencidos', [Emisor\FarmaciaController::class, 'vencidos'])->name('vencidos');
            Route::resource('categorias', Emisor\CategoriaProductoController::class)->parameters(['categorias' => 'categoria'])->except(['show']);
            Route::resource('proveedores', Emisor\ProveedorController::class)->parameters(['proveedores' => 'proveedor'])->except(['show']);
            Route::resource('presentaciones', Emisor\PresentacionController::class)->parameters(['presentaciones' => 'presentacion'])->except(['show']);
            Route::resource('laboratorios', Emisor\LaboratorioController::class)->parameters(['laboratorios' => 'laboratorio'])->except(['show']);

            // Punto de Venta (POS)
            Route::get('/pos', [Emisor\PosController::class, 'index'])->name('pos');
            Route::get('/pos/buscar-producto', [Emisor\PosController::class, 'buscarProducto'])->name('pos.buscar-producto');
            Route::post('/pos/facturar', [Emisor\PosController::class, 'facturar'])->name('pos.facturar');

            // Lotes (multi-lote FEFO)
            Route::get('/lotes', [Emisor\LoteController::class, 'index'])->name('lotes.index');
            Route::get('/lotes/ingreso', [Emisor\LoteController::class, 'ingreso'])->name('lotes.ingreso');
            Route::post('/lotes', [Emisor\LoteController::class, 'store'])->name('lotes.store');
            Route::get('/lotes/{lote}/kardex', [Emisor\LoteController::class, 'kardex'])->name('lotes.kardex');
            Route::get('/lotes/{lote}/ajuste', [Emisor\LoteController::class, 'ajuste'])->name('lotes.ajuste');
            Route::post('/lotes/{lote}/ajuste', [Emisor\LoteController::class, 'guardarAjuste'])->name('lotes.guardar-ajuste');

            // Ordenes de Compra
            Route::get('/compras', [Emisor\OrdenCompraController::class, 'index'])->name('compras.index');
            Route::get('/compras/crear', [Emisor\OrdenCompraController::class, 'create'])->name('compras.create');
            Route::post('/compras', [Emisor\OrdenCompraController::class, 'store'])->name('compras.store');
            Route::get('/compras/reposicion', [Emisor\OrdenCompraController::class, 'reposicion'])->name('compras.reposicion');
            Route::get('/compras/{compra}', [Emisor\OrdenCompraController::class, 'show'])->name('compras.show');
            Route::post('/compras/{compra}/recibir', [Emisor\OrdenCompraController::class, 'recibir'])->name('compras.recibir');

            // Reportes Farmacia
            Route::get('/reportes/rotacion', [Emisor\ReporteFarmaciaController::class, 'rotacion'])->name('reportes.rotacion');
            Route::get('/reportes/rentabilidad', [Emisor\ReporteFarmaciaController::class, 'rentabilidad'])->name('reportes.rentabilidad');
            Route::get('/reportes/vencidos-proveedor', [Emisor\ReporteFarmaciaController::class, 'vencidosProveedor'])->name('reportes.vencidos-proveedor');
        });

        // Inventario
        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/', [Emisor\InventarioController::class, 'index'])->name('index');
            Route::get('/kardex/{inventario}', [Emisor\InventarioController::class, 'kardex'])->name('kardex');
            Route::get('/ajuste', [Emisor\InventarioController::class, 'ajuste'])->name('ajuste');
            Route::post('/ajuste', [Emisor\InventarioController::class, 'guardarAjuste'])->name('guardar-ajuste');
            Route::get('/valorizado', [Emisor\InventarioController::class, 'valorizado'])->name('valorizado');
            Route::get('/export-pdf', [Emisor\InventarioController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [Emisor\InventarioController::class, 'exportExcel'])->name('export-excel');
        });

        // Códigos de Retención (consulta y gestión)
        Route::get('/codigos-retencion', [Emisor\CodigoRetencionController::class, 'index'])->name('codigos-retencion.index');
        Route::get('/codigos-retencion/buscar', [Emisor\CodigoRetencionController::class, 'buscar'])->name('codigos-retencion.buscar');
        Route::get('/codigos-retencion/create', [Emisor\CodigoRetencionController::class, 'create'])->name('codigos-retencion.create');
        Route::post('/codigos-retencion', [Emisor\CodigoRetencionController::class, 'store'])->name('codigos-retencion.store');
        Route::get('/codigos-retencion/{codigoRetencion}/edit', [Emisor\CodigoRetencionController::class, 'edit'])->name('codigos-retencion.edit');
        Route::put('/codigos-retencion/{codigoRetencion}', [Emisor\CodigoRetencionController::class, 'update'])->name('codigos-retencion.update');

        // Carga Masiva
        Route::get('/carga-masiva', [Emisor\CargaMasivaController::class, 'index'])->name('carga-masiva.index');
        Route::post('/carga-masiva', [Emisor\CargaMasivaController::class, 'store'])->name('carga-masiva.store');
        Route::get('/carga-masiva/plantilla/{tipo}', [Emisor\CargaMasivaController::class, 'descargarPlantilla'])->name('carga-masiva.plantilla');

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/comprobantes', [Emisor\ReporteController::class, 'comprobantes'])->name('comprobantes');
            Route::get('/ventas', [Emisor\ReporteController::class, 'ventas'])->name('ventas');
            Route::get('/ventas-detallada', [Emisor\ReporteController::class, 'ventasDetallada'])->name('ventas-detallada');
            Route::get('/retenciones-totalizadas', [Emisor\ReporteController::class, 'retencionesTotalizadas'])->name('retenciones-totalizadas');
            Route::get('/retenciones-factura', [Emisor\ReporteController::class, 'retencionesFactura'])->name('retenciones-factura');

            // Exportaciones Excel (max 5 por minuto por usuario)
            Route::middleware('throttle:5,1')->group(function () {
                Route::get('/export-comprobantes', [Emisor\ReporteController::class, 'exportComprobantes'])->name('export-comprobantes');
                Route::get('/export-ventas', [Emisor\ReporteController::class, 'exportVentas'])->name('export-ventas');
                Route::get('/export-ventas-detallada', [Emisor\ReporteController::class, 'exportVentasDetallada'])->name('export-ventas-detallada');
                Route::get('/export-retenciones-totalizadas', [Emisor\ReporteController::class, 'exportRetencionesTotalizadas'])->name('export-retenciones-totalizadas');
                Route::get('/export-retenciones-factura', [Emisor\ReporteController::class, 'exportRetencionesFactura'])->name('export-retenciones-factura');
            });
        });

        // Configuración
        Route::prefix('configuracion')->name('configuracion.')->middleware('role:ROLE_ADMIN,ROLE_EMISOR_ADMIN')->group(function () {
            Route::get('/emisor', [Emisor\ConfiguracionController::class, 'editEmisor'])->name('emisor');
            Route::put('/emisor', [Emisor\ConfiguracionController::class, 'updateEmisor'])->name('emisor.update');
            Route::resource('unidades-negocio', Emisor\UnidadNegocioController::class)->except(['show', 'destroy']);
            Route::resource('establecimientos', Emisor\EstablecimientoController::class);
            Route::resource('puntos-emision', Emisor\PtoEmisionController::class);
            Route::resource('usuarios', Emisor\UsuarioController::class);
            Route::resource('clientes', Emisor\ClienteController::class);
            Route::resource('productos', Emisor\ProductoController::class);
            Route::get('/impuestos', [Emisor\ConfiguracionController::class, 'impuestos'])->name('impuestos');
            Route::delete('/comprobantes-prueba', [Emisor\ConfiguracionController::class, 'eliminarComprobantesPrueba'])->name('eliminar-comprobantes-prueba');
        });

        // API AJAX para búsquedas
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/clientes/buscar', [Emisor\ClienteController::class, 'buscar'])->name('clientes.buscar');
            Route::get('/clientes/consultar/{identificacion}', [Emisor\ClienteController::class, 'consultarIdentificacion'])->name('clientes.consultar');
            Route::post('/clientes', [Emisor\ClienteController::class, 'apiStore'])->name('clientes.store');
            Route::get('/productos/buscar', [Emisor\ProductoController::class, 'buscar'])->name('productos.buscar');
            Route::post('/productos', [Emisor\ProductoController::class, 'apiStore'])->name('productos.store');
            Route::get('/transportistas/buscar', [Emisor\TransportistaController::class, 'buscar'])->name('transportistas.buscar');
            Route::post('/transportistas', [Emisor\TransportistaController::class, 'apiStore'])->name('transportistas.store');
        });
    });

// Perfil de usuario (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
