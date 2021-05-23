import organizationSelectAjaxize from "./organizationSelectAjaxize";
import newLetterFormsAjaxize from "./newLetterFormsAjaxize";
import rebind from './rebind';

//
export default function addLetterforms(numberOfForms) {
    console.log("addLetterforms("+numberOfForms+")");
    for (var nf = 0; nf < numberOfForms; nf++) {
        formNumber++;
        console.log(ifrejmy);
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
