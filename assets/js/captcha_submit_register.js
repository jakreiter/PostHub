export default function(token) {
    //document.getElementById("registration_form").submit();
    $('#registration_form').trigger('submit', [true]);
};