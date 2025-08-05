<?php

namespace App\Services\Report\XLS;

use Carbon\Carbon;
use App\Utils\Ninja;
use App\Models\Company;
use App\Libraries\MultiDB;
use Illuminate\Support\Facades\App;
use App\Services\Report\TaxSummaryReport;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Invoice;

class TaxReport
{
    private Spreadsheet $spreadsheet;

    private array $data = [];

    private string $currency_format;

    private string $number_format;

    public function __construct(public Company $company, public TaxSummaryReport $tsr, public Builder $query)
    {
    }

    public function run()
    {

        MultiDB::setDb($this->company->db);
        App::forgetInstance('translator');
        App::setLocale($this->company->locale());
        $t = app('translator');
        $t->replace(Ninja::transformTranslations($this->company->settings));

        $this->spreadsheet = new Spreadsheet();
        
        $this->buildData()
                ->setCurrencyFormat()
                ->createSummarySheet()
                ->createInvoiceSummarySheetAccrual()
                ->createInvoiceSummarySheetCash()
                ->createInvoiceItemSummarySheetAccrual()
                ->createInvoiceItemSummarySheetCash();


        return $this;

    }

    public function setCurrencyFormat()
    {
        $currency = $this->company->currency();

        $formatted = number_format(9990.00, $currency->precision, $currency->decimal_separator, $currency->thousand_separator);
        $formatted = str_replace('9', '#', $formatted);
        $this->number_format = $formatted;
        
        $formatted = "{$currency->symbol}{$formatted}";
        $this->currency_format = $formatted;

        return $this;
    }

    public function createSummarySheet()
    {
        
        $worksheet = $this->spreadsheet->getActiveSheet();
        $worksheet->setTitle(ctrans('texts.tax_summary'));

        // Add summary data and formatting here if needed
        // For now, this sheet is empty but could be populated with summary statistics

        return $this;
    }

    // All invoices within a time period - regardless if they are paid or not!
    public function createInvoiceSummarySheetAccrual()
    {
        
        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice')." ".ctrans('texts.cash_vs_accrual'));
        $worksheet->fromArray($this->data['invoices'], null, 'A1');

        $worksheet->getStyle('B:B')->getNumberFormat()->setFormatCode($this->company->date_format()); // Invoice date column
        $worksheet->getStyle('C:C')->getNumberFormat()->setFormatCode($this->currency_format); // Invoice total column
        $worksheet->getStyle('D:D')->getNumberFormat()->setFormatCode($this->currency_format); // Paid amount column
        $worksheet->getStyle('E:E')->getNumberFormat()->setFormatCode($this->currency_format); // Total taxes column
        $worksheet->getStyle('F:F')->getNumberFormat()->setFormatCode($this->currency_format); // Tax paid column

        return $this;
    }

    // All paid invoices within a time period
    public function createInvoiceSummarySheetCash()
    {
        $cash_invoices = collect($this->data['invoices'])->filter(function($invoice){
            return $invoice[3] != 0;
        })->toArray();
        
        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice')." ".ctrans('texts.cash_accounting'));
        $worksheet->fromArray($cash_invoices, null, 'A1');        
        $worksheet->getStyle('B:B')->getNumberFormat()->setFormatCode($this->company->date_format()); // Invoice date column
        $worksheet->getStyle('C:C')->getNumberFormat()->setFormatCode($this->currency_format); // Invoice total column
        $worksheet->getStyle('D:D')->getNumberFormat()->setFormatCode($this->currency_format); // Paid amount column
        $worksheet->getStyle('E:E')->getNumberFormat()->setFormatCode($this->currency_format); // Total taxes column
        $worksheet->getStyle('F:F')->getNumberFormat()->setFormatCode($this->currency_format); // Tax paid column

        return $this;
    }

    public function createInvoiceItemSummarySheetAccrual()
    {

        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice_item')." ".ctrans('texts.cash_vs_accrual'));
        $worksheet->fromArray($this->data['invoice_items'], null, 'A1');

        $worksheet->getStyle('B:B')->getNumberFormat()->setFormatCode($this->company->date_format()); // Invoice date column
        $worksheet->getStyle('C:C')->getNumberFormat()->setFormatCode($this->currency_format); // Invoice total column
        $worksheet->getStyle('D:D')->getNumberFormat()->setFormatCode($this->currency_format); // Paid amount column
        $worksheet->getStyle('F:F')->getNumberFormat()->setFormatCode($this->number_format."%"); // Tax rate column
        $worksheet->getStyle('G:G')->getNumberFormat()->setFormatCode($this->currency_format); // Tax amount column
        $worksheet->getStyle('H:H')->getNumberFormat()->setFormatCode($this->currency_format); // Tax paid column
        $worksheet->getStyle('I:I')->getNumberFormat()->setFormatCode($this->currency_format); // Taxable amount column
        // Column J (tax_nexus) is text, so no special formatting needed

        return $this;
    }

    public function createInvoiceItemSummarySheetCash()
    {

        $cash_invoice_items = collect($this->data['invoice_items'])->filter(function($invoice_item){
            return $invoice_item[3] != 0;
        })->toArray();

        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice_item')." ".ctrans('texts.cash_accounting'));
        $worksheet->fromArray($cash_invoice_items, null, 'A1');

        $worksheet->getStyle('B:B')->getNumberFormat()->setFormatCode($this->company->date_format()); // Invoice date column
        $worksheet->getStyle('C:C')->getNumberFormat()->setFormatCode($this->currency_format); // Invoice total column
        $worksheet->getStyle('D:D')->getNumberFormat()->setFormatCode($this->currency_format); // Paid amount column
        $worksheet->getStyle('F:F')->getNumberFormat()->setFormatCode($this->number_format."%"); // Tax rate column
        $worksheet->getStyle('G:G')->getNumberFormat()->setFormatCode($this->currency_format); // Tax amount column
        $worksheet->getStyle('H:H')->getNumberFormat()->setFormatCode($this->currency_format); // Tax paid column
        $worksheet->getStyle('I:I')->getNumberFormat()->setFormatCode($this->currency_format); // Taxable amount column
        // Column J (tax_nexus) is text, so no special formatting needed

        return $this;
    }

    private function buildData()
    {

        $start_date_instance = Carbon::parse($this->tsr->start_date);
        $end_date_instance = Carbon::parse($this->tsr->end_date);

        $this->data['invoices'] = [];
        $this->data['invoices'][] = [
            ctrans('texts.invoice_number'),
            ctrans('texts.invoice_date'),
            ctrans('texts.invoice_total'),
            ctrans('texts.paid'),
            ctrans('texts.total_taxes'),
            ctrans('texts.tax_paid')
        ];

        $this->data['invoice_items'] = [];
        $this->data['invoice_items'][] = [
            ctrans('texts.invoice_number'),
            ctrans('texts.invoice_date'),
            ctrans('texts.invoice_total'),
            ctrans('texts.paid'),
            ctrans('texts.tax_name'),
            ctrans('texts.tax_rate'),
            ctrans('texts.tax_amount'),
            ctrans('texts.tax_paid'),
            ctrans('texts.taxable_amount'),
            ctrans('texts.tax_nexus'),
        ];

        $offset = $this->company->timezone_offset();

        /** @var Invoice $invoice */
        foreach($this->query->cursor() as $invoice){

            $calc = $invoice->calc();
            //Combine the line taxes with invoice taxes here to get a total tax amount
            $taxes = array_merge($calc->getTaxMap()->merge($calc->getTotalTaxMap())->toArray());
            
            $payment_amount = 0;

            foreach ($invoice->payments()->get() as $payment) {

                if($payment->pivot->created_at->addSeconds($offset)->isBetween($start_date_instance,$end_date_instance)){
                    $payment_amount += ($payment->pivot->amount - $payment->pivot->refunded);
                }
            }

            $payment_amount = round($payment_amount,2);
            $invoice_amount = round($invoice->amount,2);
            
            $total_taxes = round($invoice->total_taxes,2);

            $pro_rata_payment_ratio = $payment_amount != 0 ? ($payment_amount/$invoice_amount) : 0;
            $total_taxes = $payment_amount != 0 ? ($payment_amount/$invoice_amount) * $invoice->total_taxes : 0;
            $taxable_amount = $calc->getNetSubtotal();

                $this->data['invoices'][] = [
                    $invoice->number,
                    $invoice->date,
                    $invoice_amount,
                    $payment_amount,
                    $total_taxes,
                    $taxable_amount,
                ];


            foreach($taxes as $tax){

                $this->data['invoice_items'][] = [
                    $invoice->number,
                    $invoice->date,
                    $invoice_amount,
                    $payment_amount,
                    $tax['name'],
                    $tax['tax_rate'],
                    $tax['total'],
                    $tax['total'] * $pro_rata_payment_ratio,
                    $tax['base_amount'] ?? $calc->getNetSubtotal(),
                    $tax['nexus'] ?? '',
                ];
            }

        }

        return $this;
    }

    public function getXlsFile()
    {
       
        $tempFile = tempnam(sys_get_temp_dir(), 'tax_report_');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save($tempFile);

        // $writer->save('/home/david/ttx.xslx');
        // Read file content
        $fileContent = file_get_contents($tempFile);

        // nlog($tempFile);
        // Clean up temp file
        // unlink($tempFile);

        return $fileContent;

    }
}
