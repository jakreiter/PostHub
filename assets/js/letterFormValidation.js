export default function letterFormValidation(jform) {
    let valid = true;
    let titleVal = jform.find('.ltr_title').first().val();
    if (!titleVal) {
        jform.find('.ltr_title').first().addClass('is-invalid');
        valid = false;
    }

    let organizationVal = jform.find('.ltr_organization').first().val();
    if (!organizationVal) {
        jform.find('.ltr_organization').first().addClass('is-invalid');
        valid = false;
    }
    $( ".is-invalid" ).focus(function() {
        $(this).removeClass('is-invalid');
    });
    return valid;
}
