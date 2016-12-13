/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/30/2016
 * Time: 11:11 PM
 */


// Initialize
document.addEventListener("DOMContentLoaded", function(event) {

    document.addEventListener("change", onFormChange);
    document.addEventListener("submit", onFormSubmit, false);
    document.addEventListener("keypress", onKeypress);

    function onFormChange(e) {
        if(e.target && e.target.form) {
            var form = e.target.form;
            if(form.getAttribute('name') === 'form-transaction-charge') {
                updateChargeForm(e, form);
            }
        }
    }

    function onFormSubmit(e) {
        if(e.target && e.target.nodeName.toUpperCase() === 'FORM') {
            var form = e.target;
            if(form.getAttribute('name') === 'form-transaction-charge') {
                updateChargeForm(e, form);

                if(!/^-?[0-9.]+$/.test(form.amount.value)) {
                    e.preventDefault();
                }
            }
        }
    }

    setTimeout(function() {
        var forms = document.getElementsByName('form-transaction-charge');
        for (var i = 0; i < forms.length; i++) {
            var form = forms[i];
            var e = {target: form};
            updateChargeForm(e, form);
        }
    }, 200);

    var charHistory = '';
    var lastParseData = false;
    var keyTimeout = false;
    function onKeypress(e) {
        var charCode = (typeof e.which == "number") ? e.which : e.keyCode;
        clearTimeout(keyTimeout);
        keyTimeout = setTimeout(function() {
            // Clear history if no input for half second
            charHistory = '';
            if(lastParseData) {
                setStatus("Card Track Parsed Successfully");
                var forms = document.getElementsByName('form-transaction-charge');
                for (var i = 0; i < forms.length; i++) {
                    forms[i].classList.add('swipe-input-successful');
                    updateChargeForm(e, forms[i]);
                }
                console.log("Parse Data: ", lastParseData);
                lastParseData = null;
            }
        }, 1500);

        charHistory += String.fromCharCode(charCode);

        var parseData = parseMagTekTrack(charHistory);

//         console.log(charHistory, parseData);

        if(parseData) {

            var forms = document.getElementsByName('form-transaction-charge');
            if (parseData.success) {
                lastParseData = parseData;
                for (var i = 0; i < forms.length; i++) {
                    var form = forms[i];
                    form.card_track.focus();
                    form.card_track.value = charHistory;
                    updateChargeForm(e, form);
                    for(var fi=0; fi<form.elements.length; fi++) {
                        // check for leaked magtrack
                        var elm = form.elements[fi];
                        switch(elm.nodeName.toLowerCase()) {
                            default:
                            case 'select':
                            case 'textarea':
                                break;
                            case 'input':
                                if(elm.value.indexOf('%B') !== -1) elm.value = elm.value.substr(0, elm.value.indexOf('%B'));
                                if(elm.value.indexOf('%b') !== -1) elm.value = elm.value.substr(0, elm.value.indexOf('%b'));
                                //console.log(elm, elm.value, elm.nodeName);
                                break;
                        }

                        //if(elm.value && elm.value.indexOf(charHistory.substr(0, 20)))
                    }

                }
                setStatus("Card Track Parsed Successfully");
            } else {
                for (var ii = 0; ii < forms.length; ii++) {
                    var form2 = forms[ii];
                    form2.classList.remove('swipe-input-successful');
                }
                setStatus("Key Input Detected: " + charCode);
            }
        }

    }

    function setStatus(message) {
        var elms = document.getElementsByClassName('reader-status');
        for(var i=0; i<elms.length; i++)
            elms[i].innerHTML = message;
    }

    var amount_timeout = null;
    function updateChargeForm(e, form) {
        updateStyleSheetTheme(form);
        // Enter in swiped data
        if(lastParseData && lastParseData.success) {
            //form.entry_method.value = 'Swipe';
            form.entry_mode.value = 'Swipe';
            form.card_number.value = lastParseData.card_number;
            form.payee_first_name.value = lastParseData.payee_first_name;
            form.payee_last_name.value = lastParseData.payee_last_name;
            form.customer_first_name.value = lastParseData.payee_first_name;
            form.customer_last_name.value = lastParseData.payee_last_name;

            form.card_exp_month.value = lastParseData.card_exp_month;
            form.card_exp_year.value = lastParseData.card_exp_year;
        }

        clearTimeout(amount_timeout);
        amount_timeout = setTimeout(function() {
            if(!/^-?[0-9.]+$/.test(form.amount.value)) {
                form.amount.value = '';
                return;
            }

            var amount = parseFloat(form.amount.value);
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
            if (form.convenience_fee)
                form.convenience_fee.value = '$' + (fee_amount).toFixed(2);
        }, 1200);

        // Update card type
        if(form.card_number && form.card_number.value) {
            var newType = getCreditCardType(form.card_number.value);
            if(newType) {
                for(var i=0; i<form.card_type.options.length; i++) {
                    var option = form.card_type.options[i];
                    if(option.innerHTML === newType) {
                        //form.card_type.value = newType;
                        form.card_type.selectedIndex = i;
                        console.log("Updating card type to: " + newType, form.card_type.value);
                    }
                }
            }
        }

    }

    function updateStyleSheetTheme(form) {

        var entry_mode = form.entry_mode.value.toLowerCase();

        form.classList[form.merchant_id.value ? 'add' : 'remove']('merchant-selected');
        form.classList[form.entry_mode.value ? 'add' : 'remove']('payment-method-selected');
        form.classList.remove('payment-method-keyed', 'payment-method-swipe', 'payment-method-check', 'payment-method-card');
        form.classList.add('payment-method-' + entry_mode);

        switch(entry_mode) {
            case 'keyed':
            case 'swipe':
                form.classList.add('payment-method-card');
                break;
        }

        if(form.merchant_id && form.merchant_id.nodeName.toUpperCase() === 'SELECT') {
            console.log("Merchant: ", form.merchant_id.selectedIndex);
            if(form.merchant_id.selectedIndex === -1)
                form.merchant_id.selectedIndex = 0;
            var selectedOption = form.merchant_id.options[form.merchant_id.selectedIndex];
            //formClasses += ' ' + selectedOption.getAttribute('data-form-class');
            form.integration_id.value = selectedOption.getAttribute('data-integration-id') || 0;
            form.convenience_fee_flat.value = selectedOption.getAttribute('data-convenience-fee-flat') || 0;
            form.convenience_fee_limit.value = selectedOption.getAttribute('data-convenience-fee-limit') || 0;
            form.convenience_fee_variable_rate.value = selectedOption.getAttribute('data-convenience-fee-variable-rate') || 0;

//             console.log("Merchant: ", form.merchant_id.value, formClasses);

        } else {

        }
        //form.setAttribute('class', formClasses);

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

        if(form.recur_count.value > 0) {
            form.recur_next_date.removeAttribute('disabled');
            form.recur_amount.removeAttribute('disabled');
            form.recur_frequency.removeAttribute('disabled');

            if(form.recur_amount.value <= 0) {
                form.recur_amount.value = form.amount.value;
            }

        } else {
            form.recur_next_date.setAttribute('disabled', 'disabled');
            form.recur_amount.setAttribute('disabled', 'disabled');
            form.recur_frequency.setAttribute('disabled', 'disabled');
        }

        if(typeof form.recur_frequency.last_value == 'undefined'
            || form.recur_frequency.last_value !== form.recur_frequency.value) {
            var newdate = new Date();
            var now = new Date();
            switch(form.recur_frequency.value) {
                case 'Weekly':
                    newdate = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
                    break;
                case 'BiWeekly':
                    newdate = new Date(now.getTime() + 14 * 24 * 60 * 60 * 1000);
                    break;
                default:
                case 'OneTimeFuture':
                case 'Monthly':
                    if (now.getMonth() == 11)   newdate = new Date(now.getFullYear() + 1, 0, 1);
                    else                        newdate = new Date(now.getFullYear(), now.getMonth() + 1, 1);
                    break;
                case 'BiMonthly':
                    newdate = new Date(now.getTime() + 60 * 24 * 60 * 60 * 1000);
                    break;

                case 'Quarterly':
                    newdate = new Date(now.getTime() + 91 * 24 * 60 * 60 * 1000);
                    break;
                case 'SemiAnnually':
                    newdate = new Date(now.getTime() + 182 * 24 * 60 * 60 * 1000);
                    break;
                case 'Annually':
                    newdate = new Date(now.getFullYear() + 1, 0, 1);
                    break;
            }
            var datestring = formatDateYYYYMMDD(newdate);
            form.recur_next_date.value = datestring;
            form.recur_next_date.last_value = form.recur_frequency.value;
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

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function parseMagTekTrack(string) {
        if(!string)
            return false;
        string = string.replace('%B', '');
        string = string.replace('%b', '');

        var arr = string.split('^');
        if(arr.length <= 2)
            return false;
        var nameArr = arr[1].split(' ');

        var data = {};
        data.card_number = arr[0];
        data.card_exp_year = arr[2].substring(0, 2);
        data.card_exp_month = arr[2].substring(2, 4);
        data.payee_first_name = '';
        data.payee_last_name = '';

        nameArr = (arr[1]||'/').split('/');
        data.payee_first_name = capitalizeFirstLetter(nameArr[1].toLowerCase());
        data.payee_last_name = capitalizeFirstLetter(nameArr[0].toLowerCase());

        data.success = data.card_exp_month.length == 2
            && data.card_exp_year.length == 2
            && data.card_number.length >= 15
            && data.payee_first_name.length > 0
            && data.payee_first_name.length > 0;
        return data;
    }

    function formatDateYYYYMMDD(date) {
        return date.toISOString().substring(0, 10);
        //
        //var mm = date.getMonth() + 1;
        //var dd = date.getDate();
        //
        //return [date.getFullYear(), '-', !mm[1] && '0', mm, '-', !dd[1] && '0', dd].join(''); // padding
    }

});