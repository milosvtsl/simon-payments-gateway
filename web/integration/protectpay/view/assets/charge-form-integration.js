/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/30/2016
 * Time: 11:11 PM
 */


// Initialize
document.addEventListener("DOMContentLoaded", function(event) {


    var URL_TEMP_TOKEN = 'integration/protectpay/token.php';


    document.addEventListener("order:submit", onOrderFormSubmit);
    console.info("ProPay Charge Form Integration (SPI) Loaded");

    function onOrderFormSubmit(e) {
        var form = e.target.form || e.target;
        e.preventDefault();

        console.info("Requesting Temp Token...");
        requestTempToken(e, form);
    }


    function requestTempToken(e, form) {

        var url = URL_TEMP_TOKEN;
        var payee_name_full =
            form.payee_full_name
            ? form.payee_full_name.value
            : form.payee_first_name.value + ' ' + form.payee_last_name.value;
        var POST = 'payee_full_name=' + payee_name_full;
        POST += '&integration_uid=' + form.integration_uid;
        POST += '&merchant_uid=' + form.merchant_uid;

        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", url, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.onreadystatechange = function () {
            if (xhttp.readyState === 4) {
                var response = xhttp.responseText;

                var objJSON = eval("(function(){return " + response + ";})()");
                var CID = objJSON.CID;
                var SettingsCipher = objJSON.SettingsCipher;
                if(!CID || !SettingsCipher)
                    throw new Error("Invalid CID/SettingsCipher: " + response);

                processTransaction(e, form, CID, SettingsCipher);
            }
        };
        xhttp.send(POST);                                                            
    }
    
    function processTransaction(e, form, CID, SettingsCipher) {
        var payload = 'CID=' + CID
            + '&SettingsCipher=' + encodeURIComponent(SettingsCipher);
        var avsPayload = '&Address1=' + form.payee_address.value
            + '&Address2=' + form.payee_address2.value
            + '&Address3=' + (form.payee_address3 ? form.payee_address3.value : '')
            + '&City=' + form.payee_address3.city.value
            + '&State=' + form.payee_address3.state.value
            + '&PostalCode=' + form.payee_zipcode.value
            + '&Country=' + (form.payee_country ? form.payee_country.value : 'USA')


        var entry_mode = form.entry_mode.value.toLowerCase() || 'keyed';

        var payee_name_full = form.payee_first_name.value + ' ' + form.payee_last_name.value;

        // Disable unused payment methods
        switch(entry_mode) {
            case 'check':
                var achPayload =
                    '&NameOnBankAccount=' + (form.check_account_name ? form.check_account_name.value : payee_name_full)
                    + '&BankName=' + (form.check_account_bank_name ? form.check_account_bank_name.value : '')
                    + '&RoutingNumber=' + form.check_routing_number.value
                    + '&Bank AccountNumber=' + form.check_account_number.value
                    + '&ExBankAccountTypepYear=' + form.check_account_type.value
                    + '&Bank CountryCode=' + (form.check_account_country ? form.check_account_country.value : 'USA');

                break;

            case 'swipe':
            case 'keyed':

                var ccPayload =
                      '&CardHolderName=' + (form.card_name ? form.card_name.value : payee_name_full)
                    + '&CardNumber=' + form.card_number.value
                    + '&PaymentTypeId=' + form.card_type.value
                    + '&ExpMonth=' + form.card_exp_month.value
                    + '&ExpYear=' + form.card_exp_year.value
                    + '&CVV=' + form.card_cvv2.value;

                break;
        }

    }

});