
export default function submitNextLetterForm() {
    if (posthubLetterFormsToSubmit.length>0) {
        let firstFormNumber = posthubLetterFormsToSubmit.shift();
        let formDomLocation = "#ifp"+firstFormNumber+" form";
        let formAction = $(formDomLocation).attr('action');
        console.log("A formAction: "+formAction);
        if (formAction.length>1) {
            console.log("A sendingAllLetterForms: " + posthubSendingAllLetterForms);
            $(formDomLocation).submit();
            console.log("B sendingAllLetterForms: " + posthubSendingAllLetterForms);
        } else {
            if (posthubSendingAllLetterForms) submitNextLetterForm();
        }
    } else {
        posthubSendingAllLetterForms = false;
    }
}
