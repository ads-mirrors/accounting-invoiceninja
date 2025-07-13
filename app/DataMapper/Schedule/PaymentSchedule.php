<?php

namespace App\DataMapper\Schedule;

class PaymentSchedule
{
    /**
     * The template name
     *
     * @var string
     */
    public string $template = 'payment_schedule';

    /**
     * 
     * @var array(
     *  'date' => string,
     *  'amount' => float,
     *  'percentage' => float
     * )
     */
    public array $schedule = [];

    /**
     * The invoice id
     *
     * @var string
     */
    public string $invoice_id = '';

    /**
     * Whether to auto bill the invoice
     *
     * @var bool
     */
    public bool $auto_bill = false;
}