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

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\ClientContact;
use App\Utils\Traits\MakesHash;

class SwitchCompanyController extends Controller
{
    use MakesHash;

    public function __invoke(string $contact)
    {
        $client_contact = ClientContact::query()
                                       ->where('email', auth()->user()->email)
                                       ->where('id', $this->transformKeys($contact))
                                       ->firstOrFail();
                               
/* 2025-04-04 - Session resets - Stage 1
auth()->guard('contact')->loginUsingId($client_contact->id, true);
request()->session()->regenerate();
return redirect('/client/dashboard');
*/
    
        request()->session()->invalidate();
                                       
        auth()->guard('contact')->loginUsingId($client_contact->id, true);

        request()->session()->regenerate();

        if(\App\Utils\Ninja::isHosted()){
            $domain = $client_contact->company->domain()."/client/dashboard";
            return redirect($domain);
        }
        else{
            return redirect('/client/dashboard');
        }
    }
}
