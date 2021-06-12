export default function letterFormValidation(jform) {
    let valid = true;
    let titleVal = jform.find('.ltr_title').first().val();
    console.log('ltr_title obj:');
    console.log(jform.find('.ltr_title'));
    if (!titleVal) {
        console.log('titleVal: ' + titleVal)
        jform.find('.ltr_title').first().addClass('is-invalid');
        valid = false;
    }

    let organizationVal = jform.find('.ltr_organization').first().val();
    if (!organizationVal) {
        console.log(jform.find('.ltr_organization').first());
        console.log('organizationVal: ' + organizationVal)
        jform.find('.ltr_organization').first().addClass('is-invalid');
        valid = false;
    }

    return valid;
}
