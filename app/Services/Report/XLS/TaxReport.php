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
use App\Listeners\Invoice\InvoiceTransactionEventEntry;
use App\Models\TransactionEvent;

class TaxReport
{
    private Spreadsheet $spreadsheet;

    private array $data = [];

    private string $currency_format;

    private string $number_format;

    public function __construct(public Company $company, private string $start_date, private string $end_date)
    {
    }

    public function run()
    {

        $this->start_date = Carbon::parse($this->start_date);
        $this->end_date = Carbon::parse($this->end_date);

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
        $worksheet->fromArray($this->data['accrual']['invoices'], null, 'A1');

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
        
        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice')." ".ctrans('texts.cash_accounting'));

        $worksheet->fromArray($this->data['cash']['invoices'], null, 'A1');
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
        $worksheet->fromArray($this->data['accrual']['invoice_items'], null, 'A1');

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

        $worksheet = $this->spreadsheet->createSheet();
        $worksheet->setTitle(ctrans('texts.invoice_item')." ".ctrans('texts.cash_accounting'));
        $worksheet->fromArray($this->data['cash']['invoice_items'], null, 'A1');

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

        $start_date_instance = $this->start_date;
        $end_date_instance = $this->end_date;

        $this->data['invoices'] = [];
        $this->data['invoices'][] =

        $invoice_headers = [
            ctrans('texts.invoice_number'),
            ctrans('texts.invoice_date'),
            ctrans('texts.invoice_total'),
            ctrans('texts.paid'),
            ctrans('texts.total_taxes'),
            ctrans('texts.tax_paid'),
            ctrans('texts.notes')
        ];

        $invoice_item_headers = [
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


        $this->data['accrual']['invoices'] = [$invoice_headers];
        $this->data['cash']['invoices'] = [$invoice_headers];
        $this->data['accrual']['invoice_items']     = [$invoice_item_headers];
        $this->data['cash']['invoice_items'] = [$invoice_item_headers];


        Invoice::withTrashed()
            ->with('transaction_events')
            ->where('company_id', $this->company->id)
            ->whereHas('transaction_events', function ($query){
                return $query->where('period', now()->endOfMonth()->format('Y-m-d'));
            })
            ->cursor()
            ->each(function($invoice){

                if($invoice->transaction_events->count() == 0){
                    (new InvoiceTransactionEventEntry())->run($invoice);
                    $invoice->load('transaction_events');
                }

                nlog($invoice->transaction_events->toArray());
                /** @var TransactionEvent $invoice_state */
                $invoice_state = $invoice->transaction_events()->where('event_id', TransactionEvent::INVOICE_UPDATED)->where('period', now()->endOfMonth()->format('Y-m-d'))->orderBy('timestamp', 'desc')->first();
                $payment_state = $invoice->transaction_events()->where('event_id', TransactionEvent::PAYMENT_CASH)->where('period', now()->endOfMonth()->format('Y-m-d'))->orderBy('timestamp', 'desc')->first();
                $adjustments = $invoice->transaction_events()->whereIn('event_id',[TransactionEvent::PAYMENT_REFUNDED, TransactionEvent::PAYMENT_DELETED])->where('period', now()->endOfMonth()->format('Y-m-d'))->get();

               
                if($invoice_state && $invoice_state->event_id == TransactionEvent::INVOICE_UPDATED){
                    $this->data['accrual']['invoices'][] = [
                        $invoice->number,
                        $invoice->date,
                        $invoice->amount,
                        $invoice_state->invoice_paid_to_date,
                        $invoice_state->metadata->tax_report->tax_summary->total_taxes,
                        $invoice_state->metadata->tax_report->tax_summary->total_paid,
                        'payable',
                    ];
                }

                if($payment_state && $payment_state->event_id == TransactionEvent::PAYMENT_CASH){
                
                    $this->data['cash']['invoices'][] = [
                        $invoice->number,
                        $invoice->date,
                        $invoice->amount,
                        $payment_state->invoice_paid_to_date,
                        $payment_state->metadata->tax_report->tax_summary->total_taxes,
                        $payment_state->metadata->tax_report->tax_summary->total_paid,
                        'payable',
                    ];

                }
                    $_adjustments = [];

                    foreach($adjustments as $adjustment){
                        $_adjustments[] = [
                            $invoice->number,
                            $invoice->date,
                            $invoice->amount,
                            $invoice_state->invoice_paid_to_date,
                            $invoice_state->metadata->tax_report->tax_summary->total_taxes,
                            $invoice_state->metadata->tax_report->tax_summary->adjustment,
                            'adjustment',
                        ];
                    }

                    $this->data['accrual']['invoices'] = array_merge($this->data['accrual']['invoices'], $_adjustments);
                    $this->data['cash']['invoices'] = array_merge($this->data['cash']['invoices'], $_adjustments);

            });

            return $this;
    }


    public function getXlsFile()
    {
       
        $tempFile = tempnam(sys_get_temp_dir(), 'tax_report_');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save($tempFile);

        // $writer->save('/home/david/ttx.xlsx');
        // Read file content
        $fileContent = file_get_contents($tempFile);

        // nlog($tempFile);
        // Clean up temp file
        // unlink($tempFile);

        return $fileContent;

    }
}
