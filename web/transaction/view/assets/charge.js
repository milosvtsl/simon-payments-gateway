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

    var charHistory = '';
    var lastParseData = false;
    var keyTimeout = false;
    function onKeypress(e) {
        var charCode = (typeof e.which == "number") ? e.which : e.keyCode;
        clearTimeout(keyTimeout);
        keyTimeout = setTimeout(function() {
            if(charHistory) {
                console.log("Card tracks parsed successfully", lastParseData);
                var forms = document.getElementsByName('form-transaction-charge');
                if(forms.length === 0)
                    throw new Exception("No Charge form found");
                for(var i=0; i<forms.length; i++) {
                    var form = forms[i];
                    updateChargeForm(e, form);
                    form.swipe_input.value = charHistory;
                }
                charHistory = '';
                setStatus("Card read successfully!");
            } else {
                setStatus("Card Swipe Ready!");
            }

            charHistory = '';
        }, 2000);

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
                updateChargeForm(e, form);
                form.swipe_input.value = charHistory;
                form.swipe_input.focus();
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
            form.entry_method.value = 'Swipe';
            form.card_number.value = lastParseData.card_number;
            form.payee_first_name.value = lastParseData.payee_first_name;
            form.payee_last_name.value = lastParseData.payee_last_name;
            form.card_exp_month.value = lastParseData.card_exp_month;
            form.card_exp_year.value = lastParseData.card_exp_year;
            lastParseData = null;
        }

        var amount = parseFloat(form.amount.value) || 0;
        var fee_amount = 0;
        if(form.convenience_fee_limit)
            fee_amount += parseFloat(form.convenience_fee_limit.value);
        if(form.convenience_fee_flat)
            fee_amount += parseFloat(form.convenience_fee_flat.value);
        if(form.convenience_fee_variable_rate)
            fee_amount += parseFloat(form.convenience_fee_variable_rate.value);
        form.total_amount.value = '$' + (amount+fee_amount).toFixed(2);
        form.amount.value = (amount).toFixed(2);
        if(form.convenience_fee_total)
            form.convenience_fee_total.value = '$' + (fee_amount).toFixed(2);

        // Update card type
        if(form.card_number && form.card_number.value)
            form.card_type.value = getCreditCardType(form.card_number.value);


    }

    function updateStyleSheetTheme(form) {


        if(form.merchant_id && form.merchant_id.nodeName.toUpperCase() === 'SELECT') {
            var selectedOption = form.merchant_id.options[form.merchant_id.selectedIndex];
            var formClasses = selectedOption.getAttribute('data-form-class');
            if(formClasses)
                form.setAttribute('class', 'themed ' + formClasses);
            console.log("Merchant: ", form.merchant_id.value, formClasses);

        } else {

        }
    }

    // Utilities

    function getCreditCardType(number) {
        if (/^5[1-5]/.test(number))
            return "MasterCard";

        if (/^4/.test(number))
            return "Visa";

        else if (/^3[47]/.test(number))
            return "Amex";

        return "Unknown";
    }

    function parseMagTekTrack(string) {
        try {
            string = string.replace('%B', '');
            string = string.replace('%b', '');

            var arr = string.split('^');
            var nameArr = arr[1].split(' ');
            var len = nameArr.length;
//console.log(arr);
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
                && data.card_number.length == 16
                && data.payee_first_name.length > 0
                && data.payee_first_name.length > 0;
            return data;
        } catch (e) {
            return false;
        }
    }

});