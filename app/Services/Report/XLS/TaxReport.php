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
            ->createSummarySheet()
            ->createInvoiceSummarySheetAccrual()
            ->createInvoiceSummarySheetCash()
            ->createInvoiceItemSummarySheetAccrual()
            ->createInvoiceItemSummarySheetCash();
            // ->createGroupedTaxSummarySheetAccrual()
            // ->createGroupedTaxSummarySheetCash();

        return $this;

    }

    public function createSummarySheet()
    {
        
        $worksheet = $this->spreadsheet->getActiveSheet();
        $worksheet->setTitle(ctrans('texts.tax_summary'));

        return $this;
    }

    // All invoices within a time period - regardless if they are paid or not!
    public function createInvoiceSummarySheetAccrual()
    {
        
        $newWorksheet = $this->spreadsheet->createSheet();
        $newWorksheet->setTitle(ctrans('texts.invoice')." ".ctrans('texts.cash_vs_accrual'));
        $newWorksheet->fromArray($this->data['invoices'], null, 'A1');

        return $this;
    }

    // All paid invoices within a time period
    public function createInvoiceSummarySheetCash()
    {
        $cash_invoices = collect($this->data['invoices'])->filter(function($invoice){
            return $invoice[3] != 0;
        })->toArray();
        
        $newWorksheet = $this->spreadsheet->createSheet();
        $newWorksheet->setTitle(ctrans('texts.invoice')." ".ctrans('texts.cash_accounting'));
        $newWorksheet->fromArray($cash_invoices, null, 'A1');
        return $this;
    }

    public function createInvoiceItemSummarySheetAccrual()
    {

        $newWorksheet = $this->spreadsheet->createSheet();
        $newWorksheet->setTitle(ctrans('texts.invoice_item')." ".ctrans('texts.cash_vs_accrual'));
        $newWorksheet->fromArray($this->data['invoice_items'], null, 'A1');

        return $this;
    }

    public function createInvoiceItemSummarySheetCash()
    {

        $cash_invoice_items = collect($this->data['invoice_items'])->filter(function($invoice_item){
            return $invoice_item[3] != 0;
        })->toArray();

        $newWorksheet = $this->spreadsheet->createSheet();
        $newWorksheet->setTitle(ctrans('texts.invoice_item')." ".ctrans('texts.cash_accounting'));
        $newWorksheet->fromArray($cash_invoice_items, null, 'A1');

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
                ];
            }

        }

        return $this;
    }

    public function getXlsFile()
    {
       
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save('/tmp/tax_report.xlsx');

        // return $this->spreadsheet;
        // Use output buffering to capture the file content
        // ob_start();
        // $this->spreadsheet->save('php://output');
        // $fileContent = ob_get_clean();
        
        // return $fileContent;
    }
}
