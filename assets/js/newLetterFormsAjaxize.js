import organizationSelectAjaxize from "./organizationSelectAjaxize";
import rebind from './rebind';
import submitNextLetterForm from "./submitNextLetterForm";
import makeUnikeFormFieldIds from "./makeUnikeFormFieldIds";
import letterFormValidation from "./letterFormValidation";

// newLetterFormsAjaxize
export default function newLetterFormsAjaxize() {
    $(".letterDiv form").off('submit').submit(function (e) {
        e.preventDefault();

        var formData = new FormData(this);
        var form = this;
        if (letterFormValidation($(form))) {

            $(form).find('fieldset').prop("disabled", true);
            $(form).find('.btnSubmitNormal').hide();
            $(form).find('.btnSubmitInProgress').show();

            $.ajax({
                url: this.action,
                type: 'POST',
                data: formData,
                success: function (data) {
                    let prntdv = $(form).closest('div');
                    let thisFormNumber = prntdv.data('formNumber');
                    if (data.includes('form-signin') && data.includes('form-signin')) {
                        $(form).find('fieldset').prop("disabled", false);
                        $(form).find('.btnSubmitNormal').show();
                        $(form).find('.btnSubmitInProgress').hide();
                        $(form).find('.btnSubmitSuccess').hide();

                        let statusTarget = $(form).find('div.statusDiv').first();
                        statusTarget.html("<p class='error-text text-danger text-sm-center'>Session expired. Please <a href='/login' target='_blank'>log in in new window</a>.</p>");
                    } else {
                        let html = makeUnikeFormFieldIds(data, thisFormNumber + 1000);
                        prntdv.html(html);
                        organizationSelectAjaxize(thisFormNumber);
                        if (posthubSendingAllLetterForms) submitNextLetterForm();
                        rebind();
                    }
                },
                error: function (data) {
                    $(form).find('fieldset').prop("disabled", false);
                    $(form).find('.btnSubmitNormal').show();
                    $(form).find('.btnSubmitInProgress').hide();
                    $(form).find('.btnSubmitSuccess').hide();

                    let statusTarget = $(form).closest('div.statusDiv');
                    statusTarget.html("<p class='text-danger'>Submit failed.</p>");
                    if (posthubSendingAllLetterForms) submitNextLetterForm();
                },
                cache: false,
                contentType: false,
                processData: false
            });

        }

    });
}