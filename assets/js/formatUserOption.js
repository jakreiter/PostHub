// formatUserOption
export default function formatUserOption (userInfo) {
    if (userInfo.loading) {
        return userInfo.text;
    }

    var $container = $(
        "<div class='sel-row'>" +
        " <span class='select2-result-repository__title'></span>" +
        " <span class='select2-result-repository__scan badge badge-info'></span>" +
        " <span class='select2-result-repository__location'></span>" +
        "</div>"
    );

    $container.find(".select2-result-repository__title").text(userInfo.username);
    $container.find(".select2-result-repository__scan").text(userInfo.email);

    return $container;
}