import organizationSelectAjaxize from "./organizationSelectAjaxize";
import newLetterFormsAjaxize from "./newLetterFormsAjaxize";
import rebind from './rebind';

//
export default function addLetterforms(numberOfForms) {
    console.log("addLetterforms("+numberOfForms+")");
    for (var nf = 0; nf < numberOfForms; nf++) {
        posthubFormNumber++;
        console.log(posthubLetterForms);
        posthubLetterForms.push(posthubFormNumber);
        let html = $("#template_form").html();
        html = html.replaceAll('letter__token', 'letter__token' + posthubFormNumber);
        html = html.replaceAll('letter_organization', 'letter_organization' + posthubFormNumber);
        html = html.replaceAll('letter_file', 'letter_file' + posthubFormNumber);
        html = html.replaceAll('letter_status', 'letter_status' + posthubFormNumber);
        html = html.replaceAll('letter_title', 'letter_title' + posthubFormNumber);
        html = html.replaceAll('letter_barcodeNumber', 'letter_barcodeNumber' + posthubFormNumber);
        //console.log(html);
        var koddod = "<div id=\"ifp" + posthubFormNumber + "\" data-form-number=\"" + posthubFormNumber + "\"  class=\"letterDiv\">" + html + "</div>";
        $("#forforms").append(koddod);
        organizationSelectAjaxize(posthubFormNumber);
        newLetterFormsAjaxize();
    }
    rebind();
}
