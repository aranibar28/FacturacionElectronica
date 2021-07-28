<?php
$emisor = array(
    'tipodoc'       =>  '6',
    'ruc'           =>  '20123456789',
    'razon_social'  =>  'EMPRESA EMISORA S.A.',
    'nombre_comercial'  =>  'EMISOR S.A',
    'direccion'     =>  'SURCO - LIMA',
    'pais'          =>  'PE',
    'departamento'  =>  'LIMA',
    'provincia'     =>  'LIMA',
    'distrito'      =>  'LIMA',
    'ubigeo'        =>  '010101',
    'usuario_secundario'        =>  'MODDATOS',
    'clave_usuario_secundario'  =>  'MODDATOS'
);

$cliente = array(
    'tipodoc'       =>  '6',
    'ruc'           =>  '10123456789',
    'razon_social'  =>  'PEPITO DOMINGEZ',
    'direccion'     =>  'LIMA VIRTUAL',
    'pais'          =>  'PE'
);

$comprobante = array(
    'tipodoc'       =>  '01',
    'serie'         =>  'F001',
    'correlativo'   =>  '1',
    'fecha_emision' =>  date('Y-m-d'),
    'moneda'        =>  'PEN',
    'total_opgravadas'  =>  0,
    'total_opexoneradas'=>  0,
    'total_opinafectas' =>  0,
    'igv'               =>  0,
    'total'             =>  0,
    'total_texto'       =>  0
);

$detalle = array(
    array(
        'item'              =>  '1',
        'codigo'            =>  'COD01',
        'descripcion'       =>  'ACEITE',
        'cantidad'          =>  10,
        'valor_unitario'    =>  6.78, // NO INCLUYE IGV
        'precio_unitario'  =>  8,  // SI INCLUYE IGV
        'tipo_precio'       =>  '01',
        'igv'               =>  12.2,
        'porcentaje_igv'    =>  18,
        'valor_total'       =>  67.8,
        'importe_total'     =>  80,
        'unidad'            =>  'NIU',
        'codigo_afectacion_alt' =>  '10',
        'codigo_afectacion'     =>  '1000',
        'nombre_afectacion'     =>  'IGV',
        'tipo_afectacion'       =>  'VAT'
    ),
    array(
        'item'              =>  2,
        'codigo'            =>  'COD02',
        'descripcion'       =>  'LIBRO',
        'cantidad'          =>  1,
        'valor_unitario'    =>  50,//NO INCLUYE IGV
        'precio_unitario'   =>  50,//SI INCLUYE IGV
        'tipo_precio'       =>  '01', //01: IGV 02: SIN IGV
        'igv'               =>  0,
        'porcentaje_igv'    =>  18,
        'valor_total'       =>  50,
        'importe_total'     =>  50,
        'unidad'            =>  'NIU',
        'codigo_afectacion_alt'     =>  '20',
        'codigo_afectacion'         =>  '9997',
        'nombre_afectacion'         =>  'EXO',
        'tipo_afectacion'           =>  'VAT'
    ),
    array(
        'item'              =>  3,
        'codigo'            =>  'COD03',
        'descripcion'       =>  'TOMATE',
        'cantidad'          =>  1,
        'valor_unitario'    =>  50,//NO INCLUYE IGV
        'precio_unitario'   =>  50,//SI INCLUYE IGV
        'tipo_precio'       =>  '01', //01: IGV 02: SIN IGV
        'igv'               =>  0,
        'porcentaje_igv'    =>  18,
        'valor_total'       =>  50,
        'importe_total'     =>  50,
        'unidad'            =>  'NIU',
        'codigo_afectacion_alt'     =>  '30', //10: GRAVADOS, 20: EXONERADOS, 30: INAFECTOS
        'codigo_afectacion'         =>  '9998',
        'nombre_afectacion'         =>  'INA',
        'tipo_afectacion'           =>  'FRE'
    )
);

//Inicializo totales de la factura
$op_gravadas = 0;
$op_inafectas = 0;
$op_exoneradas = 0;
$igv = 0;
$total = 0;

foreach ($detalle as $value) {
    if($value['codigo_afectacion_alt'] == '10')//OP GRABADAS
    {
        $op_gravadas = $op_gravadas + $value['valor_total'];
    }
    
    if($value['codigo_afectacion_alt'] == '20')//OP EXONERADAS
    {
        $op_exoneradas = $op_exoneradas + $value['valor_total'];
    }
    
    if($value['codigo_afectacion_alt'] == '30')//OP INAFECTAS
    {
        $op_inafectas = $op_inafectas + $value['valor_total'];
    }

    $igv = $igv + $value['igv'];
    $total = $total + $value['importe_total'];
}

$comprobante['total_opgravadas'] = $op_gravadas;
$comprobante['total_opexoneradas'] = $op_exoneradas;
$comprobante['total_opinafectas'] = $op_inafectas;
$comprobante['igv'] = $igv;
$comprobante['total'] = $total;

require_once('cantidad_en_letras.php');
$comprobante['total_texto'] = CantidadEnLetra($total);

//PASO 01 - CREAR EL XML - FACTURA INICIO
require_once('xml.php');
$xml = new GeneradorXML();

//NOMBRE DEL XML: RUC EMISOR - TIPO COMPROBANTE - SERIE - CORRELATIVO . XML
//EJEMPLO: 20123456789-01-F001-1.XML

$nombreXML = $emisor['ruc'] . '-' . $comprobante['tipodoc'] . '-' . $comprobante['serie'] . '-' . $comprobante['correlativo'];

$ruta = 'xml/' . $nombreXML;

$xml->CrearXMLFactura($ruta, $emisor, $cliente, $comprobante, $detalle);
echo "</br> PASO 01: XML DE FACTURA CREADO";

//PASO 01 - CREAR EL XML - FACTURA FIN

?>