<?php
require_once '../src/Util/Isdoc/IsdocWriter.php';
require_once '../src/Util/Isdoc/IsdocZipArchive.php';
require_once '../src/Util/UUID/UUID.php';

use Util\Isdoc\IsdocWriter;
use Util\Isdoc\IsdocZipArchive;
use Util\UUID\UUID;

$isdoc = new IsdocWriter;

$isdoc->setDocumentType(1);
$isdoc->ID = 'FA20200001'; // ID, lidsky čitelné číslo dokladu

// UUID v5: Given the same namespace and name, the output is always the same.
// PLEASE NOTE: use e.g. https://www.uuidtools.com/v5 and generate your own random namespace
$uuid_namespace = 'a092cd34-6898-11eb-818d-339fe7f487c4'; 
$uuid_seed = $isdoc->ID;
$v5uuid = UUID::v5($uuid_namespace, $uuid_seed);
$isdoc->UUID = $v5uuid; // GUID, identifikace od emitujícího systému

$isdoc->IssuingSystem = 'MY APP'; // aplikace vystavujici doklad

$isdoc->IssueDate = '2020-10-28'; // Datum vystavení
$isdoc->TaxPointDate = '2020-10-28'; // Datum plnění DPH
$isdoc->VATApplicable = 'true'; // Podléhá DPH
$isdoc->Note = 'Poznámka k faktuře';

$isdoc->LocalCurrencyCode = 'CZK'; // Lokální měna dokladu, vždy povinná položka-

$isdoc->supplier->id = '987654321'; // IČO firmy
$isdoc->supplier->companyVatId = 'CZ987654321'; // DIČ firmy
$isdoc->supplier->companyName = 'Čulibrk, a.s.';

$isdoc->supplier->name = 'Jan';
$isdoc->supplier->surname = 'Dařbuján';
$isdoc->supplier->email = 'darbujan@pandrhola.cz';

$isdoc->supplier->address->street = 'Krátká';
$isdoc->supplier->address->streetNumber = '10001';
$isdoc->supplier->address->city = 'Opava';
$isdoc->supplier->address->zipCode = '74601';
$isdoc->supplier->address->countryCode = 'CZ';
$isdoc->supplier->address->countryName = 'Česko';

$isdoc->customer->id = '123456789';
$isdoc->customer->companyVatId = 'CZ123456789'; // DIČ firmy
$isdoc->customer->companyName = 'Test, s.r.o.';

$isdoc->customer->name = 'Eva';
$isdoc->customer->surname = 'Pandrholová';
$isdoc->customer->email = 'eva@pandrholova.cz';

$isdoc->customer->address->street = 'Krátká';
$isdoc->customer->address->streetNumber = '10001';
$isdoc->customer->address->city = 'Opava';
$isdoc->customer->address->zipCode = '74601';
$isdoc->customer->address->countryCode = 'CZ';
$isdoc->customer->address->countryName = 'Česko';

// declare tax counters
$taxable_amount = $tax_inclusive_amount = $tax_amount  = array();
$total_price_without_vat = $total_price = $total_tax_amount = 0;

// invoice lines
$i=1;
$isdoc->lines[$i] = new \stdClass;
$isdoc->lines[$i]->TaxPercent = 21;
// Pořadové číslo řádku faktury
$isdoc->lines[$i]->ID = 1;
$isdoc->lines[$i]->Description = 'Hrábě na relaxační prohrabování hromady bankovek';
// počet jednotek
$isdoc->lines[$i]->InvoicedQuantity = 5;
// jedn.cena bez daně na ř. v T.M.
$isdoc->lines[$i]->UnitPrice = 10;
// jedn.cena s daní na ř. v T.M.
$isdoc->lines[$i]->UnitPriceTaxInclusive = (10 * (1+(21/100)));
// celk.cena bez daně na ř. v T.M.
$isdoc->lines[$i]->LineExtensionAmount = (5 * 10);
// celk.cena s daní na ř. v T.M.
$isdoc->lines[$i]->LineExtensionAmountTaxInclusive = (5 * 10 * (1+(21/100)));
// částka daně na ř. (v T.M.)
$isdoc->lines[$i]->LineExtensionTaxAmount = (5 * 10 * (21/100));

$tr = $isdoc->lines[$i]->TaxPercent; // tax rate
$taxable_amount[$tr] += $isdoc->lines[$i]->LineExtensionAmount;
$tax_inclusive_amount[$tr] += $isdoc->lines[$i]->LineExtensionAmountTaxInclusive;
$tax_amount[$tr] += $isdoc->lines[$i]->LineExtensionTaxAmount;

$total_tax_amount += $isdoc->lines[$i]->LineExtensionTaxAmount;
$total_price_without_vat += $isdoc->lines[$i]->LineExtensionAmount;
$total_price += $isdoc->lines[$i]->LineExtensionAmountTaxInclusive;

// tax rates recapitulation
$tax_rate = 21;
$isdoc->taxTotal[$tax_rate] = new \stdClass;
$isdoc->taxTotal[$tax_rate]->TaxPercent = 21;
// částka s daní v sazbě v T.M.
$isdoc->taxTotal[$tax_rate]->TaxableAmount = $taxable_amount[$tax_rate];
// daň v sazbě v T.M.
$isdoc->taxTotal[$tax_rate]->TaxInclusiveAmount = $tax_inclusive_amount[$tax_rate];
// základ v sazbě v T.M.
$isdoc->taxTotal[$tax_rate]->TaxAmount = $tax_amount[$tax_rate];

// celková daň v T.M. po odečtení odúčtovaných záloh
$isdoc->TaxAmount =  $total_tax_amount;

// celkové částky, vždy bez daně v tuzemské a cizí měně a s daní totéž
$isdoc->totalPrice = $total_price;
$isdoc->totalPriceWithoutVat = $total_price_without_vat;

// $isdoc->output();

$isdoc->outputFile = 0;

// pretend more documents
$isdoc2 = clone $isdoc;

$isdocZip = new IsdocZipArchive('test.zip');
$isdocZip->addFileString('FA-1.isdoc', $isdoc->output(), $isdoc->DocumentTypeName);
$isdocZip->addFileString('FA-2.isdoc', $isdoc->output(), $isdoc->DocumentTypeName);
$isdocZip->output();
