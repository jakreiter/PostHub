import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        $(document).ready(function(){
            $('.check:button').click(function(){
                $('table.handover-table input:checkbox').attr('checked','checked');
                $(this).fadeOut();
            });
        })
    }
}
