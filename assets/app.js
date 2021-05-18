/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
const $ = require('jquery');
global.$ = global.jQuery = $;
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
import 'bootstrap';
import 'tablesorter';

import 'select2';

var ifrejmy = [1];
var formNumber = 1;

function addLetterforms(numberOfForms)
{
    for (var nf=0;nf<numberOfForms;nf++)
    {
        formNumber++;
        ifrejmy.push(formNumber);
        let html = $("#template_form").html();
        html = html.replaceAll('letter_organization', 'letter_organization'+formNumber);
        //console.log(html);
        var koddod = "<div id=\"ifp"+formNumber+"\"  class=\"letterDiv\">"+ html+"</div>";
        $("#forforms").append(koddod);
        console.log($('#ifp'+formNumber+' select.superselect').length);
        $('#ifp'+formNumber+' select.superselect').select2({
            theme: 'bootstrap4',
        });
    }
    rebind();
}

function rebind() {

    $('select.select2').select2({
        theme: 'bootstrap4',
    });


    /*
     * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
     * see: https://github.com/select2/select2/issues/5993
     * see: https://github.com/jquery/jquery/issues/4382
     *
     * TODO: Recheck with the select2 GH issue and remove once this is fixed on their side
     */

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });



    $(function() {
        $("table.sortable").tablesorter({
            theme: "bootstrap"
        });
    });

    $("form.deleteForm").submit(function (event) {
        var confirmed = confirm('Are you sure you want to delete this item?')
        if (!confirmed) event.preventDefault();
        return confirmed;
    });

    $('.oneMoreForm').on("click", function (event, errorMessage) {
        addLetterforms(1);
    });

    $('.fiveMoreForms').on("click", function (event, errorMessage) {
        addLetterforms(5);
    });

    $(".letterDiv form").submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var form = this;
        $(form).find('fieldset').prop("disabled", true);
        $(form).find('.btnSubmitNormal').hide();
        $(form).find('.btnSubmitInProgress').show();

        $.ajax({
            url: this.action,
            type: 'POST',
            data: formData,
            success: function (data) {
                let prntdv = $(form).closest('div');
                prntdv.html(data);
            },
            error: function (data) {
                $(form).find('fieldset').prop("disabled", false);
                $(form).find('.btnSubmitNormal').show();
                $(form).find('.btnSubmitInProgress').hide();
                $(form).find('.btnSubmitSuccess').hide();

                let statusTarget = $(form).closest('div.statusDiv');
                statusTarget.html("<p class='text-danger'>Submit failed.</p>");
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });


    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

}

$(document).ready(function () {
    rebind();
});




// start the Stimulus application
import './bootstrap';
