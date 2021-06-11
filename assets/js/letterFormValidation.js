
export default function letterFormValidation(jform) {
    let valid = true;

    if (!jform.find('.ltr_title').first().val()) {
         jform.find('.ltr_title').first().addClass('is-invalid');
        valid = false;
    }
    if (!jform.find('.ltr_organization').first().val()) {
         jform.find('.ltr_organization').first().addClass('is-invalid');
        valid = false;
    }

    return valid;
}
