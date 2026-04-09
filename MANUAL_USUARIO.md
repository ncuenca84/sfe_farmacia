# Manual de Usuario - Sistema de Facturación Electrónica (SFE)

---

## Tabla de Contenidos

1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Panel de Administrador](#2-panel-de-administrador)
3. [Panel del Emisor (Cliente)](#3-panel-del-emisor-cliente)
4. [Preguntas Frecuentes](#4-preguntas-frecuentes)

---

## 1. Acceso al Sistema

### 1.1 Iniciar Sesión

Para ingresar al sistema, abra su navegador y acceda a la dirección proporcionada por su proveedor.

Ingrese su **usuario** y **contraseña**, luego presione el botón **"Iniciar Sesión"**.

[CAPTURA: Pantalla de login del sistema]

> **Nota:** Si olvidó su contraseña, presione "¿Olvidaste tu contraseña?" y siga las instrucciones.

### 1.2 Tipos de Usuario

El sistema tiene tres tipos de usuario:

| Tipo | Acceso | Descripción |
|------|--------|-------------|
| **Administrador** | Panel Admin + Panel Emisor | Control total del sistema |
| **Emisor Admin** | Panel Emisor (completo) | Administra su empresa: usuarios, config, comprobantes |
| **Emisor** | Panel Emisor (limitado) | Solo emite comprobantes y consulta reportes |

---

## 2. Panel de Administrador

El panel de administración permite gestionar todos los emisores (clientes), planes, impuestos y configuraciones del sistema.

### 2.1 Dashboard (Pantalla Principal)

Al ingresar como administrador, vera un resumen general con:

- **Emisores Activos**: Cantidad de clientes activos
- **Emisores Inactivos**: Clientes suspendidos o deshabilitados
- **Suscripciones Vencidas**: Clientes cuyo plan ha expirado
- **Comprobantes del Mes**: Total de comprobantes emitidos en el mes

Debajo aparece una tabla con las **suscripciones proximas a vencer**, indicando los dias restantes con colores de alerta.

[CAPTURA: Dashboard del panel admin]

---

### 2.2 Emisores

Esta seccion permite administrar los clientes (emisores) del sistema.

**¿Como llegar?** Menu lateral → **Emisores**

[CAPTURA: Listado de emisores]

#### Ver listado de emisores

Se muestra una tabla con:
- RUC del emisor
- Razon Social
- Plan contratado
- Estado de la suscripcion (Activa / Sin suscripcion)
- Estado (Activo / Inactivo)
- Botones de accion

#### Crear un nuevo emisor

1. Presione el boton **"Nuevo Emisor"** (esquina superior derecha)
2. Complete los datos requeridos:
   - **RUC** (13 digitos)
   - **Razon Social**
   - **Nombre Comercial** (opcional)
   - **Direccion Matriz**
3. Configure los datos de facturacion:
   - Obligado a llevar contabilidad (Si/No)
   - Contribuyente especial (si aplica)
   - Regimen (General, RIMPE, etc.)
   - Ambiente (1-Pruebas / 2-Produccion)
4. Configure el **correo SMTP** para envio de comprobantes:
   - Host SMTP (ej: smtp.gmail.com)
   - Puerto (ej: 465)
   - Usuario y contraseña
   - Encriptacion (SSL/TLS)
   - Email y nombre del remitente
5. Presione **"Guardar"**

[CAPTURA: Formulario de crear emisor]

#### Editar un emisor

1. En el listado, presione **"Editar"** junto al emisor
2. Modifique los datos necesarios
3. Presione **"Guardar"**

#### Acceder al panel de un emisor (Modo Soporte)

Esta funcion permite ver el panel de facturacion de cualquier emisor sin necesitar su contraseña. Es util para dar soporte tecnico.

1. En el listado, presione el boton verde **"Acceder"**
2. Se abrira el panel de facturacion del emisor
3. Aparecera una **barra amarilla** en la parte superior indicando "Modo soporte"
4. Para regresar al panel admin, presione **"Volver al Admin"**

[CAPTURA: Barra amarilla de modo soporte]

> **Importante:** En modo soporte usted ve TODOS los datos del emisor, sin restricciones de linea de negocio.

#### Activar / Desactivar un emisor

- **Desactivar**: El emisor no podra ingresar al sistema ni emitir comprobantes
- **Activar**: Restaura el acceso del emisor

#### Eliminar un emisor

> **ADVERTENCIA: Esta accion es IRREVERSIBLE**

1. Presione **"Eliminar"**
2. Lea la advertencia: se eliminaran TODOS los datos (facturas, clientes, productos, etc.)
3. Escriba el RUC del emisor para confirmar
4. Presione **"Eliminar Permanentemente"**

---

### 2.3 Establecimientos

Permite administrar los establecimientos (sucursales) de todos los emisores.

**¿Como llegar?** Menu lateral → **Establecimientos**

[CAPTURA: Listado de establecimientos]

#### Crear un establecimiento

1. Presione **"Nuevo Establecimiento"**
2. Seleccione el **Emisor** al que pertenece
3. Complete:
   - **Codigo** (ej: 001)
   - **Nombre** (ej: Matriz, Sucursal Norte)
   - **Direccion**
4. Presione **"Guardar"**

#### Editar un establecimiento

1. Presione **"Editar"** junto al establecimiento
2. Modifique los datos
3. Presione **"Guardar"**

---

### 2.4 Puntos de Emision

Cada establecimiento puede tener uno o mas puntos de emision (cajas).

**¿Como llegar?** Menu lateral → **Puntos de Emision**

[CAPTURA: Listado de puntos de emision]

#### Crear un punto de emision

1. Presione **"Nuevo Punto de Emision"**
2. Seleccione el **Emisor** y el **Establecimiento**
3. Ingrese el **Codigo** (ej: 001)
4. Presione **"Guardar"**

#### Editar secuenciales

Al editar un punto de emision, puede ver y modificar los **secuenciales** de cada tipo de comprobante:

- Secuencial de Facturas
- Secuencial de Notas de Credito
- Secuencial de Notas de Debito
- Secuencial de Retenciones
- Secuencial de Guias de Remision
- Secuencial de Liquidaciones de Compra

> **Precaucion:** Solo modifique los secuenciales si es absolutamente necesario. Un secuencial incorrecto puede causar rechazos del SRI.

---

### 2.5 Planes

Gestiona los planes de suscripcion disponibles para los emisores.

**¿Como llegar?** Menu lateral → **Planes**

#### Crear un plan

1. Presione **"Nuevo Plan"**
2. Defina:
   - Nombre del plan
   - Limite de comprobantes
   - Precio
   - Duracion
3. Presione **"Guardar"**

---

### 2.6 Usuarios

Administra todos los usuarios del sistema.

**¿Como llegar?** Menu lateral → **Usuarios**

[CAPTURA: Listado de usuarios con filtros]

#### Filtros disponibles

- **Buscar**: Por nombre o usuario
- **Rol**: Administrador, Emisor Admin, Emisor
- **Emisor**: Filtrar por empresa
- **Estado**: Activo / Inactivo

#### Crear un usuario

1. Presione **"Nuevo Usuario"**
2. Complete:
   - **Usuario** (para iniciar sesion)
   - **Nombre y Apellido**
   - **Email**
   - **Contraseña**
   - **Rol** (Administrador, Emisor Admin, Emisor)
   - **Emisor** (a que empresa pertenece, excepto para administradores)
3. Presione **"Guardar"**

#### Editar un usuario

1. Presione **"Editar"**
2. Modifique los datos. La contraseña es opcional (dejar vacio para mantener la actual)
3. Presione **"Guardar"**

> **Nota:** No puede eliminar su propio usuario.

---

### 2.7 Impuestos

Permite configurar los catalogos de impuestos del SRI.

**¿Como llegar?** Menu lateral → seccion **Catalogos** → **IVA**, **ICE**, **IRBPNR** o **Retenciones**

[CAPTURA: Listado de impuestos IVA]

#### Tarifas de IVA vigentes

| Codigo SRI | Tarifa | Estado |
|------------|--------|--------|
| 0 | 0% | Activo |
| 4 | 15% | Activo (tarifa general) |
| 5 | 5% | Activo (diferenciado) |
| 6 | No objeto de impuesto | Activo |
| 7 | Exento de IVA | Activo |
| 8 | 8% (turismo en feriados) | **Inactivo por defecto** |
| 10 | 13% | Activo |

> **Sobre el IVA 8%:** Solo aplica durante feriados nacionales decretados por el presidente para el sector turismo. Debe **activarlo manualmente** desde esta seccion los dias de feriado y **desactivarlo** cuando termine.

#### Activar/Desactivar un impuesto

1. Ubique el impuesto en la lista
2. Cambie el estado de **Activo** a **Inactivo** o viceversa
3. Guarde los cambios

Solo los impuestos activos apareceran en los formularios de comprobantes.

---

### 2.8 CRM

Herramientas de gestion de clientes integradas.

**¿Como llegar?** Menu lateral → seccion **CRM**

- **Panel CRM**: Vista general de emisores y su estado
- **Suscripciones**: Administrar suscripciones de planes
- **Firmas Electronicas**: Gestionar certificados digitales P12
- **Notificaciones**: Crear y enviar notificaciones a emisores

---

### 2.9 Configuracion del Sitio

Personalice el nombre, logo y correo del sistema.

**¿Como llegar?** Menu lateral → **Config. Sitio**

[CAPTURA: Pagina de configuracion del sitio]

#### Nombre del sistema

1. Escriba el nombre deseado (ej: "Mi Facturacion")
2. Presione **"Guardar"**
3. El nombre aparecera en el login, sidebar y titulo de la pagina

> Si deja el campo vacio, se usara el nombre por defecto del sistema.

#### Logo

1. Presione **"Seleccionar archivo"** y elija una imagen (max 2MB)
2. Presione **"Subir Logo"**
3. El logo aparecera en la pantalla de login y en el sidebar
4. Para eliminarlo, presione **"Eliminar Logo"**

#### Correo SMTP del sistema

Configure el correo para notificaciones generales del sistema (no para comprobantes).

1. Complete: Host, Puerto, Usuario, Contraseña, Encriptacion, Email remitente
2. Presione **"Guardar"**
3. Use **"Enviar correo de prueba"** para verificar que funcione

> **Nota:** Este correo es para notificaciones del sistema. Cada emisor configura su propio correo SMTP para el envio de comprobantes.

---

## 3. Panel del Emisor (Cliente)

Este es el panel que usan los clientes (emisores) para emitir sus comprobantes electronicos.

### 3.1 Dashboard (Pantalla Principal)

Muestra un resumen de la actividad del mes:

- **Ventas del Mes**: Monto total facturado
- **Facturas**: Cantidad emitida
- **Notas de Credito/Debito**: Cantidades
- **Retenciones**: Cantidad emitida
- **Guias de Remision**: Cantidad emitida

Incluye graficos de:
- Tendencia de ventas (ultimos 6 meses)
- Distribucion de comprobantes por tipo
- Facturas emitidas por mes
- Estado de facturas del mes

[CAPTURA: Dashboard del emisor]

---

### 3.2 Facturas

**¿Como llegar?** Menu lateral → **Facturas**

[CAPTURA: Listado de facturas]

#### Crear una factura

1. Presione **"Nueva Factura"**
2. Seleccione **Establecimiento** y **Punto de Emision**
3. Busque o cree un **Cliente**:
   - Presione "Buscar Cliente" para buscar uno existente
   - Presione "+ Nuevo Cliente" para crear uno nuevo
4. Agregue productos:
   - Presione **"Buscar Producto"** para buscar en el catalogo
   - Presione **"+ Nuevo Producto"** para crear uno nuevo
   - Para cada producto ingrese: cantidad, precio, descuento ($ o %), tipo de IVA
5. Verifique los **totales** (subtotal, descuento, IVA, total)
6. Agregue formas de pago
7. Presione **"Guardar"**

[CAPTURA: Formulario de nueva factura]

#### Procesar una factura (enviar al SRI)

1. En el listado o detalle de la factura, presione **"Procesar"**
2. El sistema:
   - Genera el XML
   - Firma electronicamente
   - Envia al SRI
   - Recibe la autorizacion
3. Si es exitoso, el estado cambiara a **"AUTORIZADO"**
4. Si hay error, se mostrara el motivo del rechazo

#### Descargar PDF

- Presione el boton **"PDF"** para descargar el comprobante en formato A4
- Presione **"POS"** para formato ticket (80mm)

#### Descargar XML

- Presione el boton **"XML"** para descargar el archivo XML autorizado

#### Enviar por email

1. Presione el boton **"Email"**
2. Ingrese las direcciones de correo separadas por coma
3. Presione **"Enviar"**

El destinatario recibira un correo con:
- Datos del comprobante (tipo, numero, valor total)
- PDF adjunto
- XML adjunto

#### Anular una factura

1. Presione **"Anular"**
2. Confirme la anulacion
3. El estado cambiara a **"ANULADA"**

> **Nota:** Anular en el sistema NO anula ante el SRI. Para eso debe emitir una Nota de Credito.

#### Clonar una factura

1. Presione **"Clonar"**
2. Se creara una nueva factura con los mismos datos
3. Modifique lo necesario y guarde

---

### 3.3 Notas de Credito

Sirven para anular total o parcialmente una factura ante el SRI.

**¿Como llegar?** Menu lateral → **Notas de Credito**

#### Crear una nota de credito

1. Presione **"Nueva Nota de Credito"**
2. Seleccione el **comprobante a modificar** (factura original)
3. Indique el motivo
4. Agregue los productos que se devuelven o ajustan
5. Presione **"Guardar"** y luego **"Procesar"**

---

### 3.4 Notas de Debito

Sirven para cobrar valores adicionales sobre una factura ya emitida.

**¿Como llegar?** Menu lateral → **Notas de Debito**

#### Crear una nota de debito

1. Presione **"Nueva Nota de Debito"**
2. Seleccione el comprobante que se modifica
3. Agregue los motivos con sus valores
4. Seleccione el tipo de IVA para cada motivo
5. Presione **"Guardar"** y luego **"Procesar"**

---

### 3.5 Retenciones

Comprobantes de retencion de impuestos (IR e IVA).

**¿Como llegar?** Menu lateral → **Retenciones** o **Retenciones ATS 2.0**

> **Retenciones ATS 2.0** es el formato mas reciente del SRI. Se recomienda usar este formato.

---

### 3.6 Liquidaciones de Compra

Para compras a personas que no emiten factura.

**¿Como llegar?** Menu lateral → **Liquidaciones**

---

### 3.7 Guias de Remision

Para el traslado de mercaderia.

**¿Como llegar?** Menu lateral → **Guias de Remision**

---

### 3.8 Proformas

Cotizaciones que no se envian al SRI. Utiles para presentar presupuestos a clientes.

**¿Como llegar?** Menu lateral → **Proformas**

---

### 3.9 Reportes

**¿Como llegar?** Menu lateral → seccion **Reportes**

| Reporte | Descripcion |
|---------|-------------|
| **Comprobantes** | Listado general de todos los comprobantes con filtros |
| **Ventas** | Resumen de ventas por periodo |
| **Ventas Detalladas** | Desglose producto por producto |
| **Retenciones Totalizadas** | Resumen de retenciones agrupadas |
| **Retenciones x Factura** | Retenciones vinculadas a cada factura |
| **Carga Masiva** | Importar comprobantes desde archivos |

[CAPTURA: Pantalla de reportes]

---

### 3.10 Configuracion del Emisor

Solo disponible para usuarios con rol **Emisor Admin** o superior.

**¿Como llegar?** Menu lateral → seccion **Configuracion**

#### Datos del Emisor

1. Vaya a **Configuracion** → **Datos Emisor**
2. Puede modificar:
   - Razon Social y Nombre Comercial
   - Direccion
   - Obligado a llevar contabilidad
   - Contribuyente especial
   - Regimen (General, RIMPE, etc.)
   - Ambiente (Pruebas / Produccion)
3. Suba su **Logo** y **Certificado Digital (.p12)**
4. Configure su **correo SMTP** para envio de comprobantes

[CAPTURA: Configuracion del emisor]

> **Sobre el certificado digital:** El archivo .p12 es proporcionado por el SRI o por entidades certificadoras. Es necesario para firmar electronicamente los comprobantes.

#### Correo SMTP del emisor

Esta configuracion se usa para enviar los comprobantes por email a sus clientes.

1. En **Datos Emisor**, busque la seccion **"Configuracion de Email"**
2. Complete:
   - **Host SMTP**: Servidor de correo (ej: smtp.gmail.com)
   - **Puerto**: Generalmente 465 (SSL) o 587 (TLS)
   - **Usuario**: Su direccion de correo
   - **Contraseña**: Contraseña del correo o contraseña de aplicacion
   - **Encriptacion**: SSL o TLS
   - **Email remitente**: La direccion que aparecera como remitente
   - **Nombre remitente**: El nombre que aparecera (ej: su empresa)
3. Presione **"Guardar"**

> **Para Gmail:** Debe generar una "Contraseña de aplicacion" en la configuracion de seguridad de su cuenta Google. La contraseña normal no funciona.

#### Establecimientos y Puntos de Emision

- Vaya a **Configuracion** → **Establecimientos** para ver/editar sus sucursales
- Vaya a **Configuracion** → **Puntos de Emision** para ver/editar sus cajas

#### Clientes

Administre su catalogo de clientes.

1. Vaya a **Configuracion** → **Clientes**
2. Puede crear, editar o buscar clientes
3. Datos requeridos: Identificacion (RUC/Cedula), Razon Social, Email, Direccion

#### Productos

Administre su catalogo de productos.

1. Vaya a **Configuracion** → **Productos**
2. Puede crear, editar o buscar productos
3. Datos requeridos: Codigo, Nombre, Precio Unitario, Tipo de IVA

#### Usuarios de mi empresa

1. Vaya a **Configuracion** → **Usuarios**
2. Cree usuarios para sus empleados
3. Asigne el rol adecuado:
   - **Emisor Admin**: Acceso completo (configuracion + emision)
   - **Emisor**: Solo emision de comprobantes

---

### 3.11 Ambiente de Pruebas

Si su cuenta esta en **ambiente de pruebas**, los comprobantes no tienen validez tributaria. Se usa para probar el sistema antes de pasar a produccion.

En la parte inferior del menu lateral vera una etiqueta indicando el ambiente:
- **PRUEBAS**: Los comprobantes son de prueba
- **PRODUCCION**: Los comprobantes tienen validez legal

En ambiente de pruebas, tiene la opcion de **eliminar todos los comprobantes de prueba** desde Configuracion → Datos Emisor (seccion inferior).

> **Precaucion:** Los comprobantes anulados NO se eliminan con esta funcion.

---

## 4. Preguntas Frecuentes

### ¿Por que mi comprobante fue rechazado por el SRI?

El motivo del rechazo se muestra en el detalle del comprobante. Los errores mas comunes son:
- **Clave de acceso duplicada**: Ya existe un comprobante con esa clave
- **RUC no valido**: El RUC del cliente es incorrecto
- **Certificado vencido**: Su firma digital ha expirado
- **Secuencial duplicado**: Ya se emitio un comprobante con ese numero

### ¿Como cambio de ambiente de Pruebas a Produccion?

1. Vaya a **Configuracion** → **Datos Emisor**
2. Cambie el campo **Ambiente** de "1 - Pruebas" a "2 - Produccion"
3. Guarde los cambios

> **Importante:** Antes de cambiar a produccion, asegurese de tener su certificado digital .p12 cargado y vigente.

### ¿Como configuro mi correo para enviar facturas?

Vea la seccion [3.10 - Correo SMTP del emisor](#correo-smtp-del-emisor).

### ¿Que pasa si anulo una factura en el sistema?

Anular en el sistema cambia el estado interno a "ANULADA", pero **no notifica al SRI**. Para anular formalmente debe emitir una **Nota de Credito** por el valor total de la factura.

### ¿Como uso el IVA del 8% en feriados?

El IVA del 8% solo esta disponible durante feriados nacionales decretados por el presidente para el sector turismo. Su administrador debe:
1. **Activar** el IVA 8% desde el panel de impuestos antes del feriado
2. **Desactivar** el IVA 8% cuando termine el feriado

### ¿Puedo reenviar un comprobante por email?

Si. En el detalle de cualquier comprobante autorizado:
1. Presione el boton **"Email"**
2. Ingrese las direcciones de correo
3. Se enviara el PDF y XML como adjuntos

### ¿Como subo mi certificado digital?

1. Vaya a **Configuracion** → **Datos Emisor**
2. En la seccion **Archivos**, seleccione su archivo .p12
3. Ingrese la **clave del certificado**
4. Presione **"Guardar"**

### ¿Que hago si mi certificado vence?

1. Obtenga un nuevo certificado del SRI, Banco Central o entidad certificadora
2. Suba el nuevo archivo .p12 en Configuracion → Datos Emisor
3. Ingrese la nueva clave

---

> **Soporte tecnico:** Si necesita ayuda adicional, contacte a su administrador del sistema.

---

*Manual version 1.0 - Marzo 2026*
