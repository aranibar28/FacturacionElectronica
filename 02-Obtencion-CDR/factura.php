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
    'serie'         =>  'F01J',
    'correlativo'   =>  '45679',
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

//PASO 02 - FIMAR DIGITALMENTE - INICIO
require_once('signature.php');
$objFirma = new Signature();
$flg_firma = 0; //posicion donde se firma el XML
$ruta_archivo_xml = 'xml/';
$ruta = $ruta_archivo_xml . $nombreXML . '.XML';
$ruta_certificado = "";
$ruta_firma = $ruta_certificado . 'certificado_prueba_sunat.pfx';
$pass_firma = 'ceti';

$objFirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);

echo "</br> PASO 02: XML FIRMADO DIGITALMENTE";

//PASO 02 - FIMAR DIGITALMENTE - FIN

//PASO 03 - COMPRIMIR - INICIO
$zip = new ZipArchive();
$nombrezip = $nombreXML . '.ZIP';
$ruta_zip = $ruta_archivo_xml . $nombreXML . '.ZIP';

if ($zip->open($ruta_zip, ZipArchive::CREATE) == TRUE) {
    $zip->addFile($ruta, $nombreXML . '.XML');
    $zip->close();
}

echo "</br> PASO 03: XML COMPRIMIDO EN ZIP";

//PASO 03 - COMPRIMIR - FIN

//PASO 04 - CODIFICAR BASE64 INICIO
$ruta_archivo = $ruta_zip;
$nombre_archivo = $nombrezip;
$contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));

echo "</br> PASO 03: ZIP CODIFICADO EN BASE64 : " . $contenido_del_zip;
//PASO 04 - CODIFICAR BASE64 FIN

//PASO 05 - XML ENVELOPE INICIO
//WEB SERVICES DE SUNAT

$ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //WS BETA PRUEBA

//$ws = "https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService"; //WS PRODUCCION

$xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                        <wsse:Password>' . $emisor['clave_usuario_secundario'] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendBill>
                    <fileName>' . $nombre_archivo . '</fileName>
                    <contentFile>' . $contenido_del_zip . '</contentFile>
                </ser:sendBill>
            </soapenv:Body>
        </soapenv:Envelope>';

    $header = array(
        "Content-type: text/xml; charset=\"utf-8\"",
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "SOAPAction: ",
        "Content-lenght: " . strlen($xml_envio)
    );

    $ch = curl_init(); //inicia la llamada
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); //
    curl_setopt($ch,CURLOPT_URL, $ws);//url a consultar
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch,CURLOPT_TIMEOUT, 30);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
    curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");//windows, cuando estemos productivos comenta esta linea

    $response = curl_exec($ch); //Ejecutar y obtiene respuesta
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "</br> PASO 05: ENVIO DE XML A SUNAT: " . $httpcode;
//PASO 05 - XML ENVELOPE FIN

//PASO 06 al 09 - RECIBIR LA RPTA SUNAT INICIO

$estadofe = '0'; //0:Aun no se envia a sunat, 1: Aprobado, 2: Rechazado, 3: Problemas Comunicacion
$ruta_archivo_cdr = "cdr/";

if ($httpcode == 200) { //Hubo rpta, conexion OK
    $doc = new DOMDocument();
    $doc->loadXML($response);//convierto en xml la rpta de sunat (memoria)

    if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
        $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
        //$RPTA = $doc->getElementsByTagName('documentResponse')->item(0)->nodeValue;
        echo "</br> PASO 06: Obtuvimos rpta de SUNAT";

        $cdr = base64_decode($cdr);
        echo "</br> PASO 07: Rpta de SUNAT decodificada";

        file_put_contents($ruta_archivo_cdr . "R-" . $nombrezip, $cdr); //Zip de memoria paso a disco

        $zip = new ZipArchive();
        if($zip->open($ruta_archivo_cdr . "R-" . $nombrezip) == TRUE)
        {
            $zip->extractTo($ruta_archivo_cdr, "R-" . $nombreXML . '.XML');
            $zip->close();
            echo "</br> PASO 08: Descomprimir el ZIP";

            $estadofe = "1";
            echo "</br> PASO 09: PROCESO TERMINADO: Obtuvimos el CDR";
        }
    }
    else
    {
        $estadofe = "2";//error, rechazo, obs
        $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
        $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
        echo "</br> Ocurrio un error con código: " . $codigo . " Msje: " . $mensaje;
    }
}
else
{
    $estadofe = "3"; //Problemas de conexion
    echo curl_error($ch);
    echo '</br> Problemas de conexión';
}

curl_close($ch);

//PASO 06 al 09 - RECIBIR LA RPTA SUNAT FIN

?>