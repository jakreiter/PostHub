import formatOrganizationOption from './formatOrganizationOption';
import formatOrganizationSelection from './formatOrganizationSelection';

export default function organizationFilterSelectAjaxize()
{
    $('select.organization-ajax-filter-select').select2({
        theme: 'bootstrap4',
        ajax: {
            url: '/kadmin/organization/find.json',
            dataType: 'json',
            delay: 250 // milliseconds
        },
        minimumInputLength: 2,
        placeholder: "",
        allowClear: true,
        templateResult: formatOrganizationOption,
        templateSelection: formatOrganizationSelection
    });
}