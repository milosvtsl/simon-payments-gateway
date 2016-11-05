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
            if (form.getAttribute('name') === 'form-order-search') {
                updateOrderSearchForm(e, form);
            }
        }
    }

    function updateOrderSearchForm(e, form) {
        if(form.date_from.value) {
            if(!form.date_to.value || form.date_to.value === form.date_to.last_value) {
                var newdate = new Date(form.date_from.value);
                newdate.setDate(newdate.getDate() + 1);
                form.date_to.value = formatDateYYYYMMDD(newdate);
                form.date_to.last_value = form.date_to.value;
            }
        }
    }

    function formatDateYYYYMMDD(date) {
        return date.toISOString().substring(0, 10);
    }

});