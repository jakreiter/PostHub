import formatUserOption from './formatUserOption';
import formatUserSelection from './formatUserSelection';

// userSelectAjaxize
export default function userSelectAjaxize()
{
    console.log("userSelectAjaxize()");
    $('select.user-ajax-select').select2({
        theme: 'bootstrap4',
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