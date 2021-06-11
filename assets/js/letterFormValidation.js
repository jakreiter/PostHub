
export default function letterFormValidation(formDomLocation) {
    let valid = true;

    if (!$(formDomLocation+' .ltr_title').val()) {
        $(formDomLocation+' .ltr_title').addClass('is-invalid');
        valid = false;
    }
    if (!$(formDomLocation+' .ltr_organization').val()) {
        $(formDomLocation+' .ltr_organization').addClass('is-invalid');
        valid = false;
    }

    return valid;
}
