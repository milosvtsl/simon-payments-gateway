/**
 * Created by ari on 10/11/2016.
 */


// Initialize
document.addEventListener("DOMContentLoaded", function(event) {

    document.addEventListener("change", onForm);
    document.addEventListener("submit", onForm);

    function onForm(e) {
        if (e.target && e.target.form) {
            var form = e.target.form;
            if (form.getAttribute('name') === 'form-merchant-edit') {
                updateMerchantEditForm(e, form);
            }
        }
    }

    function updateMerchantEditForm(e, form) {
        if(form.convenience_fee_enabled.checked !== true) {
            form.convenience_fee_limit.disabled = true;
            form.convenience_fee_flat.disabled = true;
            form.convenience_fee_variable.disabled = true;
        } else {
            form.convenience_fee_limit.disabled = false;
            form.convenience_fee_flat.disabled = false;
            form.convenience_fee_variable.disabled = false;

        }
    }

});