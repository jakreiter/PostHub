import formatOrganizationOption from './formatOrganizationOption';
import formatOrganizationSelection from './formatOrganizationSelection';

export default function organizationFilterSelectAjaxize()
{
    console.log("organizationFilterSelectAjaxize()");
    $('select.organization-ajax-filter-select').select2({
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