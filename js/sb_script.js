/* login */
$(document).ready(function () {
    $('.link-span').click(function () {
        const action = $(this).data('get');
        if (action === 'register') {
            $('#login-form').hide();
            $('#register-form').show();
            $('#form-title').text('Register');
        } else if (action === 'login') {
            $('#register-form').hide();
            $('#login-form').show();
            $('#form-title').text('Login');
        }
    });
});