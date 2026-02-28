import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        $(document).ready(function(){
            $('button.print-this-page').click(function(){
                window.print();
            });
        })
    }
}
