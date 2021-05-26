export default function(token) {
    //document.getElementById("registration_form").submit();
    console.log('captcha_submit_register');
    $('#registration_form').trigger('submit', [true]);
};