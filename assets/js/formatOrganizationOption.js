// formatOrganizationOption

export default function (organizationInfo) {
    if (organizationInfo.loading) {
        return organizationInfo.text;
    }

    var $container = $(
        "<div class='sel-row'>" +
        " <span class='select2-result-repository__title'></span>" +
        " <span class='select2-result-repository__splan badge badge-info'></span>" +
        " <span class='select2-result-repository__scan badge'></span>" +
        " <span class='select2-result-repository__location badge badge-info'></span>" +
        "</div>"
    );

    $container.find(".select2-result-repository__title").text(organizationInfo.name);
    if (organizationInfo.scan) {
        $container.find(".select2-result-repository__scan").text('scan');
    }
    $container.find(".select2-result-repository__splan").text(organizationInfo.scanPlan);

    if (organizationInfo.scan) $container.find(".select2-result-repository__scan").addClass('badge-success');
    else  $container.find(".select2-result-repository__scan").addClass('badge-secondary');

    $container.find(".select2-result-repository__location").text(organizationInfo.locationName);

    return $container;
}