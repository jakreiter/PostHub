export default function letterFormValidation(jform) {
    let valid = true;
    let titleVal = jform.find('.ltr_title').first().val();
    console.log('ltr_title obj first:');
    console.log(jform.find('.ltr_title').first());
    if (!titleVal) {
        console.log('titleVal: ' + titleVal)
        jform.find('.ltr_title').first().addClass('is-invalid');
        valid = false;
    }

    let organizationVal = jform.find('.ltr_organization').first().val();
    if (!organizationVal) {
        jform.find('.ltr_organization').first().addClass('is-invalid');
        valid = false;
    }

    return valid;
}
