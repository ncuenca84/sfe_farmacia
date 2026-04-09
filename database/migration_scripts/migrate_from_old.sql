-- ============================================
-- MIGRACIÓN: BD vieja → BD nueva SFE (Laravel)
-- ============================================
-- CONFIGURACIÓN: Cambiar estas 2 variables según el servidor
-- SET @OLD_DB = 'nombre_bd_vieja';
-- SET @NEW_DB = 'nombre_bd_nueva';
-- Como MySQL no permite variables en nombres de BD,
-- se usa search/replace antes de ejecutar:
--   sed 's/OLD_DB/nombre_bd_vieja/g; s/NEW_DB/nombre_bd_nueva/g' migrate_from_old.sql > migrate_final.sql
-- ============================================
-- PREREQUISITOS:
--   1. BD nueva con migraciones Laravel ejecutadas (php artisan migrate)
--   2. Usuario MySQL con SELECT en BD vieja: GRANT SELECT ON old_db.* TO 'user'@'localhost';
--   3. Tablas nuevas vacías (o ejecutar truncate_all.sql primero)
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

USE NEW_DB;

-- ============================================
-- 1. CATÁLOGOS DE IMPUESTOS (primero por FK)
-- ============================================
INSERT IGNORE INTO impuesto_ivas (id, codigo_porcentaje, nombre, tarifa, activo, created_at, updated_at)
SELECT id, codigoPorcentaje, nombre, tarifa, 1, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.impuestoiva;

INSERT IGNORE INTO impuesto_ices (id, codigo_porcentaje, nombre, activo, created_at, updated_at)
SELECT id, codigoPorcentaje, nombre, 1, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.impuestoice;

INSERT IGNORE INTO impuesto_irbpnrs (id, codigo_porcentaje, nombre, activo, created_at, updated_at)
SELECT id, codigoPorcentaje, nombre, 1, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.impuestoirbpnr;

-- ============================================
-- 2. PLANES
-- ============================================
INSERT IGNORE INTO planes (id, nombre, cant_comprobante, precio,
    tipo_periodo, activo, created_at, updated_at)
SELECT id, CONCAT('Plan ', cantComprobante), cantComprobante, precio,
    CASE
        WHEN UPPER(periodo) = 'MENSUAL' THEN 'MENSUAL'
        WHEN UPPER(periodo) = 'ANUAL' THEN 'ANUAL'
        ELSE 'MENSUAL'
    END,
    COALESCE(activo, 1), NOW(), NOW()
FROM OLD_DB.plan;

-- ============================================
-- 3. EMISORES
-- ============================================
INSERT IGNORE INTO emisores (id, ruc, ambiente, tipo_emision, razon_social,
    nombre_comercial, direccion_matriz, obligado_contabilidad,
    contribuyente_especial,
    logo_path, firma_path, firma_password,
    mail_host, mail_username, mail_password, mail_port,
    mail_encryption,
    activo, created_at, updated_at)
SELECT id, ruc, ambiente, tipoEmision, razonSocial,
    nombreComercial, direccionMatriz,
    CASE WHEN obligadoContabilidad = 'SI' THEN 1 ELSE 0 END,
    contribuyenteEspecial,
    dirLogo, dirFirma, passFirma,
    servidorCorreo, correoRemitente, passCorreo, puerto,
    CASE WHEN SSLHabilitado = 1 THEN 'ssl' ELSE 'tls' END,
    COALESCE(activo, 1), COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.emisor;

-- ============================================
-- 4. SUSCRIPCIONES (desde datos embebidos en emisor viejo)
-- ============================================
INSERT IGNORE INTO emisor_suscripciones (emisor_id, plan_id,
    fecha_inicio, fecha_fin, comprobantes_usados,
    estado, created_at, updated_at)
SELECT e.id, e.plan_id,
    COALESCE(e.fechaInicio, CURDATE()),
    COALESCE(e.fechaFin, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
    0, 'ACTIVA', NOW(), NOW()
FROM OLD_DB.emisor e
WHERE e.plan_id IS NOT NULL;

-- ============================================
-- 5. ESTABLECIMIENTOS
-- ============================================
INSERT IGNORE INTO establecimientos (id, emisor_id, codigo, nombre,
    direccion, activo, created_at, updated_at)
SELECT id, emisor_id, codigo, nombre,
    direccion, COALESCE(activo, 1),
    COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.establecimiento;

-- ============================================
-- 6. PUNTOS DE EMISIÓN
-- ============================================
INSERT IGNORE INTO pto_emisiones (id, establecimiento_id, codigo, nombre,
    sec_factura, sec_nota_credito, sec_nota_debito,
    sec_guia, sec_retencion, sec_liquidacion,
    activo, created_at, updated_at)
SELECT id, establecimiento_id, codigo, nombre,
    CAST(secuencialFactura AS UNSIGNED),
    CAST(secuencialNotaCredito AS UNSIGNED),
    CAST(secuencialNotaDebito AS UNSIGNED),
    CAST(secuencialGuiaRemision AS UNSIGNED),
    CAST(secuencialRetencion AS UNSIGNED),
    CAST(secuencialLiquidacionCompra AS UNSIGNED),
    activo, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.ptoemision;

-- ============================================
-- 7. ROLES
-- ============================================
INSERT IGNORE INTO roles (id, nombre, descripcion, created_at, updated_at) VALUES
(1, 'ROLE_ADMIN', 'Administrador del sistema', NOW(), NOW()),
(2, 'ROLE_EMISOR_ADMIN', 'Administrador del emisor', NOW(), NOW()),
(3, 'ROLE_EMISOR', 'Usuario emisor', NOW(), NOW());

-- ============================================
-- 8. USUARIOS
-- NOTA: Passwords incompatibles (Symfony vs Laravel bcrypt).
-- Ejecutar post_migracion.php después para resetear passwords.
-- INSERT IGNORE para saltar usernames duplicados en BD vieja.
-- ============================================
INSERT IGNORE INTO users (id, emisor_id, rol_id, username, password,
    email, nombre, apellido, activo, created_at, updated_at)
SELECT id, emisor_id, rol_id, username, password,
    email, nombre, COALESCE(apellidos, ''),
    is_active, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.user
ORDER BY id ASC;

-- ============================================
-- 9. CLIENTES
-- INSERT IGNORE para saltar duplicados por emisor+identificacion.
-- ============================================
INSERT IGNORE INTO clientes (id, emisor_id, tipo_identificacion, identificacion,
    razon_social, direccion, telefono, email,
    activo, created_at, updated_at)
SELECT id, emisor_id, tipoIdentificacion, identificacion,
    nombre, direccion, celular, correoElectronico,
    1, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.cliente;

-- ============================================
-- 10. PRODUCTOS
-- INSERT IGNORE para saltar duplicados por emisor+codigo.
-- ============================================
INSERT IGNORE INTO productos (id, emisor_id, impuesto_iva_id,
    codigo_principal, codigo_auxiliar, nombre,
    precio_unitario, activo, created_at, updated_at)
SELECT id, emisor_id, impuesto_iva_id,
    codigoPrincipal, codigoAuxiliar, nombre,
    precioUnitario, 1, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.producto;

-- ============================================
-- 11. FACTURAS
-- ============================================
INSERT IGNORE INTO facturas (id, cliente_id, emisor_id, establecimiento_id,
    pto_emision_id, clave_acceso, numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, forma_pago,
    fecha_emision, total_sin_impuestos, total_descuento,
    total_iva, total_ice, total_irbpnr, propina, importe_total,
    observaciones, user_id, created_at, updated_at)
SELECT id, cliente_id, emisor_id, establecimiento_id,
    ptoEmision_id, claveAcceso, numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), formaPago,
    fechaEmision, totalSinImpuestos, totalDescuento,
    iva12, valorICE, valorIRBPNR, propina, valorTotal,
    observacion, createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.factura;

-- ============================================
-- 12. FACTURA DETALLES
-- ============================================
INSERT IGNORE INTO factura_detalles (id, factura_id,
    codigo_principal, descripcion, cantidad,
    precio_unitario, descuento, precio_total_sin_impuesto,
    created_at, updated_at)
SELECT id, factura_id,
    codigoProducto, nombre, cantidad,
    precioUnitario, descuento, valorTotal,
    NOW(), NOW()
FROM OLD_DB.facturahasproducto;

-- ============================================
-- 13. FACTURA REEMBOLSOS
-- ============================================
INSERT IGNORE INTO factura_reembolsos (id, factura_id,
    tipo_identificacion_proveedor, identificacion_proveedor,
    cod_doc_reembolso, estab_doc_reembolso,
    pto_emision_doc_reembolso, secuencial_doc_reembolso,
    fecha_emision_doc_reembolso, numero_autorizacion_doc_reembolso,
    base_imponible, impuesto_valor,
    created_at, updated_at)
SELECT id, factura_id,
    tipoIdentificacionProveedorReembolso, identificacionProveedorReembolso,
    codDocReembolso, estabDocReembolso,
    ptoEmiDocReembolso, secuencialDocReembolso,
    fechaEmisionDocReembolso, numeroautorizacionDocReemb,
    baseImponibleReembolso, impuestoReembolso,
    COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.facturareembolso;

-- ============================================
-- 14. NOTAS DE CRÉDITO
-- ============================================
INSERT IGNORE INTO nota_creditos (id, cliente_id, emisor_id, establecimiento_id,
    pto_emision_id, clave_acceso, numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, fecha_emision,
    cod_doc_modificado, num_doc_modificado, fecha_emision_doc_sustento, motivo,
    total_sin_impuestos, total_descuento, total_iva, total_ice,
    importe_total, user_id, created_at, updated_at)
SELECT id, cliente_id, emisor_id, establecimiento_id,
    ptoEmision_id, claveAcceso, numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), fechaEmision,
    tipoDocMod, nroDocMod, fechaEmisionDocMod, motivo,
    totalSinImpuestos, totalDescuento, iva12, valorICE,
    valorTotal, createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.notacredito;

-- ============================================
-- 15. NOTA CRÉDITO DETALLES
-- ============================================
INSERT IGNORE INTO nota_credito_detalles (id, nota_credito_id,
    codigo_principal, descripcion, cantidad,
    precio_unitario, descuento, precio_total_sin_impuesto,
    created_at, updated_at)
SELECT id, notaCredito_id,
    codigoProducto, nombre, cantidad,
    precioUnitario, descuento, valorTotal,
    NOW(), NOW()
FROM OLD_DB.notacreditohasproducto;

-- ============================================
-- 16. NOTAS DE DÉBITO
-- ============================================
INSERT IGNORE INTO nota_debitos (id, cliente_id, emisor_id, establecimiento_id,
    pto_emision_id, clave_acceso, numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, fecha_emision,
    cod_doc_modificado, num_doc_modificado, fecha_emision_doc_sustento,
    total_sin_impuestos, total_iva, importe_total,
    user_id, created_at, updated_at)
SELECT id, cliente_id, emisor_id, establecimiento_id,
    ptoEmision_id, claveAcceso, numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), fechaEmision,
    tipoDocMod, nroDocMod, fechaEmisionDocMod,
    totalSinImpuestos, iva12, valorTotal,
    createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.notadebito;

-- ============================================
-- 17. NOTA DÉBITO MOTIVOS
-- ============================================
INSERT IGNORE INTO nota_debito_motivos (id, nota_debito_id,
    razon, valor, created_at, updated_at)
SELECT id, dotaDebito_id,
    nombre, valor, NOW(), NOW()
FROM OLD_DB.motivo;

-- ============================================
-- 18. RETENCIONES
-- ============================================
INSERT IGNORE INTO retenciones (id, emisor_id, establecimiento_id,
    pto_emision_id, cliente_id, clave_acceso,
    numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, fecha_emision,
    periodo_fiscal, user_id, created_at, updated_at)
SELECT id, emisor_id, establecimiento_id,
    ptoEmision_id, cliente_id, claveAcceso,
    numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), fechaEmision,
    periodoFiscal, createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.retencion;

-- ============================================
-- 19. RETENCIÓN IMPUESTOS
-- ============================================
INSERT IGNORE INTO retencion_impuestos (id, retencion_id,
    codigo_impuesto, codigo_retencion, base_imponible,
    porcentaje_retener, valor_retenido,
    cod_doc_sustento, num_doc_sustento, fecha_emision_doc_sustento,
    created_at, updated_at)
SELECT id, retencion_id,
    codigo, codigoRetencion, baseImponible,
    porcentajeRetener, valorRetenido,
    codDocSustento, numDocSustento, fechaEmisionDocSustento,
    NOW(), NOW()
FROM OLD_DB.impuestocomprobanteretencion;

-- ============================================
-- 20. RETENCIÓN ATS
-- ============================================
INSERT IGNORE INTO retencion_ats (id, emisor_id, cliente_id,
    periodo_fiscal, estado,
    user_id, created_at, updated_at)
SELECT id, emisor_id, cliente_id,
    periodoFiscal, estado,
    createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.retencionats;

-- ============================================
-- 21. DOC SUSTENTO RETENCIONES (ATS)
-- ============================================
INSERT IGNORE INTO doc_sustento_retenciones (id, retencion_ats_id,
    cod_doc_sustento, num_doc_sustento, fecha_emision_doc_sustento,
    num_aut_doc_sustento, total_sin_impuestos, total_iva, importe_total,
    created_at, updated_at)
SELECT id, retencion_ats_id,
    codDocSustento, numDocSustento, fechaEmisionDocSustento,
    numAutDocSustento, totalSinImpuestos,
    0, importeTotal,
    COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.docsustentoretencionats;

-- ============================================
-- 22. DESGLOCE RETENCIONES (ATS)
-- ============================================
INSERT IGNORE INTO desgloce_retenciones (id, doc_sustento_retencion_id,
    codigo_impuesto, codigo_retencion, base_imponible,
    porcentaje_retener, valor_retenido,
    created_at, updated_at)
SELECT id, doc_sustento_retencion_ats_id,
    codigo, codigoRetencion, baseImponible,
    porcentajeRetener, valorImpuesto,
    COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.desgloceretencionats;

-- ============================================
-- 23. GUÍAS DE REMISIÓN
-- ============================================
INSERT IGNORE INTO guias (id, emisor_id, establecimiento_id,
    pto_emision_id, clave_acceso, numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, fecha_emision,
    dir_partida, razon_social_transportista, ruc_transportista,
    fecha_ini_transporte, fecha_fin_transporte, placa,
    observaciones, user_id, created_at, updated_at)
SELECT id, emisor_id, establecimiento_id,
    ptoEmision_id, claveAcceso, numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), NOW(),
    dirPartida, razonSocialTransportista, rucTransportista,
    fechaIniTransporte, fechaFinTransporte, placa,
    observacion, createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.guia;

-- ============================================
-- 24. GUÍA DETALLES
-- ============================================
INSERT IGNORE INTO guia_detalles (id, guia_id,
    identificacion_destinatario, razon_social_destinatario,
    motivo_traslado, codigo_principal, descripcion, cantidad,
    created_at, updated_at)
SELECT ghp.id, ghp.guia_id,
    COALESCE(c.identificacion, ''), COALESCE(c.nombre, ''),
    COALESCE(g.motivoTraslado, ''), ghp.codigoProducto, ghp.nombre, ghp.cantidad,
    NOW(), NOW()
FROM OLD_DB.guiahasproducto ghp
JOIN OLD_DB.guia g ON g.id = ghp.guia_id
LEFT JOIN OLD_DB.cliente c ON c.id = g.cliente_id;

-- ============================================
-- 25. LIQUIDACIONES DE COMPRA
-- ============================================
INSERT IGNORE INTO liquidacion_compras (id, cliente_id, emisor_id, establecimiento_id,
    pto_emision_id, clave_acceso, numero_autorizacion, fecha_autorizacion,
    estado, ambiente, secuencial, forma_pago, fecha_emision,
    total_sin_impuestos, total_descuento, total_iva,
    total_ice, importe_total,
    observaciones, user_id, created_at, updated_at)
SELECT id, cliente_id, emisor_id, establecimiento_id,
    ptoEmision_id, claveAcceso, numeroAutorizacion, fechaAutorizacion,
    estado, ambiente, CAST(secuencial AS UNSIGNED), formaPago, fechaEmision,
    totalSinImpuestos, totalDescuento, iva12,
    valorICE, valorTotal,
    observacion, createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.liquidacioncompra;

-- ============================================
-- 26. LIQUIDACIÓN DETALLES
-- ============================================
INSERT IGNORE INTO liquidacion_detalles (id, liquidacion_compra_id,
    codigo_principal, descripcion, cantidad,
    precio_unitario, descuento, precio_total_sin_impuesto,
    created_at, updated_at)
SELECT id, liquidacioncompra_id,
    codigoProducto, nombre, cantidad,
    precioUnitario, descuento, valorTotal,
    NOW(), NOW()
FROM OLD_DB.liquidacioncomprahasproducto;

-- ============================================
-- 27. PROFORMAS (muchas fallarán por falta de pto_emision_id, es normal)
-- ============================================
INSERT IGNORE INTO proformas (id, cliente_id, emisor_id, establecimiento_id,
    secuencial, fecha_emision,
    total_sin_impuestos, total_descuento, total_iva,
    importe_total, observaciones, estado,
    user_id, created_at, updated_at)
SELECT id, cliente_id, emisor_id, establecimiento_id,
    numero, fechaEmision,
    totalSinImpuestos, totalDescuento, iva12,
    valorTotal, observacion, COALESCE(estado, 'CREADA'),
    createdBy_id, COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.proforma;

-- ============================================
-- 28. PROFORMA DETALLES
-- ============================================
INSERT IGNORE INTO proforma_detalles (id, proforma_id,
    codigo_principal, descripcion, cantidad,
    precio_unitario, descuento, precio_total_sin_impuesto,
    created_at, updated_at)
SELECT id, proforma_id,
    codigoProducto, nombre, cantidad,
    precioUnitario, descuento, valorTotal,
    NOW(), NOW()
FROM OLD_DB.proformahasproducto;

-- ============================================
-- 29. COMPRAS
-- ============================================
INSERT IGNORE INTO compras (id, emisor_id, establecimiento_id,
    ruc_proveedor, razon_social_proveedor,
    tipo_comprobante, numero_comprobante, autorizacion,
    clave_acceso, fecha_emision,
    total_sin_impuestos, total_iva, importe_total,
    estado, created_at, updated_at)
SELECT id, emisor_id, establecimiento_id,
    identificacionProveedor, razonSocialProveedor,
    '01', numeroFactura, numeroAutorizacion,
    claveAcceso, fechaEmision,
    totalSinImpuestos, iva12, valorTotal,
    'CREADA', COALESCE(createdAt, NOW()), COALESCE(updatedAt, NOW())
FROM OLD_DB.compra;

-- ============================================
-- 30. COMPRA DETALLES
-- ============================================
INSERT IGNORE INTO compra_detalles (id, compra_id,
    codigo_principal, descripcion, cantidad,
    precio_unitario, subtotal, iva, total,
    created_at, updated_at)
SELECT id, compra_id,
    codigoProducto, nombre, cantidad,
    precioUnitario, subTotal, iva12, subTotal + iva12,
    NOW(), NOW()
FROM OLD_DB.detallecompra;

-- ============================================
-- 31. IMPUESTOS (polimórficos)
-- Viejo: FK directas → Nuevo: morphs (detalle_type, detalle_id)
-- ============================================
INSERT IGNORE INTO impuestos (codigo, codigo_porcentaje, tarifa,
    base_imponible, valor, detalle_id, detalle_type, created_at, updated_at)
SELECT i.codigo, i.codigoPorcentaje, COALESCE(i.tarifa, 0),
    i.baseImponible, i.valor, i.facturaHasProducto_id,
    'App\\Models\\FacturaDetalle', NOW(), NOW()
FROM OLD_DB.impuesto i WHERE i.facturaHasProducto_id IS NOT NULL;

INSERT IGNORE INTO impuestos (codigo, codigo_porcentaje, tarifa,
    base_imponible, valor, detalle_id, detalle_type, created_at, updated_at)
SELECT i.codigo, i.codigoPorcentaje, COALESCE(i.tarifa, 0),
    i.baseImponible, i.valor, i.notaCreditoHasProducto_id,
    'App\\Models\\NotaCreditoDetalle', NOW(), NOW()
FROM OLD_DB.impuesto i WHERE i.notaCreditoHasProducto_id IS NOT NULL;

INSERT IGNORE INTO impuestos (codigo, codigo_porcentaje, tarifa,
    base_imponible, valor, detalle_id, detalle_type, created_at, updated_at)
SELECT i.codigo, i.codigoPorcentaje, COALESCE(i.tarifa, 0),
    i.baseImponible, i.valor, i.liquidacionHasProducto_id,
    'App\\Models\\LiquidacionDetalle', NOW(), NOW()
FROM OLD_DB.impuesto i WHERE i.liquidacionHasProducto_id IS NOT NULL;

INSERT IGNORE INTO impuestos (codigo, codigo_porcentaje, tarifa,
    base_imponible, valor, detalle_id, detalle_type, created_at, updated_at)
SELECT i.codigo, i.codigoPorcentaje, COALESCE(i.tarifa, 0),
    i.baseImponible, i.valor, i.proformaHasProducto_id,
    'App\\Models\\ProformaDetalle', NOW(), NOW()
FROM OLD_DB.impuesto i WHERE i.proformaHasProducto_id IS NOT NULL;

-- ============================================
-- 32. CAMPOS ADICIONALES (polimórficos)
-- ============================================
INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, Factura_id, 'App\\Models\\Factura', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE Factura_id IS NOT NULL;

INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, NotaCredito_id, 'App\\Models\\NotaCredito', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE NotaCredito_id IS NOT NULL;

INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, NotaDebito_id, 'App\\Models\\NotaDebito', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE NotaDebito_id IS NOT NULL;

INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, Retencion_id, 'App\\Models\\Retencion', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE Retencion_id IS NOT NULL;

INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, Guia_id, 'App\\Models\\Guia', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE Guia_id IS NOT NULL;

INSERT IGNORE INTO campos_adicionales (nombre, valor, comprobante_id, comprobante_type, created_at, updated_at)
SELECT nombre, valor, liquidacioncompra_id, 'App\\Models\\LiquidacionCompra', NOW(), NOW()
FROM OLD_DB.campoadicional WHERE liquidacioncompra_id IS NOT NULL;

-- ============================================
-- 33. MENSAJES SRI (polimórficos)
-- ============================================
INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    Factura_id, 'App\\Models\\Factura', NOW(), NOW()
FROM OLD_DB.mensaje WHERE Factura_id IS NOT NULL;

INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    NotaCredito_id, 'App\\Models\\NotaCredito', NOW(), NOW()
FROM OLD_DB.mensaje WHERE NotaCredito_id IS NOT NULL;

INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    NotaDebito_id, 'App\\Models\\NotaDebito', NOW(), NOW()
FROM OLD_DB.mensaje WHERE NotaDebito_id IS NOT NULL;

INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    Retencion_id, 'App\\Models\\Retencion', NOW(), NOW()
FROM OLD_DB.mensaje WHERE Retencion_id IS NOT NULL;

INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    Guia_id, 'App\\Models\\Guia', NOW(), NOW()
FROM OLD_DB.mensaje WHERE Guia_id IS NOT NULL;

INSERT IGNORE INTO mensajes (identificador, mensaje, informacion_adicional, tipo,
    comprobante_id, comprobante_type, created_at, updated_at)
SELECT identificador, mensaje, informacionAdicional, tipo,
    liquidacioncompra_id, 'App\\Models\\LiquidacionCompra', NOW(), NOW()
FROM OLD_DB.mensaje WHERE liquidacioncompra_id IS NOT NULL;

-- ============================================
-- 34. ACTUALIZAR AUTO_INCREMENT
-- ============================================
SET @max_id = 0;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM emisores;
SET @sql = CONCAT('ALTER TABLE emisores AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM establecimientos;
SET @sql = CONCAT('ALTER TABLE establecimientos AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM pto_emisiones;
SET @sql = CONCAT('ALTER TABLE pto_emisiones AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM users;
SET @sql = CONCAT('ALTER TABLE users AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM clientes;
SET @sql = CONCAT('ALTER TABLE clientes AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM productos;
SET @sql = CONCAT('ALTER TABLE productos AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM facturas;
SET @sql = CONCAT('ALTER TABLE facturas AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM factura_detalles;
SET @sql = CONCAT('ALTER TABLE factura_detalles AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM nota_creditos;
SET @sql = CONCAT('ALTER TABLE nota_creditos AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM nota_debitos;
SET @sql = CONCAT('ALTER TABLE nota_debitos AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM retenciones;
SET @sql = CONCAT('ALTER TABLE retenciones AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM guias;
SET @sql = CONCAT('ALTER TABLE guias AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM liquidacion_compras;
SET @sql = CONCAT('ALTER TABLE liquidacion_compras AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM proformas;
SET @sql = CONCAT('ALTER TABLE proformas AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COALESCE(MAX(id), 0) + 1 INTO @max_id FROM planes;
SET @sql = CONCAT('ALTER TABLE planes AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 35. VERIFICACIÓN DE CONTEO
-- ============================================
SELECT 'emisores' AS tabla, COUNT(*) AS nuevos FROM emisores
UNION ALL SELECT 'emisor (old)', COUNT(*) FROM OLD_DB.emisor
UNION ALL SELECT 'clientes', COUNT(*) FROM clientes
UNION ALL SELECT 'cliente (old)', COUNT(*) FROM OLD_DB.cliente
UNION ALL SELECT 'productos', COUNT(*) FROM productos
UNION ALL SELECT 'producto (old)', COUNT(*) FROM OLD_DB.producto
UNION ALL SELECT 'facturas', COUNT(*) FROM facturas
UNION ALL SELECT 'factura (old)', COUNT(*) FROM OLD_DB.factura
UNION ALL SELECT 'nota_creditos', COUNT(*) FROM nota_creditos
UNION ALL SELECT 'notacredito (old)', COUNT(*) FROM OLD_DB.notacredito
UNION ALL SELECT 'nota_debitos', COUNT(*) FROM nota_debitos
UNION ALL SELECT 'notadebito (old)', COUNT(*) FROM OLD_DB.notadebito
UNION ALL SELECT 'retenciones', COUNT(*) FROM retenciones
UNION ALL SELECT 'retencion (old)', COUNT(*) FROM OLD_DB.retencion
UNION ALL SELECT 'guias', COUNT(*) FROM guias
UNION ALL SELECT 'guia (old)', COUNT(*) FROM OLD_DB.guia
UNION ALL SELECT 'liquidacion_compras', COUNT(*) FROM liquidacion_compras
UNION ALL SELECT 'liquidacioncompra (old)', COUNT(*) FROM OLD_DB.liquidacioncompra
UNION ALL SELECT 'proformas', COUNT(*) FROM proformas
UNION ALL SELECT 'proforma (old)', COUNT(*) FROM OLD_DB.proforma
UNION ALL SELECT 'impuestos (morph)', COUNT(*) FROM impuestos
UNION ALL SELECT 'impuesto (old)', COUNT(*) FROM OLD_DB.impuesto;
