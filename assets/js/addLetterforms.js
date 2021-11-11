import organizationSelectAjaxize from "./organizationSelectAjaxize";
import newLetterFormsAjaxize from "./newLetterFormsAjaxize";
import rebind from './rebind';
import makeUnikeFormFieldIds from './makeUnikeFormFieldIds';

export default function addLetterforms(numberOfForms) {
    for (var nf = 0; nf < numberOfForms; nf++) {
        posthubLetterForms.push(posthubFormNumber);
        let html = $("#template_form").html();
        html = makeUnikeFormFieldIds(html, posthubFormNumber);
        var koddod = "<div id=\"ifp" + posthubFormNumber + "\" data-form-number=\"" + posthubFormNumber + "\"  class=\"letterDiv\">" + html + "</div>";
        $("#forforms").append(koddod);
        organizationSelectAjaxize(posthubFormNumber);
        newLetterFormsAjaxize();
        posthubFormNumber++;
    }
    rebind();
}
