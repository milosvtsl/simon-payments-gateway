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
            switch (form.getAttribute('name')) {
                case 'form-merchant-edit':
                    return updateMerchantEditForm(e, form);

                case 'form-merchant-form-edit':
                    return updateMerchantFormEditForm(e, form);

                case 'form-merchant-email-templates':
                    return updateMerchantEmailTemplatesForm(e, form);
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

    function updateMerchantFormEditForm(e, form) {
        if(form.field_add_select.value) {
            var field = form.field_add_select.value;
            form.field_add_select.value = '';
            e.preventDefault();
            var checkbox = form["fields[" + field + "][enabled]"];
            checkbox.checked = true;
            console.log("Adding field ", field, checkbox);

            var rows = form.getElementsByClassName('field-row');
            for(var i=0; i<rows.length; i++) {
                var row = rows[i];
                var rowfield = row.getAttribute('data-field');
                var rowcheckbox = form["fields[" + rowfield + "][enabled]"];
                row.classList.toggle('enabled', rowcheckbox.checked);
            }


            return false;
        }
    }

    function updateMerchantEmailTemplatesForm(e, form) {
        if(form.class_change && form.class_change.value) {
            var href =
                'merchant/?uid=' + form.merchant_uid.value
                + '&class=' + form.class_change.value
                + '&action=email-templates';
            if(document.location.href != href)
                document.location.href = href; //TODO: BROKEN??
            return;
        }

        form.subject.disabled =
        form.body.disabled =
        form.submit.disabled = form['status[enabled]'].checked;

    }
});