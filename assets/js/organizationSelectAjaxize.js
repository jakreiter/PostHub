import formatOrganizationOption from './formatOrganizationOption';
import formatOrganizationSelection from './formatOrganizationSelection';

export default function organizationSelectAjaxize(posthubFormNumber)
{
    $('#ifp' + posthubFormNumber + ' select.organization-ajax-select').select2({
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