import userSelectAjaxize from "./userSelectAjaxize";
import organizationFilterSelectAjaxize from "./organizationFilterSelectAjaxize";
import addLetterforms from "./addLetterforms";
import newLetterFormsAjaxize from "./newLetterFormsAjaxize";
import submitAllLetterForms from "./submitAllLetterForms";

var posthubSendingAllLetterForms = false;
global.posthubSendingAllLetterForms = posthubSendingAllLetterForms;

// rebind
export default function rebind() {

    $('select.select2').select2({
        theme: 'bootstrap4',
    });

    function formatAccount(repo) {
        if (!repo.id) {
            return repo.text;
        }

        var markup = $("<span>"
            + repo.text + " <span class='badge badge-info'>" + repo.owner + "</span> </span>");

        return markup;
    }

    $("#import1_registered")
        .select2(
            {
                minimumInputLength: 2,
                minimumResultsForSearch: 10,
                theme: 'bootstrap4',
                ajax: {
                    url: '/kadmin/import/registered',
                    dataType: "json",
                    type: "GET",
                    data: function (params) {

                        var queryParameters = {
                            fragment: params.term
                        };
                        return queryParameters;
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    id: item.id,
                                    text: item.company,
                                    owner: item.name
                                }
                            })
                        };
                    }
                },
                templateResult: formatAccount
            });

    userSelectAjaxize();
    organizationFilterSelectAjaxize();
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

    // on first focus (bubbles up to document), open the menu
    $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
        $(this).closest(".select2-container").siblings('select:enabled').select2('open');
    });

    // steal focus during close - only capture once and stop propogation
    $('select.select2').on('select2:closing', function (e) {
        $(e.target).data("select2").$selection.one('focus focusin', function (e) {
            e.stopPropagation();
        });
    });

    $(function () {
        $("table.sortable").tablesorter({
            theme: "bootstrap"
        });
    });

    $('form.deleteForm').off();
    $("form.deleteForm").submit(function (event) {
        var confirmed = confirm('Are you sure you want to delete this item?');
        if (!confirmed) event.preventDefault();
        return confirmed;
    });

    $('.oneMoreForm').off().on("click", function (event, errorMessage) {
        addLetterforms(1);
    });
    $('.fiveMoreForms').off().on("click", function (event, errorMessage) {
        addLetterforms(5);
    });

    $('.submitAllForms').off().on("click", function (event, errorMessage) {
        submitAllLetterForms();
        $('.oneMoreForm').hide();
        $('.fiveMoreForms').hide();
    });


    newLetterFormsAjaxize();


    $(".custom-file-input").on("change", function () {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

}
