import letterFormValidation from './letterFormValidation';

export default function submitNextLetterForm() {
    if (posthubLetterFormsToSubmit.length > 0) {
        let firstFormNumber = posthubLetterFormsToSubmit.shift();
        let formDomLocation = "#ifp" + firstFormNumber + " form";
        let formAction = $(formDomLocation).attr('action');
        if (formAction.length > 1) {
            if (letterFormValidation($(formDomLocation))) {
                $(formDomLocation).submit();
            } else {
                if (posthubSendingAllLetterForms) submitNextLetterForm();
            }

        } else {
            if (posthubSendingAllLetterForms) submitNextLetterForm();
        }


    } else {
        posthubSendingAllLetterForms = false;
    }
}

