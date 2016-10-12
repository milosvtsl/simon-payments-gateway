/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/30/2016
 * Time: 11:11 PM
 */


// Initialize
document.addEventListener("DOMContentLoaded", function(event) {

    document.addEventListener("change", onForm);
    document.addEventListener("submit", onForm);
    document.addEventListener("keypress", onKeypress);

    function onForm(e) {
        if(e.target && e.target.form) {
            var form = e.target.form;
            if(form.getAttribute('name') === 'form-transaction-charge') {
                updateChargeForm(e, form);
            }
        }
    }

    function updateAllForms(force) {
        if(force || charHistory.length > 100) {
            console.log("Card tracks parsed successfully");
            var forms = document.getElementsByName('form-transaction-charge');
            if(forms.length === 0)
                throw new Exception("No Charge form found");
            for(var i=0; i<forms.length; i++) {
                var form = forms[i];
                var e = {target: form};
                updateChargeForm(e, form);
                form.card_track.value = charHistory;
            }
            if(charHistory.length > 0) {
                setStatus("Card read successfully!");
            }
            charHistory = '';
        } else {
            setStatus("Card Swipe Ready!");
        }

        charHistory = '';
    }
    setTimeout(function() {
        updateAllForms(true);
    }, 200);

    var charHistory = '';
    var lastParseData = false;
    var keyTimeout = false;
    function onKeypress(e) {
        var charCode = (typeof e.which == "number") ? e.which : e.keyCode;
        clearTimeout(keyTimeout);
        keyTimeout = setTimeout(updateAllForms, 500);

        if(lastParseData)
            setStatus("Card Track Parsed Successfully");
        else
            setStatus("Key Input Detected: " + charCode);

        charHistory += String.fromCharCode(charCode);
        var parseData = parseMagTekTrack(charHistory);
        if(parseData && parseData.success) {
            lastParseData = parseData;
            var forms = document.getElementsByName('form-transaction-charge');
            if(forms.length === 0)
                throw new Exception("No Charge form found");
            for(var i=0; i<forms.length; i++) {
                var form = forms[i];

                form.card_track.focus();
                form.card_track.value = charHistory;
                updateChargeForm(e, form);
                if(charHistory)
                    form.card_track.removeAttribute('disabled');
                else
                    form.card_track.setAttribute('disabled', 'disabled');

            }
        }

    }

    function setStatus(message) {
        var elms = document.getElementsByClassName('reader-status');
        for(var i=0; i<elms.length; i++)
            elms[i].innerHTML = message;
    }

    function updateChargeForm(e, form) {
        updateStyleSheetTheme(form);
        // Enter in swiped data
        if(lastParseData && lastParseData.success) {
            //form.entry_method.value = 'Swipe';
            form.entry_mode.value = 'swipe';
            form.card_number.value = lastParseData.card_number;
            form.payee_first_name.value = lastParseData.payee_first_name;
            form.payee_last_name.value = lastParseData.payee_last_name;
            form.customer_first_name.value = lastParseData.payee_first_name;
            form.customer_last_name.value = lastParseData.payee_last_name;

            form.card_exp_month.value = lastParseData.card_exp_month;
            form.card_exp_year.value = lastParseData.card_exp_year;
            lastParseData = null;
        }

        var amount =  /^-?[0-9.]+$/.test(form.amount.value) ? parseFloat(form.amount.value) : 0;
        if(amount) {
            form.amount.value = (amount).toFixed(2);
            var fee_amount = 0;
            var fee_flat = parseFloat(form.convenience_fee_flat.value);
            var fee_variable = parseFloat(form.convenience_fee_variable_rate.value);
            var fee_limit = parseFloat(form.convenience_fee_limit.value);
            fee_amount += fee_flat;
            fee_amount += fee_variable / 100 * amount;
            if(fee_limit && fee_limit > fee_amount)
                fee_amount = fee_limit;

            form.total_amount.value = '$' + (amount + fee_amount).toFixed(2);
            if (form.convenience_fee_total)
                form.convenience_fee_total.value = '$' + (fee_amount).toFixed(2);
        } else {
            form.amount.value = '';
        }

        // Update card type
        if(form.card_number && form.card_number.value) {
            var newType = getCreditCardType(form.card_number.value);
            if(newType)
                form.card_type.value = newType;
        }

    }

    function updateStyleSheetTheme(form) {

        var formClasses = 'themed';
        if(form.merchant_id.value)
            formClasses += ' merchant-selected';
        if(form.entry_mode.value)
            formClasses += ' payment-method-selected payment-method-' + form.entry_mode.value;

        if(form.merchant_id && form.merchant_id.nodeName.toUpperCase() === 'SELECT') {
            var selectedOption = form.merchant_id.options[form.merchant_id.selectedIndex];
            formClasses += ' ' + selectedOption.getAttribute('data-form-class');
            form.integration_id.value = selectedOption.getAttribute('data-integration-id') || 0;
            form.convenience_fee_flat.value = selectedOption.getAttribute('data-convenience-fee-flat') || 0;
            form.convenience_fee_limit.value = selectedOption.getAttribute('data-convenience-fee-limit') || 0;
            form.convenience_fee_variable_rate.value = selectedOption.getAttribute('data-convenience-fee-variable-rate') || 0;

//             console.log("Merchant: ", form.merchant_id.value, formClasses);

        } else {

        }
        form.setAttribute('class', formClasses);

        // Disable unused payment methods
        switch(form.entry_mode.value.toLowerCase()) {
            case 'check':
                form.getElementsByClassName('form-payment-method-check')[0].removeAttribute('disabled');
                form.getElementsByClassName('form-payment-method-credit')[0].setAttribute('disabled', 'disabled');
                break;

            case 'swipe':
            case 'keyed':
                form.getElementsByClassName('form-payment-method-check')[0].setAttribute('disabled', 'disabled');
                form.getElementsByClassName('form-payment-method-credit')[0].removeAttribute('disabled');
                break;
        }

        if(form.recur_enable.checked) {
            form.recur_until_cancel.removeAttribute('disabled');
            form.recur_start_date.removeAttribute('disabled');
            form.recur_count.removeAttribute('disabled');
            form.recur_frequency.removeAttribute('disabled');

        } else {
            form.recur_until_cancel.setAttribute('disabled', 'disabled');
            form.recur_start_date.setAttribute('disabled', 'disabled');
            form.recur_count.setAttribute('disabled', 'disabled');
            form.recur_frequency.setAttribute('disabled', 'disabled');

        }
    }

    // Utilities

    function getCreditCardType(number) {

        // visa
        var re = new RegExp("^4");
        if (number.match(re) != null)
            return "Visa";

        // Mastercard
        re = new RegExp("^[2|5][1-5]");
        if (number.match(re) != null)
            return "MasterCard";

        // AMEX
        re = new RegExp("^3[47]");
        if (number.match(re) != null)
            return "Amex";

        // Discover
        re = new RegExp("^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)");
        if (number.match(re) != null)
            return "Discover";

        // Diners
        re = new RegExp("^36");
        if (number.match(re) != null)
            return "Diners";

        // Diners - Carte Blanche
        re = new RegExp("^30[0-5]");
        if (number.match(re) != null)
            return "Diners - Carte Blanche";

        // JCB
        re = new RegExp("^35(2[89]|[3-8][0-9])");
        if (number.match(re) != null)
            return "JCB";

        // Visa Electron
        re = new RegExp("^(4026|417500|4508|4844|491(3|7))");
        if (number.match(re) != null)
            return "Visa Electron";

        return null;
    }

    function parseMagTekTrack(string) {
        try {
            string = string.replace('%B', '');
            string = string.replace('%b', '');

            var arr = string.split('^');
            var nameArr = arr[1].split(' ');
            var len = nameArr.length;
// console.log(arr);
            var data = {};
            data.card_number = arr[0];
            data.card_exp_year = arr[2].substring(0, 2);
            data.card_exp_month = arr[2].substring(2, 4);
            data.payee_first_name = '';
            data.payee_last_name = '';

            nameArr = arr[1].split('/');
            data.payee_first_name = nameArr[1];
            data.payee_last_name = nameArr[0];

            data.success = data.card_exp_month.length == 2
                && data.card_exp_year.length == 2
                && data.card_number.length >= 15
                && data.payee_first_name.length > 0
                && data.payee_first_name.length > 0;
                console.log(data);
            return data;
        } catch (e) {
            console.log(string, e);
            return false;
        }
    }

});