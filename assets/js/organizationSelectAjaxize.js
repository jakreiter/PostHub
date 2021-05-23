import formatOrganizationOption from './formatOrganizationOption';
import formatOrganizationSelection from './formatOrganizationSelection';

export default function (formNumber)
{
    console.log("organizationSelectAjaxize("+formNumber+")");
    $('#ifp' + formNumber + ' select.organization-ajax-select').select2({
        theme: 'bootstrap4',
        ajax: {
            url: '/kadmin/organization/find.json',
            dataType: 'json',
            delay: 250 // milliseconds
        },
        minimumInputLength: 2,
        templateResult: formatOrganizationOption,
        templateSelection: formatOrganizationSelection
    });
}