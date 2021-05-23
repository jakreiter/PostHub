
import organizationSelectAjaxize from "./organizationSelectAjaxize";
import rebind from './rebind';

// newLetterFormsAjaxize
export default function newLetterFormsAjaxize() {
    $(".letterDiv form").off('submit').submit(function (e) {
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