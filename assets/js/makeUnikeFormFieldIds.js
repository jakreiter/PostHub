export default function makeUnikeFormFieldIds(html, posthubFormNumber) {

        html = html.replaceAll('letter__token', 'letter__token' + posthubFormNumber);
        html = html.replaceAll('letter_organization', 'letter_organization' + posthubFormNumber);
        html = html.replaceAll('letter_file', 'letter_file' + posthubFormNumber);
        html = html.replaceAll('letter_status', 'letter_status' + posthubFormNumber);
        html = html.replaceAll('letter_title', 'letter_title' + posthubFormNumber);
        html = html.replaceAll('letter_barcodeNumber', 'letter_barcodeNumber' + posthubFormNumber);
        return html;

}
