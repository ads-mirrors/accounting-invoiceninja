<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Services\EDocument\Standards\Validation\Verifactu;

use App\Services\EDocument\Standards\Validation\EntityLevelInterface;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Support\Facades\App;

//@todo - need to implement a rule set for verifactu for validation
class EntityLevel implements EntityLevelInterface
{
    private array $errors = [];

    private array $client_fields = [
        // 'address1',
        // 'city',
        // 'state',
        // 'postal_code',
        'vat_number',
        'country_id',
    ];

    private array $company_settings_fields = [
        // 'address1',
        // 'city',
        // 'state',
        // 'postal_code',
        'vat_number',
        'country_id',
    ];

    public function __construct(){}


    private function init(string $locale): self
    {

        App::forgetInstance('translator');
        $t = app('translator');
        App::setLocale($locale);

        return $this;

    }

    public function checkClient(Client $client): array
    {
                
        $this->init($client->locale());
        $this->errors['client'] = $this->testClientState($client);
        $this->errors['passes'] = count($this->errors['client']) == 0;

        return $this->errors;

    }

    public function checkCompany(Company $company): array
    {
        
        $this->init($company->locale());
        $this->errors['company'] = $this->testCompanyState($company);
        $this->errors['passes'] = count($this->errors['company']) == 0;

        return $this->errors;

    }

    public function checkInvoice(Invoice $invoice): array
    {
                
        $this->init($invoice->client->locale());

        $this->errors['invoice'] = [];
        $this->errors['client'] = $this->testClientState($invoice->client);
        $this->errors['company'] = $this->testCompanyState($invoice->client); // uses client level settings which is what we want

        if (count($this->errors['client']) > 0) {

            $this->errors['passes'] = false;
            return $this->errors;

        }


        // $p = new Peppol($invoice);

        // $xml = false;

        // try {
        //     $xml = $p->run()->toXml();
             
        //     if (count($p->getErrors()) >= 1) {

        //         foreach ($p->getErrors() as $error) {
        //             $this->errors['invoice'][] = $error;
        //         }
        //     }

        // } catch (PeppolValidationException $e) {
        //     $this->errors['invoice'] = ['field' => $e->getInvalidField(), 'label' => $e->getInvalidField()];
        // } catch (\Throwable $th) {

        // }

        // if ($xml) {
        //     // Second pass through the XSLT validator
        //     $xslt = new XsltDocumentValidator($xml);
        //     $errors = $xslt->validate()->getErrors();

        //     if (isset($errors['stylesheet']) && count($errors['stylesheet']) > 0) {
        //         $this->errors['invoice'] = array_merge($this->errors['invoice'], $errors['stylesheet']);
        //     }

        //     if (isset($errors['general']) && count($errors['general']) > 0) {
        //         $this->errors['invoice'] = array_merge($this->errors['invoice'], $errors['general']);
        //     }

        //     if (isset($errors['xsd']) && count($errors['xsd']) > 0) {
        //         $this->errors['invoice'] = array_merge($this->errors['invoice'], $errors['xsd']);
        //     }
        // }
        
        // $this->checkNexus($invoice->client);

        $this->errors['passes'] = count($this->errors['invoice']) == 0 && count($this->errors['client']) == 0 && count($this->errors['company']) == 0;

        return $this->errors;

    }

    private function testClientState(Client $client): array
    {
                
        $errors = [];

        foreach ($this->client_fields as $field) {

            if ($this->validString($client->{$field})) {
                continue;
            }

            if ($field == 'country_id' && $client->country_id >= 1) {
                continue;
            }

            $errors[] = ['field' => $field, 'label' => ctrans("texts.{$field}")];

        }

        //If not an individual, you MUST have a VAT number if you are in the EU
        if (!$this->validString($client->vat_number)) {
            $errors[] = ['field' => 'vat_number', 'label' => ctrans("texts.vat_number")];
        }

        //Primary contact email is present.
        if ($client->present()->email() == 'No Email Set') {
            $errors[] = ['field' => 'email', 'label' => ctrans("texts.email")];
        }

        $delivery_network_supported = $client->checkDeliveryNetwork();

        if (is_string($delivery_network_supported)) {
            $errors[] = ['field' => ctrans("texts.country"), 'label' => $delivery_network_supported];
        }

        return $errors;

    }

    private function validString(?string $string): bool
    {
        return iconv_strlen($string) >= 1;
    }

    public function isValidSpanishVAT(string $vat): bool
    {
        $vat = strtoupper(trim($vat));

        // Quick format check
        if (!preg_match('/^[A-Z]\d{7}[A-Z0-9]$|^\d{8}[A-Z]$|^[XYZ]\d{7}[A-Z]$/', $vat)) {
            return false;
        }

        // NIF (individuals)
        if (preg_match('/^\d{8}[A-Z]$/', $vat)) {
            $number = (int)substr($vat, 0, 8);
            $letter = substr($vat, -1);
            $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
            return $letter === $letters[$number % 23];
        }

        // NIE (foreigners)
        if (preg_match('/^[XYZ]\d{7}[A-Z]$/', $vat)) {
            $replace = ['X' => '0', 'Y' => '1', 'Z' => '2'];
            $number = (int)($replace[$vat[0]] . substr($vat, 1, 7));
            $letter = substr($vat, -1);
            $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
            return $letter === $letters[$number % 23];
        }

        // CIF (companies)
        if (preg_match('/^[ABCDEFGHJKLMNPQRSUVW]\d{7}[0-9A-J]$/', $vat)) {
            $controlLetter = substr($vat, -1);
            $digits = substr($vat, 1, 7);

            $sumEven = 0;
            $sumOdd = 0;
            for ($i = 0; $i < 7; $i++) {
                $n = (int)$digits[$i];
                if ($i % 2 === 0) { // Odd positions (0-based index)
                    $n = $n * 2;
                    if ($n > 9) {
                        $n = floor($n / 10) + ($n % 10);
                    }
                    $sumOdd += $n;
                } else {
                    $sumEven += $n;
                }
            }

            $total = $sumEven + $sumOdd;
            $controlDigit = (10 - ($total % 10)) % 10;
            $controlChar = 'JABCDEFGHI'[$controlDigit];

            $firstLetter = $vat[0];
            if (strpos('PQRSW', $firstLetter) !== false) {
                return $controlLetter === $controlChar; // Must be letter
            } elseif (strpos('ABEH', $firstLetter) !== false) {
                return $controlLetter == $controlDigit; // Must be digit
            } else {
                return ($controlLetter == $controlDigit || $controlLetter === $controlChar);
            }
        }

        return false;
    }

// // Example usage:
// var_dump(isValidSpanishVAT("12345678Z")); // true
// var_dump(isValidSpanishVAT("B12345674")); // true (CIF example)
// var_dump(isValidSpanishVAT("X1234567L")); // true (NIE)

}