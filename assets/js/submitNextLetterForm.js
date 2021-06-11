import letterFormValidation from './letterFormValidation';

export default function submitNextLetterForm() {
    console.log('posthubLetterFormsToSubmit '+posthubLetterFormsToSubmit);
    if (posthubLetterFormsToSubmit.length > 0) {
        let firstFormNumber = posthubLetterFormsToSubmit.shift();
        let formDomLocation = "#ifp" + firstFormNumber + " form";
        let formAction = $(formDomLocation).attr('action');
        console.log("A formAction: " + formAction);
        if (formAction.length > 1) {
            console.log("A sendingAllLetterForms: " + posthubSendingAllLetterForms);
            if (letterFormValidation(formDomLocation)) {
                $(formDomLocation).submit();
            } else {
                console.log("invalid form " + formDomLocation);
            }
            console.log("B sendingAllLetterForms: " + posthubSendingAllLetterForms);
        }
        if (posthubSendingAllLetterForms) submitNextLetterForm();

    } else {
        posthubSendingAllLetterForms = false;
    }
}
