export default function letterFormValidation(jform) {
    let valid = true;
    let titleVal = jform.find('.ltr_title').first().val();
    console.log('ltr_title obj first:');
    console.log(jform.find('.ltr_title').first());
    if (!titleVal) {
        console.log('titleVal: ' + titleVal);
        jform.find('.ltr_title').first().addClass('is-invalid');
        valid = false;
    }

    let organizationVal = jform.find('.ltr_organization').first().val();
    console.log('organizationVal: ' + organizationVal);
    if (!organizationVal) {
        jform.find('.ltr_organization').first().addClass('is-invalid');
        valid = false;
    }
    $( ".is-invalid" ).focus(function() {
        console.log( "focus on input.is-invalid1" );
        console.log(this);
        $(this).removeClass('is-invalid');
    });
    return valid;
}
