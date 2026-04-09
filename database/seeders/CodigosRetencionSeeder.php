<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodigosRetencionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRetencionesIva();
        $this->seedRetencionesRenta();
    }

    private function seedRetencionesIva(): void
    {
        $codigos = [
            ['codigo' => '9',  'descripcion' => 'Retención de IVA 10%', 'porcentaje' => 10.00],
            ['codigo' => '10', 'descripcion' => 'Retención de IVA 20%', 'porcentaje' => 20.00],
            ['codigo' => '1',  'descripcion' => 'Retención de IVA 30%', 'porcentaje' => 30.00],
            ['codigo' => '11', 'descripcion' => 'Retención de IVA 50%', 'porcentaje' => 50.00],
            ['codigo' => '2',  'descripcion' => 'Retención de IVA 70%', 'porcentaje' => 70.00],
            ['codigo' => '3',  'descripcion' => 'Retención de IVA 100%', 'porcentaje' => 100.00],
        ];

        foreach ($codigos as $c) {
            DB::table('codigos_retencion')->updateOrInsert(
                ['tipo' => 'IVA', 'codigo' => $c['codigo']],
                array_merge($c, ['tipo' => 'IVA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    private function seedRetencionesRenta(): void
    {
        $codigos = [
            ['codigo' => '303', 'descripcion' => 'Honorarios profesionales y demás pagos por servicios relacionados con el título profesional', 'porcentaje' => 10.00],
            ['codigo' => '304', 'descripcion' => 'Servicios predomina el intelecto no relacionados con el título profesional', 'porcentaje' => 10.00],
            ['codigo' => '304A', 'descripcion' => 'Comisiones y demás pagos por servicios predomina intelecto no relacionados con el título profesional', 'porcentaje' => 10.00],
            ['codigo' => '304B', 'descripcion' => 'Pagos a notarios y registradores de la propiedad y mercantil por sus actividades ejercidas como tales', 'porcentaje' => 10.00],
            ['codigo' => '304C', 'descripcion' => 'Pagos a deportistas, entrenadores, árbitros, miembros del cuerpo técnico por sus actividades ejercidas como tales', 'porcentaje' => 10.00],
            ['codigo' => '304D', 'descripcion' => 'Pagos a artistas por sus actividades ejercidas como tales', 'porcentaje' => 10.00],
            ['codigo' => '304E', 'descripcion' => 'Honorarios y demás pagos por servicios de docencia', 'porcentaje' => 10.00],
            ['codigo' => '307', 'descripcion' => 'Servicios predomina la mano de obra', 'porcentaje' => 3.00],
            ['codigo' => '308', 'descripcion' => 'Utilización o aprovechamiento de la imagen o renombre', 'porcentaje' => 10.00],
            ['codigo' => '309', 'descripcion' => 'Servicios prestados por medios de comunicación y agencias de publicidad', 'porcentaje' => 3.00],
            ['codigo' => '310', 'descripcion' => 'Servicio de transporte privado de pasajeros o transporte público o privado de carga', 'porcentaje' => 1.00],
            ['codigo' => '311', 'descripcion' => 'Pagos a través de liquidación de compra (nivel cultural o rusticidad)', 'porcentaje' => 2.00],
            ['codigo' => '312', 'descripcion' => 'Transferencia de bienes muebles de naturaleza corporal', 'porcentaje' => 2.00],
            ['codigo' => '312A', 'descripcion' => 'Compra de bienes de origen agrícola, avícola, pecuario, apícola, cunícula, bioacuático, y forestal', 'porcentaje' => 1.00],
            ['codigo' => '312B', 'descripcion' => 'Impuesto a la Renta único para la actividad de producción y cultivo de palma aceitera', 'porcentaje' => 1.00],
            ['codigo' => '314A', 'descripcion' => 'Regalías por concepto de franquicias de acuerdo a Ley de Propiedad Intelectual - pago a personas naturales', 'porcentaje' => 10.00],
            ['codigo' => '314B', 'descripcion' => 'Cánones, derechos de autor, marcas, patentes y similares de acuerdo a Ley de Propiedad Intelectual – pago a personas naturales', 'porcentaje' => 10.00],
            ['codigo' => '314C', 'descripcion' => 'Regalías por concepto de franquicias de acuerdo a Ley de Propiedad Intelectual - pago a sociedades', 'porcentaje' => 10.00],
            ['codigo' => '314D', 'descripcion' => 'Cánones, derechos de autor, marcas, patentes y similares de acuerdo a Ley de Propiedad Intelectual – pago a sociedades', 'porcentaje' => 10.00],
            ['codigo' => '319', 'descripcion' => 'Cuotas de arrendamiento mercantil (prestado por sociedades), inclusive la de opción de compra', 'porcentaje' => 1.00],
            ['codigo' => '320', 'descripcion' => 'Arrendamiento bienes inmuebles', 'porcentaje' => 10.00],
            ['codigo' => '322', 'descripcion' => 'Seguros y reaseguros (primas y cesiones)', 'porcentaje' => 1.00],
            ['codigo' => '323', 'descripcion' => 'Rendimientos financieros pagados a naturales y sociedades (No a IFIs)', 'porcentaje' => 2.00],
            ['codigo' => '323A', 'descripcion' => 'Rendimientos financieros: depósitos Cta. Corriente', 'porcentaje' => 2.00],
            ['codigo' => '323B1', 'descripcion' => 'Rendimientos financieros: depósitos Cta. Ahorros Sociedades', 'porcentaje' => 2.00],
            ['codigo' => '323E', 'descripcion' => 'Rendimientos financieros: depósito a plazo fijo gravados', 'porcentaje' => 2.00],
            ['codigo' => '323E2', 'descripcion' => 'Rendimientos financieros: depósito a plazo fijo exentos', 'porcentaje' => 0.00],
            ['codigo' => '323F', 'descripcion' => 'Rendimientos financieros: operaciones de reporto - repos', 'porcentaje' => 2.00],
            ['codigo' => '323G', 'descripcion' => 'Inversiones (captaciones) rendimientos distintos de aquellos pagados a IFIs', 'porcentaje' => 2.00],
            ['codigo' => '323H', 'descripcion' => 'Rendimientos financieros: obligaciones', 'porcentaje' => 2.00],
            ['codigo' => '323I', 'descripcion' => 'Rendimientos financieros: bonos convertible en acciones', 'porcentaje' => 2.00],
            ['codigo' => '323M', 'descripcion' => 'Rendimientos financieros: Inversiones en títulos valores en renta fija gravados', 'porcentaje' => 2.00],
            ['codigo' => '323N', 'descripcion' => 'Rendimientos financieros: Inversiones en títulos valores en renta fija exentos', 'porcentaje' => 0.00],
            ['codigo' => '323O', 'descripcion' => 'Intereses y demás rendimientos financieros pagados a bancos y otras entidades sometidas al control de la Superintendencia de Bancos y de la Economía Popular y Solidaria', 'porcentaje' => 0.00],
            ['codigo' => '323P', 'descripcion' => 'Intereses pagados por entidades del sector público a favor de sujetos pasivos', 'porcentaje' => 2.00],
            ['codigo' => '323Q', 'descripcion' => 'Otros intereses y rendimientos financieros gravados', 'porcentaje' => 2.00],
            ['codigo' => '323R', 'descripcion' => 'Otros intereses y rendimientos financieros exentos', 'porcentaje' => 0.00],
            ['codigo' => '323S', 'descripcion' => 'Pagos y créditos en cuenta efectuados por el BCE y los depósitos centralizados de valores, en calidad de intermediarios', 'porcentaje' => 2.00],
            ['codigo' => '323T', 'descripcion' => 'Rendimientos financieros originados en la deuda pública ecuatoriana', 'porcentaje' => 0.00],
            ['codigo' => '323U', 'descripcion' => 'Rendimientos financieros originados en títulos valores de obligaciones de 360 días o más', 'porcentaje' => 0.00],
            ['codigo' => '324A', 'descripcion' => 'Intereses y comisiones en operaciones de crédito entre instituciones del sistema financiero y entidades economía popular y solidaria', 'porcentaje' => 1.00],
            ['codigo' => '324B', 'descripcion' => 'Inversiones entre instituciones del sistema financiero y entidades economía popular y solidaria', 'porcentaje' => 1.00],
            ['codigo' => '324C', 'descripcion' => 'Pagos y créditos en cuenta efectuados por el BCE, en calidad de intermediarios, entre instituciones del sistema financiero', 'porcentaje' => 1.00],
            ['codigo' => '325', 'descripcion' => 'Anticipo dividendos a residentes o establecidos en el Ecuador', 'porcentaje' => 22.00],
            ['codigo' => '325A', 'descripcion' => 'Préstamos accionistas, beneficiarios o partícipes residentes o establecidos en el Ecuador', 'porcentaje' => 22.00],
            ['codigo' => '326', 'descripcion' => 'Dividendos distribuidos que correspondan al impuesto a la renta único establecido en el art. 27 de la LRTI', 'porcentaje' => 25.00],
            ['codigo' => '327', 'descripcion' => 'Dividendos distribuidos a personas naturales residentes', 'porcentaje' => 25.00],
            ['codigo' => '328', 'descripcion' => 'Dividendos distribuidos a sociedades residentes', 'porcentaje' => 25.00],
            ['codigo' => '329', 'descripcion' => 'Dividendos distribuidos a fideicomisos residentes', 'porcentaje' => 25.00],
            ['codigo' => '330', 'descripcion' => 'Dividendos gravados distribuidos en acciones (reinversión de utilidades sin derecho a reducción tarifa IR)', 'porcentaje' => 25.00],
            ['codigo' => '331', 'descripcion' => 'Dividendos exentos distribuidos en acciones (reinversión de utilidades con derecho a reducción tarifa IR)', 'porcentaje' => 0.00],
            ['codigo' => '332', 'descripcion' => 'Otras compras de bienes y servicios no sujetas a retención', 'porcentaje' => 0.00],
            ['codigo' => '332B', 'descripcion' => 'Compra de bienes inmuebles', 'porcentaje' => 0.00],
            ['codigo' => '332C', 'descripcion' => 'Transporte público de pasajeros', 'porcentaje' => 0.00],
            ['codigo' => '332D', 'descripcion' => 'Pagos en el país por transporte de pasajeros o transporte internacional de carga', 'porcentaje' => 0.00],
            ['codigo' => '332E', 'descripcion' => 'Valores entregados por las cooperativas de transporte a sus socios', 'porcentaje' => 0.00],
            ['codigo' => '332F', 'descripcion' => 'Compraventa de divisas distintas al dólar de los Estados Unidos de América', 'porcentaje' => 0.00],
            ['codigo' => '332G', 'descripcion' => 'Pagos con tarjeta de crédito', 'porcentaje' => 0.00],
            ['codigo' => '332H', 'descripcion' => 'Pago al exterior tarjeta de crédito reportada por la Emisora de tarjeta de crédito, solo RECAP', 'porcentaje' => 0.00],
            ['codigo' => '332I', 'descripcion' => 'Pago a través de convenio de debito (Clientes IFIs)', 'porcentaje' => 0.00],
            ['codigo' => '333', 'descripcion' => 'Enajenación de derechos representativos de capital y otros derechos cotizados en bolsa ecuatoriana', 'porcentaje' => 0.20],
            ['codigo' => '334', 'descripcion' => 'Enajenación de derechos representativos de capital y otros derechos no cotizados en bolsa ecuatoriana', 'porcentaje' => 1.00],
            ['codigo' => '335', 'descripcion' => 'Loterías, rifas, apuestas y similares', 'porcentaje' => 15.00],
            ['codigo' => '336', 'descripcion' => 'Venta de combustibles a comercializadoras', 'porcentaje' => 0.20],
            ['codigo' => '337', 'descripcion' => 'Venta de combustibles a distribuidores', 'porcentaje' => 0.30],
            ['codigo' => '338', 'descripcion' => 'Compra local de banano a productor', 'porcentaje' => 1.00],
            ['codigo' => '339', 'descripcion' => 'Liquidación impuesto único a la venta local de banano de producción propia', 'porcentaje' => 2.00],
            ['codigo' => '340', 'descripcion' => 'Impuesto único a la exportación de banano de producción propia - componente 1', 'porcentaje' => 1.25],
            ['codigo' => '341', 'descripcion' => 'Impuesto único a la exportación de banano de producción propia - componente 2', 'porcentaje' => 1.50],
            ['codigo' => '342', 'descripcion' => 'Impuesto único a la exportación de banano producido por terceros', 'porcentaje' => 2.00],
            ['codigo' => '343', 'descripcion' => 'Otras retenciones aplicables el 1%', 'porcentaje' => 1.00],
            ['codigo' => '343A', 'descripcion' => 'Energía eléctrica', 'porcentaje' => 1.00],
            ['codigo' => '343B', 'descripcion' => 'Actividades de construcción de obra material inmueble, urbanización, lotización o actividades similares', 'porcentaje' => 2.00],
            ['codigo' => '343C', 'descripcion' => 'Impuesto Redimible a las botellas plásticas - IRBP', 'porcentaje' => 1.00],
            ['codigo' => '344', 'descripcion' => 'Otras retenciones aplicables el 2%', 'porcentaje' => 2.00],
            ['codigo' => '344A', 'descripcion' => 'Pago local tarjeta de crédito reportada por la Emisora de tarjeta de crédito, solo RECAP', 'porcentaje' => 2.00],
            ['codigo' => '344B', 'descripcion' => 'Adquisición de sustancias minerales dentro del territorio nacional', 'porcentaje' => 2.00],
            ['codigo' => '345', 'descripcion' => 'Otras retenciones aplicables el 10%', 'porcentaje' => 10.00],
            ['codigo' => '346', 'descripcion' => 'Otras retenciones aplicables a otros porcentajes', 'porcentaje' => 0.00],
            ['codigo' => '346A', 'descripcion' => 'Otras ganancias de capital distintas de enajenación de derechos representativos de capital', 'porcentaje' => 22.00],
            ['codigo' => '346B', 'descripcion' => 'Donaciones en dinero - Impuesto a las donaciones', 'porcentaje' => 35.00],
            ['codigo' => '346C', 'descripcion' => 'Retención a cargo del propio sujeto pasivo por la exportación de concentrados y/o elementos metálicos', 'porcentaje' => 10.00],
            ['codigo' => '346D', 'descripcion' => 'Retención a cargo del propio sujeto pasivo por la comercialización de productos forestales', 'porcentaje' => 1.00],
            // Pagos al exterior
            ['codigo' => '500', 'descripcion' => 'Pago a no residentes - Rentas Inmobiliarias', 'porcentaje' => 25.00],
            ['codigo' => '501', 'descripcion' => 'Pago a no residentes - Beneficios/Servicios Empresariales', 'porcentaje' => 25.00],
            ['codigo' => '501A', 'descripcion' => 'Pago a no residentes - Servicios técnicos, administrativos o de consultoría y regalías', 'porcentaje' => 25.00],
            ['codigo' => '503', 'descripcion' => 'Pago a no residentes - Navegación Marítima y/o aérea', 'porcentaje' => 25.00],
            ['codigo' => '504', 'descripcion' => 'Pago a no residentes - Dividendos distribuidos a personas naturales', 'porcentaje' => 25.00],
            ['codigo' => '504A', 'descripcion' => 'Pago al exterior - Dividendos a sociedades con beneficiario efectivo persona natural residente en el Ecuador', 'porcentaje' => 25.00],
            ['codigo' => '504B', 'descripcion' => 'Pago a no residentes - Dividendos a fideicomisos con beneficiario efectivo persona natural residente en el Ecuador', 'porcentaje' => 25.00],
            ['codigo' => '504C', 'descripcion' => 'Pago a no residentes - Dividendos a sociedades domiciladas en paraísos fiscales', 'porcentaje' => 25.00],
            ['codigo' => '504D', 'descripcion' => 'Pago a no residentes - Dividendos a fideicomisos domicilados en paraísos fiscales', 'porcentaje' => 25.00],
            ['codigo' => '504E', 'descripcion' => 'Pago a no residentes - Anticipo dividendos (no domiciliada en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '504F', 'descripcion' => 'Pago a no residentes - Anticipo dividendos (domiciliadas en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '504G', 'descripcion' => 'Pago a no residentes - Préstamos accionistas (no domiciladas en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '504H', 'descripcion' => 'Pago a no residentes - Préstamos accionistas (domiciladas en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '504I', 'descripcion' => 'Pago a no residentes - Préstamos no comerciales a partes relacionadas (no domiciladas en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '504J', 'descripcion' => 'Pago a no residentes - Préstamos no comerciales a partes relacionadas (domiciladas en paraísos fiscales)', 'porcentaje' => 25.00],
            ['codigo' => '505', 'descripcion' => 'Pago a no residentes - Rendimientos financieros', 'porcentaje' => 25.00],
            ['codigo' => '505A', 'descripcion' => 'Pago a no residentes - Intereses de créditos de Instituciones Financieras del exterior', 'porcentaje' => 25.00],
            ['codigo' => '505B', 'descripcion' => 'Pago a no residentes - Intereses de créditos de gobierno a gobierno', 'porcentaje' => 0.00],
            ['codigo' => '505C', 'descripcion' => 'Pago a no residentes - Intereses de créditos de organismos multilaterales', 'porcentaje' => 0.00],
            ['codigo' => '505D', 'descripcion' => 'Pago a no residentes - Intereses por financiamiento de proveedores externos', 'porcentaje' => 25.00],
            ['codigo' => '505E', 'descripcion' => 'Pago a no residentes - Intereses de otros créditos externos', 'porcentaje' => 25.00],
            ['codigo' => '505F', 'descripcion' => 'Pago a no residentes - Otros Intereses y Rendimientos Financieros', 'porcentaje' => 25.00],
            ['codigo' => '509', 'descripcion' => 'Pago a no residentes - Cánones, derechos de autor, marcas, patentes y similares', 'porcentaje' => 25.00],
            ['codigo' => '509A', 'descripcion' => 'Pago a no residentes - Regalías por concepto de franquicias', 'porcentaje' => 25.00],
            ['codigo' => '510', 'descripcion' => 'Pago a no residentes - Otras ganancias de capital distintas de enajenación de derechos representativos de capital', 'porcentaje' => 25.00],
            ['codigo' => '511', 'descripcion' => 'Pago a no residentes - Servicios profesionales independientes', 'porcentaje' => 25.00],
            ['codigo' => '512', 'descripcion' => 'Pago a no residentes - Servicios profesionales dependientes', 'porcentaje' => 25.00],
            ['codigo' => '513', 'descripcion' => 'Pago a no residentes - Artistas', 'porcentaje' => 25.00],
            ['codigo' => '513A', 'descripcion' => 'Pago a no residentes - Deportistas', 'porcentaje' => 25.00],
            ['codigo' => '514', 'descripcion' => 'Pago a no residentes - Participación de consejeros', 'porcentaje' => 25.00],
            ['codigo' => '515', 'descripcion' => 'Pago a no residentes - Entretenimiento Público', 'porcentaje' => 25.00],
            ['codigo' => '516', 'descripcion' => 'Pago a no residentes - Pensiones', 'porcentaje' => 25.00],
            ['codigo' => '517', 'descripcion' => 'Pago a no residentes - Reembolso de Gastos', 'porcentaje' => 25.00],
            ['codigo' => '518', 'descripcion' => 'Pago a no residentes - Funciones Públicas', 'porcentaje' => 25.00],
            ['codigo' => '519', 'descripcion' => 'Pago a no residentes - Estudiantes', 'porcentaje' => 25.00],
            ['codigo' => '520A', 'descripcion' => 'Pago a no residentes - Pago a proveedores de servicios hoteleros y turísticos en el exterior', 'porcentaje' => 25.00],
            ['codigo' => '520B', 'descripcion' => 'Pago a no residentes - Arrendamientos mercantil internacional', 'porcentaje' => 25.00],
            ['codigo' => '520D', 'descripcion' => 'Pago a no residentes - Comisiones por exportaciones y por promoción de turismo receptivo', 'porcentaje' => 25.00],
            ['codigo' => '520E', 'descripcion' => 'Pago a no residentes - Por empresas de transporte marítimo o aéreo', 'porcentaje' => 0.00],
            ['codigo' => '520F', 'descripcion' => 'Pago a no residentes - Por las agencias internacionales de prensa', 'porcentaje' => 0.00],
            ['codigo' => '520G', 'descripcion' => 'Pago a no residentes - Contratos de fletamento de naves', 'porcentaje' => 0.00],
            ['codigo' => '521', 'descripcion' => 'Pago a no residentes - Enajenación de derechos representativos de capital y otros derechos', 'porcentaje' => 5.00],
            ['codigo' => '523A', 'descripcion' => 'Pago a no residentes - Seguros y reaseguros (primas y cesiones)', 'porcentaje' => 25.00],
            ['codigo' => '525', 'descripcion' => 'Pago a no residentes - Donaciones en dinero - Impuesto a las donaciones', 'porcentaje' => 35.00],
        ];

        foreach ($codigos as $c) {
            DB::table('codigos_retencion')->updateOrInsert(
                ['tipo' => 'RENTA', 'codigo' => $c['codigo']],
                array_merge($c, ['tipo' => 'RENTA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
