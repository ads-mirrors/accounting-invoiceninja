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

namespace App\Services\Report;

use App\Models\User;
use App\Utils\Ninja;
use App\Utils\Number;
use App\Models\Client;
use League\Csv\Writer;
use App\Models\Company;
use App\Models\Invoice;
use App\Libraries\MultiDB;
use App\Export\CSV\BaseExport;
use App\Utils\Traits\MakesDates;
use Illuminate\Support\Facades\App;
use App\Services\Template\TemplateService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Invoice;
use App\Listeners\Invoice\InvoiceTransactionEventEntry;
use App\Models\TransactionEvent;


class TaxPeriodReport extends BaseExport
{
    use MakesDates;
 
    private Spreadsheet $spreadsheet;

    private array $data = [];

    private string $currency_format;

    private string $number_format;

    //is_income_billed = accrual
    //!is_invoice_billed = cash
    /**
        @param array $input
        [
            'date_range',
            'start_date',
            'end_date',
            'client_id',
        ]
    */
    public function __construct(public Company $company, public array $input)
    {
    }

    public function run()
    {

        MultiDB::setDb($this->company->db);
        App::forgetInstance('translator');
        App::setLocale($this->company->locale());
        $t = app('translator');
        $t->replace(Ninja::transformTranslations($this->company->settings));

        $this->calculateDateRange()
                ->initializeData()
                ->buildData();

    }
    
    /**
     * initializeData
     * 
     * Ensure our dataset has the appropriate transaction events.
     *
     * @return self
     */
    private function initializeData(): self
    {
        Invoice::withTrashed()
            ->where('company_id', $this->company->id)
            ->where('is_deleted', 0)
            ->whereIn('status_id', [2,3,4,5])
            ->whereBetween('date', ['1970-01-01', now()->subMonth()->endOfMonth()->format('Y-m-d')])
            ->whereDoesntHave('transaction_events')
            ->cursor()
            ->each(function($invoice){

                if($invoice->status_id == Invoice::STATUS_SENT){
                    (new InvoiceTransactionEventEntry())->run($invoice, \Carbon\Carbon::parse($invoice->date)->endOfMonth()->format('Y-m-d'));
                }
                elseif(in_array($invoice->status_id, [Invoice::STATUS_PAID, Invoice::STATUS_PARTIAL])){
                    
                }
            });

            return $this;
    }

    private function resolveQuery()
    {
        $query = Invoice::query()
            ->withTrashed()
            ->with('transaction_events')
            ->where('company_id', $this->company->id)
            ->where('is_deleted', 0);

        if($this->input['is_income_billed']) //acrrual
        {
            $query->whereIn('status_id', [2,3,4])
                    ->whereHas('transaction_events', function($query){
                        $query->where('event_id', TransactionEvent::INVOICE_UPDATED)
                            ->whereBetween('period', [$this->start_date, $this->end_date]);
                    });
        }
        else //cash
        {
            $query->whereIn('status_id', [3,4])
                ->whereHas('transaction_events', function($query){
                    $query->where('event_id', TransactionEvent::PAYMENT_CASH)
                        ->whereBetween('period', [$this->start_date, $this->end_date]);
                });
        }

        $query->orderBy('balance', 'desc');

        return $query;
    }
    
    /**
     * calculateDateRange
     *
     * We only support dates as of the end of the last month.
     * @return self
     */
    private function calculateDateRange(): self
    {

        switch ($date_range) {
            case 'last7':
            case 'last30':
            case 'this_month':
            case 'last_month':
                $this->start_date = now()->startOfMonth()->subMonth()->format('Y-m-d');
                $this->end_date = now()->startOfMonth()->subMonth()->endOfMonth()->format('Y-m-d');
            case 'this_quarter':
                $this->start_date = (new \Carbon\Carbon('0 months'))->startOfQuarter()->format('Y-m-d');
                $this->end_date = (new \Carbon\Carbon('0 months'))->endOfQuarter()->format('Y-m-d');
            case 'last_quarter':
                $this->start_date = (new \Carbon\Carbon('-3 months'))->startOfQuarter()->format('Y-m-d');
                $this->end_date = (new \Carbon\Carbon('-3 months'))->endOfQuarter()->format('Y-m-d');
            case 'last365_days':
                $this->start_date = now()->startOfDay()->subDays(365)->format('Y-m-d');
                $this->end_date = now()->startOfDay()->format('Y-m-d');
            case 'this_year':

                $first_month_of_year = $this->company->first_month_of_year ?? 1;
                $fin_year_start = now()->createFromDate(now()->year, $first_month_of_year, 1);

                if (now()->lt($fin_year_start)) {
                    $fin_year_start->subYearNoOverflow();
                }

                $this->start_date = $fin_year_start->format('Y-m-d');
                $this->end_date = $fin_year_start->copy()->addYear()->subDay()->format('Y-m-d');
            case 'last_year':

                $first_month_of_year = $this->company->first_month_of_year ?? 1;
                $fin_year_start = now()->createFromDate(now()->year, $first_month_of_year, 1);
                $fin_year_start->subYearNoOverflow();

                if (now()->subYear()->lt($fin_year_start)) {
                    $fin_year_start->subYearNoOverflow();
                }

                $this->start_date = $fin_year_start->format('Y-m-d');
                $this->end_date = $fin_year_start->copy()->addYear()->subDay()->format('Y-m-d');
            case 'custom':
                
                try {
                    $custom_start_date = Carbon::parse($this->input['start_date']);
                    $custom_end_date = Carbon::parse($this->input['end_date']);
                } catch (\Exception $e) {
                    $custom_start_date = now()->startOfYear();
                    $custom_end_date = now();
                }

                $this->start_date = $custom_start_date->format('Y-m-d');
                $this->end_date = $custom_end_date->format('Y-m-d');
            case 'all':
            default:
                $this->start_date = now()->startOfYear()->format('Y-m-d');
                $this->end_date = now()->format('Y-m-d');
        }

        return $this;
    }

    public function accrual()
    {

    }

    public function cash()
    {

    }

    public function getXlsFile()
    {
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
    public function createInvoiceSummarySheet()
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

    public function createInvoiceItemSummarySheet()
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


    private function buildData()
    {

        $query = $this->resolveQuery();

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


        $query->cursor()
            ->each(function($invoice){

                /** @var TransactionEvent $invoice_state */
                $invoice_state = $invoice->transaction_events()->where('event_id', TransactionEvent::INVOICE_UPDATED)->where('period', now()->endOfMonth()->format('Y-m-d'))->orderBy('timestamp', 'desc')->first();
                $payment_state = $invoice->transaction_events()->where('event_id', TransactionEvent::PAYMENT_CASH)->where('period', now()->endOfMonth()->format('Y-m-d'))->orderBy('timestamp', 'desc')->first();
                $adjustments = $invoice->transaction_events()->whereIn('event_id',[TransactionEvent::PAYMENT_REFUNDED, TransactionEvent::PAYMENT_DELETED])->where('period', now()->endOfMonth()->format('Y-m-d'))->get();

                
                if($invoice_state && $invoice_state->event_id == TransactionEvent::INVOICE_UPDATED){
                    $this->data['accrual']['invoices'][] = [
                        $invoice->number,
                        $invoice->date,
                        $invoice->amount,
                        
                        $invoice_state->metadata->tax_report->payment_history?->sum('amount') ?? 00,
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
                        
                        $payment_state->metadata->tax_report->payment_history?->sum('amount') ?? 0,
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
