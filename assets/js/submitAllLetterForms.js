import submitNextLetterForm from "./submitNextLetterForm";

export default function submitAllLetterForms() {

    posthubLetterFormsToSubmit = [...posthubLetterForms];
    if (posthubLetterFormsToSubmit.length>0) {
        console.log("posthubLetterFormsToSubmit: "+posthubLetterFormsToSubmit);
        posthubSendingAllLetterForms = true;
        submitNextLetterForm();
    }
}
