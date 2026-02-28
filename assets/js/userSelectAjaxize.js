import formatUserOption from './formatUserOption';
import formatUserSelection from './formatUserSelection';

// userSelectAjaxize
export default function userSelectAjaxize()
{
    $('select.user-ajax-select').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '/kadmin/user/find.json',
            dataType: 'json',
            delay: 250 // milliseconds
        },
        minimumInputLength: 2,
        templateResult: formatUserOption,
        templateSelection: formatUserSelection
    });
}