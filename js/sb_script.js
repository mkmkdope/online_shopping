$(document).ready(function () {
    // Check URL parameter to show correct form on page load
    const urlParams = new URLSearchParams(window.location.search);
    const formParam = urlParams.get('form');
    
    if (formParam === 'register') {
        $('#login-form').hide();
        $('#updatePassword-form').hide();
        $('#register-form').show();
    } else if (formParam === 'forgotPassword') {
        $('#login-form').hide();
        $('#register-form').hide();
        $('#updatePassword-form').show();
    }
    
    $('.link-span').click(function (e) {
        e.preventDefault();
        const action = $(this).data('get');
        
        // Hide all error and success messages when switching forms
        $('.message').hide();
        
        if (action === 'register') {
            try {
                $('#register-form').find('input').val('');
            } catch (ex) {
                console.error('Error clearing register form inputs:', ex);
            }
            $('#register-form').find('input[type="checkbox"]').prop('checked', false);
            $('#login-form').hide();
            $('#updatePassword-form').hide();
            $('#register-form').show();
            $('#form-title').text('Register');
        } else if (action === 'login') {
            $('#register-form').hide();
            $('#updatePassword-form').hide();
            $('#login-form').show();
            $('#form-title').text('Login');
        } else if (action === 'forgotPassword') {
            $('#register-form').hide();
            $('#login-form').hide();
            $('#updatePassword-form').show();
            $('#form-title').text('Update Password');
        }
    });

    $('#login-form').submit(function (e) {
        e.preventDefault();

        const form = $(this);
        const username = form.find('input[name="username"]').val().trim();
        const password = form.find('input[name="password"]').val();
        const rememberMe = form.find('input[name="remember_me"]').is(':checked');

        let errors = [];

        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/.test(username)) {
            errors.push('Username must contain letters and numbers only.');
        }

        if (!/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)) {
            errors.push('Password must be at least 8 characters with letters, numbers, and special characters.');
        }

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        $.ajax({
            url: 'login/login.php',
            type: 'POST',
            data: { username: username, password: password, rememberMe: rememberMe },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    form[0].reset();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    alert(data.message);
                }
            },
            error: function (error) {
                alert('AJAX error: ' + error);
            }
        });
    });

    $('#request-form').submit(function (e) {
        e.preventDefault();

        const form = $(this);
        const email = form.find('input[name="email"]').val().trim();

        let errors = [];

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Invalid email format.');
        }

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        $.ajax({
            url: 'login/request.php',
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    form[0].reset();
                    alert('Password reset link sent to your email.');
                    $('#request-form').show();
                }
                else {
                    alert(data.message);
                }
            }
        });
    });

    const url = new URLSearchParams(window.location.search);
    const token = url.get('token');

    const updateForm = $('#updatePassword-form');
    const validToken = updateForm.attr('data-valid') === 'true';
    $('#login-form, #register-form, #request-form, #updatePassword-form').hide();

    // Show the correct form
    if (validToken) {
        updateForm.show();
    } else {
        $('#login-form').show();
    }



    $('.link-span').click(function () {
        const action = $(this).data('get');
        $('#login-form, #register-form, #request-form, #updatePassword-form').hide();

        if (action === 'register') $('#register-form').show();
        if (action === 'login') $('#login-form').show();
        if (action === 'forgotPassword') $('#request-form').show();
        if (action === 'updatePassword') $('#updatePassword-form').show();
    });




    $('#updatePassword-form').submit(function (e) {
        e.preventDefault();

        const form = $(this);
        const password = form.find('input[name="password"]').val();
        const confirm_password = form.find('input[name="confirm_password"]').val();

        let errors = [];

        if (!/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)) {
            errors.push('Password must be at least 8 characters with letters, numbers, and special characters.');
        }

        if (password !== confirm_password) {
            errors.push('Passwords do not match.');
        }

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        $.ajax({
            url: 'login/updatePass.php',
            type: 'POST',
            data: { password: password, confirm_password: confirm_password, token: token },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    form[0].reset();
                    form.hide();
                    alert('Password updated successfully! Please log in.');
                    $('#login-form').show();
                } else {
                    alert(data.message);
                }
            },
            error: function (error) {
                alert('AJAX error: ' + error);
            }
        });

    });



});