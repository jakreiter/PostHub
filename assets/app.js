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
        var koddod = "<div id=\"ifp"+formNumber+"\"  class=\"letterDiv\">"+$("#template_form").html();+"</div>";
        $("#forforms").append(koddod);
    }
    rebind();
}

function rebind() {

    $('select.select2').select2({
        theme: 'bootstrap4',
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
        $.ajax({
            url: this.action,
            type: 'POST',
            data: formData,
            success: function (data) {
                let prntdv = $(form).closest('div');
                prntdv.append("<p>saved</p>");

                //alert(data)
            },
            error: function (data) {
                console.log('error');
                let prntdv = $(form).closest('div');
                $(form).find('fieldset').prop("disabled", false);
                prntdv.append("<p>fail</p>");

                //alert(data)
            },
            always: function (data) {
                console.log('always');
                let prntdv = $(form).closest('div');
                $(form).find('fieldset').prop("disabled", false);
                prntdv.append("<p>fail</p>");

                //alert(data)
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
