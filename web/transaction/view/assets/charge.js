/**
 * Created by PhpStorm.
 * Transaction: ari
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
            setStatus("Card Swipe Ready!");
            charHistory = '';
        }, 2000);

        if(lastParseData)
            setStatus("Card Track Parsed Successfully");
        else
            setStatus("Key Input Detected: " + charCode);

        charHistory += String.fromCharCode(charCode);
        var parseData = parseMagTekTrack(charHistory);
        if(parseData && parseData.card_number && parseData.payee_last_name) {
            charHistory = '';
            lastParseData = parseData;
            console.log("Card tracks parsed successfully", lastParseData);
            var forms = document.getElementsByName('form-transaction-charge');
            if(forms.length === 0)
                throw new Exception("No Charge form found");
            for(var i=0; i<forms.length; i++)
                updateChargeForm(e, forms[i]);
        }

    }

    function setStatus(message) {
        var elms = document.getElementsByClassName('reader-status');
        for(var i=0; i<elms.length; i++)
            elms[i].innerHTML = message;
    }

    function updateChargeForm(e, form) {
        // Enter in swiped data
        if(lastParseData) {
            form.entry_method.value = 'Swipe';
            form.card_number.value = lastParseData.card_number;
            form.payee_first_name.value = lastParseData.payee_first_name;
            form.payee_last_name.value = lastParseData.payee_last_name;
            form.card_exp_month.value = lastParseData.card_exp_month;
            form.card_exp_year.value = lastParseData.card_exp_year;
        }

        // Update card type
        if(form.card_number && form.card_number.value)
            form.card_type.value = getCreditCardType(form.card_number.value);
    }

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

            var data = {};
            data.card_number = arr[0];
            data.card_exp_month = arr[2].substring(2, 4);
            data.card_exp_year = arr[2].substring(0, 2);
            data.payee_first_name = '';
            data.payee_last_name = '';

            nameArr = arr[1].split('/');
            data.payee_first_name = nameArr[1];
            data.payee_last_name = nameArr[0];
            return data;
        } catch (e) {
            return false;
        }
    }

    var testTrack = "%B4867000000008981^PRINCE/MATTHEW T^16070000000000000000000?;4867000000008981=16070000000000000000?|0600|20E68ACE32C1451621D3293B80990C6897C8F77B6C94AF55D04276CB280E0EFFC5056BE30B3373C6A7AB1487548E4D1F7765474E06F073EB63035D6A01E6B868|E251E166F5946EEB403E48B55266F70FFD53DAFE9C552DBCE94679CE4F876BBE20CD5B942DDE88FC||61403000|FAC066E1437774E3F7FDACD1122C5D9BD8F926F30F216625C8E8D3AB405BB10811313969784A3BAAED7E281292C68EEB0F886AADD939F7E0|B362DF8081616AA|F19E6ED2C0B2B65B|9010190B362DF800000B|D29B||1000%E";
    var testData = parseMagTekTrack(testTrack);
    console.log(testData);
});