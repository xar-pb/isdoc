<?php

namespace Util\Isdoc;

class IsdocWriter
{
    public $xmlnsNameSpace = 'http://isdoc.cz/namespace/2013';
    public $version = '6.0.1';
    
    public $outputFile = 1; // output: 1 = file, 0 = text
    
    public $documentTypesList = array(
     1 // bezna faktura
    , 2 // dobropis
    , 3 // vrubopis
    , 4 //zálohová faktura (nedaňový ZL)
    , 5 //Daňový ZL
    , 6 // Dobropis DZL
    );
    
    public $supplier; // stdClass
    
    public $customer; // stdClass
    
    public $lines = array(); // array of invoice lines stdClass objects
    
    public function __construct()
    {
        $this->supplier = new \stdClass;
        $this->supplier->address = new \stdClass;
        $this->customer = new \stdClass;
        $this->customer->address = new \stdClass;
    }
    
    public function setDocumentType($type)
    {
        if (array_key_exists($type, $this->documentTypesList)) {
            $this->DocumentType = $type;
            return true;
        } else {
            // $value not allowed
            return false;
        }
    }
    
    public function output()
    {
        $xml = new \DomDocument('1.0', 'UTF-8');
        
        $root_element = $xml->createElement('Invoice');
        $root_element->setAttribute('xmlns', $this->xmlnsNameSpace);
        $root_element->setAttribute('version', $this->version);
        
        // please note, 1st level $nodes are appended to $root_element in one cycle at the end of this method
        $nodes['DocumentType'] = $xml->createElement('DocumentType', $this->DocumentType);
        $nodes['ID'] = $xml->createElement('ID', $this->ID);
        $nodes['UUID'] = $xml->createElement('UUID', $this->UUID);
        $nodes['IssuingSystem'] = $xml->createElement('IssuingSystem', $this->IssuingSystem);
        $nodes['IssueDate'] = $xml->createElement('IssueDate', $this->IssueDate);
        $nodes['TaxPointDate'] = $xml->createElement('TaxPointDate', $this->TaxPointDate);
        $nodes['VATApplicable'] = $xml->createElement('VATApplicable', $this->VATApplicable);
        $nodes['ElectronicPossibilityAgreementReference'] = $xml->createElement('ElectronicPossibilityAgreementReference');
        
        $nodes['Note'] = $xml->createElement('Note', $this->Note);
        
        $nodes['LocalCurrencyCode'] = $xml->createElement('LocalCurrencyCode', $this->LocalCurrencyCode);
        
        // 2nd document foreign currency not supported now, copy local currency again
        // $node['ForeignCurrencyCode'] = $xml->addChild('ForeignCurrencyCode', $this->currency);
        $nodes['CurrRate'] = $xml->createElement('CurrRate', 1);
        $nodes['RefCurrRate'] = $xml->createElement('RefCurrRate', 1);
        
        // accounting supplier party
        $nodes['AccountingSupplierParty'] = $xml->createElement('AccountingSupplierParty');
            
            $sub['Party'] = $xml->createElement('Party');
            $nodes['AccountingSupplierParty']->appendChild($sub['Party']);
        
                $sub2['PartyIdentification'] = $xml->createElement('PartyIdentification');
                $sub['Party']->appendChild($sub2['PartyIdentification']);
            
                    $sub3['ID'] =  $xml->createElement('ID', $this->supplier->id);
                    $sub2['PartyIdentification']->appendChild($sub3['ID']);
        
                $sub2['PartyName'] = $xml->createElement('PartyName');
                $sub['Party']->appendChild($sub2['PartyName']);
        
                    $sub3['Name'] = $xml->createElement('Name', $this->supplier->name);
                    $sub2['PartyName']->appendChild($sub3['Name']);
        
                $sub2['PostalAddress'] = $xml->createElement('PostalAddress');
                $sub['Party']->appendChild($sub2['PostalAddress']);
        
                    $sub3['StreetName'] = $xml->createElement('StreetName', $this->supplier->address->street);
                    $sub2['PostalAddress']->appendChild($sub3['StreetName']);
                    
                    $sub3['BuildingNumber'] = $xml->createElement('BuildingNumber', $this->supplier->address->streetNumber);
                    $sub2['PostalAddress']->appendChild($sub3['BuildingNumber']);
                
                    $sub3['CityName'] = $xml->createElement('CityName', $this->supplier->address->city);
                    $sub2['PostalAddress']->appendChild($sub3['CityName']);
        
                    $sub3['PostalZone'] = $xml->createElement('PostalZone', $this->supplier->address->zipCode);
                    $sub2['PostalAddress']->appendChild($sub3['PostalZone']);
        
                    $sub3['Country'] = $xml->createElement('Country');
                    $sub2['PostalAddress']->appendChild($sub3['Country']);

                        $sub4['IdentificationCode'] = $xml->createElement('IdentificationCode', $this->supplier->address->countryCode);
                        $sub3['Country']->appendChild($sub4['IdentificationCode']);
                    
                        $sub4['Name'] = $xml->createElement('Name', $this->supplier->address->countryName);
                        $sub3['Country']->appendChild($sub4['Name']);
        
        // accounting customer party
        $nodes['AccountingCustomerParty'] = $xml->createElement('AccountingCustomerParty');
            
            $sub['Party'] = $xml->createElement('Party');
            $nodes['AccountingCustomerParty']->appendChild($sub['Party']);
        
                $sub2['PartyIdentification'] = $xml->createElement('PartyIdentification');
                $sub['Party']->appendChild($sub2['PartyIdentification']);
            
                    $sub3['ID'] =  $xml->createElement('ID', $this->customer->id);
                    $sub2['PartyIdentification']->appendChild($sub3['ID']);
        
                $sub2['PartyName'] = $xml->createElement('PartyName');
                $sub['Party']->appendChild($sub2['PartyName']);
        
                    $sub3['Name'] = $xml->createElement('Name', $this->customer->name);
                    $sub2['PartyName']->appendChild($sub3['Name']);
        
                $sub2['PostalAddress'] = $xml->createElement('PostalAddress');
                $sub['Party']->appendChild($sub2['PostalAddress']);
        
                    $sub3['StreetName'] = $xml->createElement('StreetName', $this->customer->address->street);
                    $sub2['PostalAddress']->appendChild($sub3['StreetName']);
                    
                    $sub3['BuildingNumber'] = $xml->createElement('BuildingNumber', $this->customer->address->streetNumber);
                    $sub2['PostalAddress']->appendChild($sub3['BuildingNumber']);
                
                    $sub3['CityName'] = $xml->createElement('CityName', $this->customer->address->city);
                    $sub2['PostalAddress']->appendChild($sub3['CityName']);
        
                    $sub3['PostalZone'] = $xml->createElement('PostalZone', $this->customer->address->zipCode);
                    $sub2['PostalAddress']->appendChild($sub3['PostalZone']);
        
                    $sub3['Country'] = $xml->createElement('Country');
                    $sub2['PostalAddress']->appendChild($sub3['Country']);

                        $sub4['IdentificationCode'] = $xml->createElement('IdentificationCode', $this->customer->address->countryCode);
                        $sub3['Country']->appendChild($sub4['IdentificationCode']);
                        
                        $sub4['Name'] = $xml->createElement('Name', $this->customer->address->countryName);
                        $sub3['Country']->appendChild($sub4['Name']);
        
        // invoice lines
        $nodes['InvoiceLines'] = $xml->createElement('InvoiceLines');

        foreach ($this->lines as $item) {

            $sub['InvoiceLine'] = $xml->createElement('InvoiceLine');
            $nodes['InvoiceLines']->appendChild($sub['InvoiceLine']);
            
                $sub2['ID'] = $xml->createElement('ID', $item->ID);
                $sub['InvoiceLine']->appendChild($sub2['ID']);
            
                $sub2['InvoicedQuantity'] = $xml->createElement('InvoicedQuantity', $item->InvoicedQuantity);
                $sub2['InvoicedQuantity']->setAttribute('unitCode', 'Ks');
                $sub['InvoiceLine']->appendChild($sub2['InvoicedQuantity']);
            
                $sub2['LineExtensionAmount'] = $xml->createElement('LineExtensionAmount', $item->LineExtensionAmount);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionAmount']);
                
                $sub2['LineExtensionAmountTaxInclusive'] = $xml->createElement('LineExtensionAmountTaxInclusive', $item->LineExtensionAmountTaxInclusive);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionAmountTaxInclusive']);
            
                $sub2['LineExtensionTaxAmount'] = $xml->createElement('LineExtensionTaxAmount', $item->LineExtensionTaxAmount);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionTaxAmount']);
            
                $sub2['UnitPrice'] = $xml->createElement('UnitPrice', $item->UnitPrice);
                $sub['InvoiceLine']->appendChild($sub2['UnitPrice']);
            
                $sub2['UnitPriceTaxInclusive'] = $xml->createElement('UnitPriceTaxInclusive', $item->UnitPriceTaxInclusive);
                $sub['InvoiceLine']->appendChild($sub2['UnitPriceTaxInclusive']);
            
                $sub2['ClassifiedTaxCategory'] = $xml->createElement('ClassifiedTaxCategory');
                $sub['InvoiceLine']->appendChild($sub2['ClassifiedTaxCategory']);

                    $sub3['Percent'] = $xml->createElement('Percent', $item->TaxPercent);
                    $sub2['ClassifiedTaxCategory']->appendChild($sub3['Percent']);

                    $sub3['VATCalculationMethod'] = $xml->createElement('VATCalculationMethod', 0); // Způsob výpočtu DPH, 0-zdola, 1-shora
                    $sub2['ClassifiedTaxCategory']->appendChild($sub3['VATCalculationMethod']);
            
                $sub2['Item'] = $xml->createElement('Item');
                $sub['InvoiceLine']->appendChild($sub2['Item']);

                    $sub3['Description'] = $xml->createElement('Description', $item->Description);
                    $sub2['Item']->appendChild($sub3['Description']);
        }
        
        // tax total
        $nodes['TaxTotal'] = $xml->createElement('TaxTotal');
        
        foreach ($this->taxTotal as $taxSub) {
            
            $sub['TaxSubTotal'] = $xml->createElement('TaxSubTotal');
            $nodes['TaxTotal']->appendChild($sub['TaxSubTotal']);
                
                $sub2['TaxableAmount'] = $xml->createElement('TaxableAmount', $taxSub->TaxableAmount);
                $sub['TaxSubTotal']->appendChild($sub2['TaxableAmount']);

                $sub2['TaxAmount'] = $xml->createElement('TaxAmount', $taxSub->TaxAmount);
                $sub['TaxSubTotal']->appendChild($sub2['TaxAmount']);
            
                $sub2['TaxInclusiveAmount'] = $xml->createElement('TaxInclusiveAmount', $taxSub->TaxInclusiveAmount);
                $sub['TaxSubTotal']->appendChild($sub2['TaxInclusiveAmount']);
            
                $sub2['AlreadyClaimedTaxableAmount'] = $xml->createElement('AlreadyClaimedTaxableAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['AlreadyClaimedTaxableAmount']);
            
                $sub2['AlreadyClaimedTaxAmount'] = $xml->createElement('AlreadyClaimedTaxAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['AlreadyClaimedTaxAmount']);
            
                $sub2['AlreadyClaimedTaxInclusiveAmount'] = $xml->createElement('AlreadyClaimedTaxInclusiveAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['AlreadyClaimedTaxInclusiveAmount']);
   
                $sub2['DifferenceTaxableAmount'] = $xml->createElement('DifferenceTaxableAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['DifferenceTaxableAmount']);
            
                $sub2['DifferenceTaxAmount'] = $xml->createElement('DifferenceTaxAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['DifferenceTaxAmount']);
            
                $sub2['DifferenceTaxInclusiveAmount'] = $xml->createElement('DifferenceTaxInclusiveAmount', 0);
                $sub['TaxSubTotal']->appendChild($sub2['DifferenceTaxInclusiveAmount']);
                
                $sub2['TaxCategory'] = $xml->createElement('TaxCategory');
                $sub['TaxSubTotal']->appendChild($sub2['TaxCategory']);

                    $sub3['Percent'] = $xml->createElement('Percent', $taxSub->TaxPercent);
                    $sub2['TaxCategory']->appendChild($sub3['Percent']);

                    $sub3['VATApplicable'] = $xml->createElement('VATApplicable', 'true');
                    $sub2['TaxCategory']->appendChild($sub3['VATApplicable']);
        }
        
            $sub['TaxAmount'] = $xml->createElement('TaxAmount', $this->TaxAmount);
            $nodes['TaxTotal']->appendChild($sub['TaxAmount']);
        
        // legal monetary total
        $nodes['LegalMonetaryTotal'] = $xml->createElement('LegalMonetaryTotal');
        
            $sub['TaxExclusiveAmount'] = $xml->createElement('TaxExclusiveAmount', 999);
            $nodes['LegalMonetaryTotal']->appendChild($sub['TaxExclusiveAmount']);
        
            $sub['TaxInclusiveAmount'] = $xml->createElement('TaxInclusiveAmount', 999);
            $nodes['LegalMonetaryTotal']->appendChild($sub['TaxInclusiveAmount']);
        
            $sub['AlreadyClaimedTaxExclusiveAmount'] = $xml->createElement('AlreadyClaimedTaxExclusiveAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['AlreadyClaimedTaxExclusiveAmount']);
        
            $sub['AlreadyClaimedTaxInclusiveAmount'] = $xml->createElement('AlreadyClaimedTaxInclusiveAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['AlreadyClaimedTaxInclusiveAmount']);
        
            $sub['DifferenceTaxExclusiveAmount'] = $xml->createElement('DifferenceTaxExclusiveAmount', 999);
            $nodes['LegalMonetaryTotal']->appendChild($sub['DifferenceTaxExclusiveAmount']);
        
            $sub['DifferenceTaxInclusiveAmount'] = $xml->createElement('DifferenceTaxInclusiveAmount', 999);
            $nodes['LegalMonetaryTotal']->appendChild($sub['DifferenceTaxInclusiveAmount']);
        
            $sub['PayableRoundingAmount'] = $xml->createElement('PayableRoundingAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PayableRoundingAmount']);
        
            $sub['PaidDepositsAmount'] = $xml->createElement('PaidDepositsAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PaidDepositsAmount']);
        
            $sub['PayableAmount'] = $xml->createElement('PayableAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PayableAmount']);
        
        foreach ($nodes as $key => $val) {
            $root_element->appendChild($nodes[$key]);
        }
        
        $xml->appendChild($root_element);
        $xml->formatOutput = true;
        
        if ($this->outputFile) {
            header('Content-type: text/xml; charset=UTF-8');
            header('Content-disposition: attachment; filename="'.$this->ID.'.isdoc"');
        }
        
        echo $xml->saveXML();
    }

}
