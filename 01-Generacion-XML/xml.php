<?php

class GeneradorXML
{
    public function CrearXMLFactura($nombrexml, $emisor, $cliente, $comprobante, $detalle)
    {
        $doc = new DOMDocument();
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = true;
        $doc->encoding = 'utf-8';

        //crear el texto XML de la factura electronica
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
           <ext:UBLExtensions>
              <ext:UBLExtension>
                 <ext:ExtensionContent />
              </ext:UBLExtension>
           </ext:UBLExtensions>
           <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
           <cbc:CustomizationID>2.0</cbc:CustomizationID>
           <cbc:ID>'.$comprobante['serie'].'-'.$comprobante['correlativo'].'</cbc:ID>
           <cbc:IssueDate>'.$comprobante['fecha_emision'].'</cbc:IssueDate>
           <cbc:IssueTime>00:00:00</cbc:IssueTime>
           <cbc:DueDate>'.$comprobante['fecha_emision'].'</cbc:DueDate>
           <cbc:InvoiceTypeCode listID="0101">'.$comprobante['tipodoc'].'</cbc:InvoiceTypeCode>
           <cbc:Note languageLocaleID="1000"><![CDATA['.$comprobante['total_texto'].']]></cbc:Note>
           <cbc:DocumentCurrencyCode>'.$comprobante['moneda'].'</cbc:DocumentCurrencyCode>
           <cac:Signature>
              <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
              <cbc:Note><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Note>
              <cac:SignatoryParty>
                 <cac:PartyIdentification>
                    <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyName>
                    <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                 </cac:PartyName>
              </cac:SignatoryParty>
              <cac:DigitalSignatureAttachment>
                 <cac:ExternalReference>
                    <cbc:URI>#SIGN-EMPRESA</cbc:URI>
                 </cac:ExternalReference>
              </cac:DigitalSignatureAttachment>
           </cac:Signature>
           <cac:AccountingSupplierParty>
              <cac:Party>
                 <cac:PartyIdentification>
                    <cbc:ID schemeID="'.$emisor['tipodoc'].'">'.$emisor['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyName>
                    <cbc:Name><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Name>
                 </cac:PartyName>
                 <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                    <cac:RegistrationAddress>
                       <cbc:ID>'.$emisor['ubigeo'].'</cbc:ID>
                       <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                       <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
                       <cbc:CityName>'.$emisor['provincia'].'</cbc:CityName>
                       <cbc:CountrySubentity>'.$emisor['departamento'].'</cbc:CountrySubentity>
                       <cbc:District>'.$emisor['distrito'].'</cbc:District>
                       <cac:AddressLine>
                          <cbc:Line><![CDATA['.$emisor['direccion'].']]></cbc:Line>
                       </cac:AddressLine>
                       <cac:Country>
                          <cbc:IdentificationCode>'.$emisor['pais'].'</cbc:IdentificationCode>
                       </cac:Country>
                    </cac:RegistrationAddress>
                 </cac:PartyLegalEntity>
              </cac:Party>
           </cac:AccountingSupplierParty>
           <cac:AccountingCustomerParty>
              <cac:Party>
                 <cac:PartyIdentification>
                    <cbc:ID schemeID="'.$cliente['tipodoc'].'">'.$cliente['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA['.$cliente['razon_social'].']]></cbc:RegistrationName>
                    <cac:RegistrationAddress>
                       <cac:AddressLine>
                          <cbc:Line><![CDATA['.$cliente['direccion'].']]></cbc:Line>
                       </cac:AddressLine>
                       <cac:Country>
                          <cbc:IdentificationCode>'.$cliente['pais'].'</cbc:IdentificationCode>
                       </cac:Country>
                    </cac:RegistrationAddress>
                 </cac:PartyLegalEntity>
              </cac:Party>
           </cac:AccountingCustomerParty>

           <cac:PaymentTerms>
               <cbc:ID>FormaPago</cbc:ID>
               <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
           </cac:PaymentTerms>


           <cac:TaxTotal>
              <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>';

              if($comprobante['total_opgravadas']>0)
              {
                  $xml.='<cac:TaxSubtotal>
                     <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opgravadas'].'</cbc:TaxableAmount>
                     <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
                     <cac:TaxCategory>
                        <cac:TaxScheme>
                           <cbc:ID>1000</cbc:ID>
                           <cbc:Name>IGV</cbc:Name>
                           <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                     </cac:TaxCategory>
                  </cac:TaxSubtotal>';
              }             
                
              if($comprobante['total_opexoneradas']>0){
                 $xml.='<cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opexoneradas'].'</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                       <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                       <cac:TaxScheme>
                          <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID>
                          <cbc:Name>EXO</cbc:Name>
                          <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';
              }
        
              if($comprobante['total_opinafectas']>0){
                 $xml.='<cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opinafectas'].'</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                       <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                       <cac:TaxScheme>
                          <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID>
                          <cbc:Name>INA</cbc:Name>
                          <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';
              }
        
               $total_antes_de_impuestos = $comprobante['total_opgravadas']+$comprobante['total_opexoneradas']+$comprobante['total_opinafectas'];
        
           $xml.='</cac:TaxTotal>
           <cac:LegalMonetaryTotal>
              <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$total_antes_de_impuestos.'</cbc:LineExtensionAmount>
              <cbc:TaxInclusiveAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:TaxInclusiveAmount>
              <cbc:PayableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:PayableAmount>
           </cac:LegalMonetaryTotal>';
           
           foreach($detalle as $k=>$v){
        
               $xml.='<cac:InvoiceLine>
                  <cbc:ID>'.$v['item'].'</cbc:ID>
                  <cbc:InvoicedQuantity unitCode="'.$v['unidad'].'">'.$v['cantidad'].'</cbc:InvoicedQuantity>
                  <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:LineExtensionAmount>
                  <cac:PricingReference>
                     <cac:AlternativeConditionPrice>
                        <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['precio_unitario'].'</cbc:PriceAmount>
                        <cbc:PriceTypeCode>'.$v['tipo_precio'].'</cbc:PriceTypeCode>
                     </cac:AlternativeConditionPrice>
                  </cac:PricingReference>
                  <cac:TaxTotal>
                     <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                     <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                        <cac:TaxCategory>
                           <cbc:Percent>'.$v['porcentaje_igv'].'</cbc:Percent>
                           <cbc:TaxExemptionReasonCode>'.$v['codigo_afectacion_alt'].'</cbc:TaxExemptionReasonCode>
                           <cac:TaxScheme>
                              <cbc:ID>'.$v['codigo_afectacion'].'</cbc:ID>
                              <cbc:Name>'.$v['nombre_afectacion'].'</cbc:Name>
                              <cbc:TaxTypeCode>'.$v['tipo_afectacion'].'</cbc:TaxTypeCode>
                           </cac:TaxScheme>
                        </cac:TaxCategory>
                     </cac:TaxSubtotal>
                  </cac:TaxTotal>
                  <cac:Item>
                     <cbc:Description><![CDATA['.$v['descripcion'].']]></cbc:Description>
                     <cac:SellersItemIdentification>
                        <cbc:ID>'.$v['codigo'].'</cbc:ID>
                     </cac:SellersItemIdentification>
                  </cac:Item>
                  <cac:Price>
                     <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_unitario'].'</cbc:PriceAmount>
                  </cac:Price>
               </cac:InvoiceLine>';
                   
                   }
        
        $xml.="</Invoice>";

        $doc->loadXML($xml);
        $doc->save($nombrexml . '.XML');
    }
}

?>