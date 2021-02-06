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
    
    public $DocumentType;
    public $DocumentTypeName;
    
    public $supplier; // stdClass
    
    public $customer; // stdClass
    
    public $lines = array(); // array of invoice lines stdClass objects
    
    public $taxTotal = array(); // field of sums of amounts in tax rates
    
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
            $this->setDocumentTypeName($this->DocumentType);
            return true;
        } else {
            // $value not allowed
            return false;
        }
    }
    
    public function setDocumentTypeName($type) {
        switch ($this->DocumentType) {
            case 1:
                $name = 'Invoice';
                break;
            case 2:
                $name = 'Credit';
                break;
            case 3:
                $name = 'Debit';
                break;
            case 4:
                $name = 'Advance';
                break;
            case 5:
                $name = 'TaxAdvance';
                break;
            case 6:
                $name = 'CreditTax';
                break;
            default:
                $name = 'Other';
                break;
        }
        $this->DocumentTypeName = $name;
        return true;
    }
    
    public function output()
    {
        $xml = new \DomDocument('1.0', 'UTF-8');
        
        $this->totalPriceWithoutVat = round($this->totalPriceWithoutVat, 2);
        $this->totalPrice = round($this->totalPrice, 2);
        $this->TaxAmount = round($this->TaxAmount, 2);
        
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
        
                    $company = $this->supplier->companyName;
                    if (empty($company)) {
                        $company = $this->supplier->name.' '.$this->supplier->surname;
                    }
                    $sub3['Name'] = $xml->createElement('Name', $company);
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
        
                if ($this->supplier->companyVatId and $this->VATApplicable) {
                    $sub2['PartyTaxScheme'] = $xml->createElement('PartyTaxScheme');
                    $sub['Party']->appendChild($sub2['PartyTaxScheme']);

                        $sub3['CompanyID'] = $xml->createElement('CompanyID', $this->supplier->companyVatId);
                        $sub2['PartyTaxScheme']->appendChild($sub3['CompanyID']);

                        $sub3['TaxScheme'] = $xml->createElement('TaxScheme', 'VAT');
                        $sub2['PartyTaxScheme']->appendChild($sub3['TaxScheme']);
                }
        
                $sub2['Contact'] = $xml->createElement('Contact');
                $sub['Party']->appendChild($sub2['Contact']);
                
                    $contact =  $this->supplier->name.' '.$this->supplier->surname;
                    $sub3['Name'] = $xml->createElement('Name', $contact);
                    $sub2['Contact']->appendChild($sub3['Name']);
        
                    $sub3['ElectronicMail'] = $xml->createElement('ElectronicMail', $this->supplier->email);
                    $sub2['Contact']->appendChild($sub3['ElectronicMail']);
                    
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
        
                    $company = $this->customer->companyName;
                    if (empty($company)) {
                        $company = $this->customer->name.' '.$this->customer->surname;
                    }
                    $sub3['Name'] = $xml->createElement('Name', $company);
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
        
                if ($this->customer->companyVatId) {
                    $sub2['PartyTaxScheme'] = $xml->createElement('PartyTaxScheme');
                    $sub['Party']->appendChild($sub2['PartyTaxScheme']);
                        
                        $sub3['CompanyID'] = $xml->createElement('CompanyID', $this->customer->companyVatId);
                        $sub2['PartyTaxScheme']->appendChild($sub3['CompanyID']);
                        
                        $sub3['TaxScheme'] = $xml->createElement('TaxScheme', 'VAT');
                        $sub2['PartyTaxScheme']->appendChild($sub3['TaxScheme']);
                }
                
                $sub2['Contact'] = $xml->createElement('Contact');
                $sub['Party']->appendChild($sub2['Contact']);
                
                    $contact =  $this->customer->name.' '.$this->customer->surname;
                    $sub3['Name'] = $xml->createElement('Name', $contact);
                    $sub2['Contact']->appendChild($sub3['Name']);
        
                    $sub3['ElectronicMail'] = $xml->createElement('ElectronicMail', $this->customer->email);
                    $sub2['Contact']->appendChild($sub3['ElectronicMail']);
        
        // invoice lines
        $nodes['InvoiceLines'] = $xml->createElement('InvoiceLines');

        foreach ($this->lines as $item) {

            $sub['InvoiceLine'] = $xml->createElement('InvoiceLine');
            $nodes['InvoiceLines']->appendChild($sub['InvoiceLine']);
            
                $sub2['ID'] = $xml->createElement('ID', $item->ID);
                $sub['InvoiceLine']->appendChild($sub2['ID']);
            
                $sub2['InvoicedQuantity'] = $xml->createElement('InvoicedQuantity', (int) $item->InvoicedQuantity);
                $sub2['InvoicedQuantity']->setAttribute('unitCode', 'Ks');
                $sub['InvoiceLine']->appendChild($sub2['InvoicedQuantity']);
            
                $sub2['LineExtensionAmount'] = $xml->createElement('LineExtensionAmount', (float) $item->LineExtensionAmount);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionAmount']);
                
                $sub2['LineExtensionAmountTaxInclusive'] = $xml->createElement('LineExtensionAmountTaxInclusive', (float) $item->LineExtensionAmountTaxInclusive);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionAmountTaxInclusive']);
            
                $sub2['LineExtensionTaxAmount'] = $xml->createElement('LineExtensionTaxAmount', (float) $item->LineExtensionTaxAmount);
                $sub['InvoiceLine']->appendChild($sub2['LineExtensionTaxAmount']);
            
                $sub2['UnitPrice'] = $xml->createElement('UnitPrice', $item->UnitPrice);
                $sub['InvoiceLine']->appendChild($sub2['UnitPrice']);
            
                $sub2['UnitPriceTaxInclusive'] = $xml->createElement('UnitPriceTaxInclusive', (float) $item->UnitPriceTaxInclusive);
                $sub['InvoiceLine']->appendChild($sub2['UnitPriceTaxInclusive']);
            
                $sub2['ClassifiedTaxCategory'] = $xml->createElement('ClassifiedTaxCategory');
                $sub['InvoiceLine']->appendChild($sub2['ClassifiedTaxCategory']);

                    $sub3['Percent'] = $xml->createElement('Percent', (float) $item->TaxPercent);
                    $sub2['ClassifiedTaxCategory']->appendChild($sub3['Percent']);

                    $sub3['VATCalculationMethod'] = $xml->createElement('VATCalculationMethod', 1); // Způsob výpočtu DPH, 0-zdola, 1-shora
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
                
                $sub2['TaxableAmount'] = $xml->createElement('TaxableAmount', (float) $taxSub->TaxableAmount);
                $sub['TaxSubTotal']->appendChild($sub2['TaxableAmount']);

                $sub2['TaxAmount'] = $xml->createElement('TaxAmount', (float) $taxSub->TaxAmount);
                $sub['TaxSubTotal']->appendChild($sub2['TaxAmount']);
            
                $sub2['TaxInclusiveAmount'] = $xml->createElement('TaxInclusiveAmount', (float) $taxSub->TaxInclusiveAmount);
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

                    $sub3['Percent'] = $xml->createElement('Percent', (float) $taxSub->TaxPercent);
                    $sub2['TaxCategory']->appendChild($sub3['Percent']);

                    $sub3['VATApplicable'] = $xml->createElement('VATApplicable', $this->VATApplicable);
                    $sub2['TaxCategory']->appendChild($sub3['VATApplicable']);
        }
        
            $sub['TaxAmount'] = $xml->createElement('TaxAmount', (float) $this->TaxAmount);
            $nodes['TaxTotal']->appendChild($sub['TaxAmount']);
        
        // legal monetary total
        $nodes['LegalMonetaryTotal'] = $xml->createElement('LegalMonetaryTotal');
        
            $sub['TaxExclusiveAmount'] = $xml->createElement('TaxExclusiveAmount', (float) $this->totalPriceWithoutVat);
            $nodes['LegalMonetaryTotal']->appendChild($sub['TaxExclusiveAmount']);
        
            $sub['TaxInclusiveAmount'] = $xml->createElement('TaxInclusiveAmount', (float) $this->totalPrice);
            $nodes['LegalMonetaryTotal']->appendChild($sub['TaxInclusiveAmount']);
        
            $sub['AlreadyClaimedTaxExclusiveAmount'] = $xml->createElement('AlreadyClaimedTaxExclusiveAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['AlreadyClaimedTaxExclusiveAmount']);
        
            $sub['AlreadyClaimedTaxInclusiveAmount'] = $xml->createElement('AlreadyClaimedTaxInclusiveAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['AlreadyClaimedTaxInclusiveAmount']);
        
            $sub['DifferenceTaxExclusiveAmount'] = $xml->createElement('DifferenceTaxExclusiveAmount', (float) $this->totalPriceWithoutVat);
            $nodes['LegalMonetaryTotal']->appendChild($sub['DifferenceTaxExclusiveAmount']);
        
            $sub['DifferenceTaxInclusiveAmount'] = $xml->createElement('DifferenceTaxInclusiveAmount', (float) $this->totalPrice);
            $nodes['LegalMonetaryTotal']->appendChild($sub['DifferenceTaxInclusiveAmount']);
        
            $sub['PayableRoundingAmount'] = $xml->createElement('PayableRoundingAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PayableRoundingAmount']);
        
            $sub['PaidDepositsAmount'] = $xml->createElement('PaidDepositsAmount', 0);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PaidDepositsAmount']);
        
            $sub['PayableAmount'] = $xml->createElement('PayableAmount', (float) $this->totalPrice);
            $nodes['LegalMonetaryTotal']->appendChild($sub['PayableAmount']);
        
        // payment means
        /*
        $nodes['PaymentMeans'] = $xml->createElement('PaymentMeans');
        
            $sub['Payment'] = $xml->createElement('Payment');
            $nodes['PaymentMeans']->appendChild($sub['Payment']);
        
                $sub2['Details'] = $xml->createElement('Details');
                $sub['Payment']->appendChild($sub2['Details']);
                
                    $sub3['VariableSymbol'] = $xml->createElement('VariableSymbol', $this->variableSymbol);
                    $sub2['Details']->appendChild($sub3['VariableSymbol']);
        */
        
        foreach ($nodes as $key => $val) {
            $root_element->appendChild($nodes[$key]);
        }
        
        $xml->appendChild($root_element);
        $xml->formatOutput = true;
        
        if ($this->outputFile) {
            header('Content-type: text/xml; charset=UTF-8');
            header('Content-disposition: attachment; filename="'.$this->ID.'.isdoc"');
            echo $xml->saveXML();
        } else {
            return $xml->saveXML();
        }
    }
}
