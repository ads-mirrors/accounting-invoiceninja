/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

import { wait, instant } from '../wait'; 

class ForteAuthorizeACH {
    constructor(apiLoginId) {
        this.apiLoginId = apiLoginId;
    }

    handleAuthorization = () => {
        var account_number = document.getElementById('account-number').value;
        var routing_number = document.getElementById('routing-number').value;

        var data = {
            api_login_id: this.apiLoginId,
            account_number: account_number,
            routing_number: routing_number,
            account_type: 'checking',
        };

        let payNowButton = document.getElementById('pay-now');

        if (payNowButton) {
            document.getElementById('pay-now').disabled = true;
            document.querySelector('#pay-now > svg').classList.remove('hidden');
            document.querySelector('#pay-now > span').classList.add('hidden');
        }
        // console.log(data);
        forte
            .createToken(data)
            .success(this.successResponseHandler)
            .error(this.failedResponseHandler);
        return false;
    };

    successResponseHandler = (response) => {
        document.getElementById('payment_token').value = response.onetime_token;
        document.getElementById('last_4').value = response.last_4;
        document.getElementById('account_holder_name').value = document.getElementById('account-holder-name').value;
        document.getElementById('server_response').submit();

        return false;
    };

    failedResponseHandler = (response) => {
        var errors =
            '<div class="alert alert-failure mb-4"><ul><li>' +
            response.response_description +
            '</li></ul></div>';
        document.getElementById('forte_errors').innerHTML = errors;
        document.getElementById('pay-now').disabled = false;
        document.querySelector('#pay-now > svg').classList.add('hidden');
        document.querySelector('#pay-now > span').classList.remove('hidden');

        return false;
    };


    completePaymentUsingToken() {
        
        let payNowButton = document.getElementById('pay-now');
        this.payNowButton = payNowButton;

        this.payNowButton.disabled = true;

        this.payNowButton.querySelector('svg').classList.remove('hidden');
        this.payNowButton.querySelector('span').classList.add('hidden');

        document.getElementById('server_response').submit();

        return false;
    }

    handle = () => {


        Array.from(
            document.getElementsByClassName('toggle-payment-with-token')
        ).forEach((element) =>
            element.addEventListener('click', (element) => {
                document
                    .getElementById('forte-payment-container')
                    .classList.add('hidden');

                    
                document.querySelector('input[name=token]').value =
                    element.target.dataset.token;
            })
        );

        document
            .getElementById('toggle-payment-with-new-bank-account')
            .addEventListener('click', (element) => {
                document
                    .getElementById('forte-payment-container')
                    .classList.remove('hidden');
               
                document.querySelector('input[name=token]').value = '';
            });

        let payNowButton = document.getElementById('pay-now');

        if (payNowButton) {
            payNowButton.addEventListener('click', (e) => {

                let tokenInput =
                    document.querySelector('input[name=token]');

                console.log(tokenInput.value);

                if (tokenInput.value) {
                    return this.completePaymentUsingToken();
                }

                console.log("whoopsie");

                this.handleAuthorization();
            });
        }

        return this;
    };
}

function boot() {
    const apiLoginId = document.querySelector(
        'meta[name="forte-api-login-id"]'
    ).content;
    
    /** @handle */
    new ForteAuthorizeACH(apiLoginId).handle();
}

instant() ? boot() : wait('#force-ach-payment').then(() => boot());
