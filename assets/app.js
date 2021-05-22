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

var ifrejmy = [0];
var formNumber = 0;

function organizationSelectAjaxize(formNumber)
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

function addLetterforms(numberOfForms) {
    console.log("addLetterforms("+numberOfForms+")");
    for (var nf = 0; nf < numberOfForms; nf++) {
        formNumber++;
        ifrejmy.push(formNumber);
        let html = $("#template_form").html();
        html = html.replaceAll('letter__token', 'letter__token' + formNumber);
        html = html.replaceAll('letter_organization', 'letter_organization' + formNumber);
        html = html.replaceAll('letter_file', 'letter_file' + formNumber);
        html = html.replaceAll('letter_status', 'letter_status' + formNumber);
        html = html.replaceAll('letter_title', 'letter_title' + formNumber);
        html = html.replaceAll('letter_barcodeNumber', 'letter_barcodeNumber' + formNumber);
        //console.log(html);
        var koddod = "<div id=\"ifp" + formNumber + "\" data-form-number=\"" + formNumber + "\"  class=\"letterDiv\">" + html + "</div>";
        $("#forforms").append(koddod);
        organizationSelectAjaxize(formNumber);
        newLetterFormsAjaxize();
    }
    rebind();
}


function formatOrganizationOption (organizationInfo) {
    if (organizationInfo.loading) {
        return organizationInfo.text;
    }

    var $container = $(
        "<div class='sel-row'>" +
        " <span class='select2-result-repository__title'></span>" +
        " <span class='select2-result-repository__scan badge'></span>" +
        " <span class='select2-result-repository__location'></span>" +
        "</div>"
    );

    $container.find(".select2-result-repository__title").text(organizationInfo.name);
    $container.find(".select2-result-repository__scan").text(organizationInfo.scan);

    if (organizationInfo.scan) $container.find(".select2-result-repository__scan").addClass('badge-success');
    else  $container.find(".select2-result-repository__scan").addClass('badge-secondary');

    $container.find(".select2-result-repository__location").text(organizationInfo.locationName);

    return $container;
}

function newLetterFormsAjaxize() {
    $(".letterDiv form").off('submit');
    $(".letterDiv form").submit(function (e) {
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
                formNumber = prntdv.data('formNumber');
                prntdv.html(data);
                organizationSelectAjaxize(formNumber);
                rebind();
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
}

function formatOrganizationSelection (repo) {
    return repo.text;
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


    $(function () {
        $("table.sortable").tablesorter({
            theme: "bootstrap"
        });
    });

    $('form.deleteForm').off();
    $("form.deleteForm").submit(function (event) {
        var confirmed = confirm('Are you sure you want to delete this item?')
        if (!confirmed) event.preventDefault();
        return confirmed;
    });

    $('.oneMoreForm').off();
    $('.oneMoreForm').on("click", function (event, errorMessage) {
        addLetterforms(1);
    });
    $('.fiveMoreForms').off();
    $('.fiveMoreForms').on("click", function (event, errorMessage) {
        addLetterforms(5);
    });


    newLetterFormsAjaxize();


    $(".custom-file-input").on("change", function () {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

}

$(document).ready(function () {
    rebind();
});


// start the Stimulus application
import './bootstrap';
