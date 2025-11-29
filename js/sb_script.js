$(document).ready(function () {

    $('.eye-icon').on('click', function () {
        const input = $(this).prev('input');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
    });


    $('#register-form').submit(function (e) {
        e.preventDefault();

        const form = $(this);
        const username = form.find('input[name="username"]').val().trim();
        const email = form.find('input[name="email"]').val().trim();
        const password = form.find('input[name="password"]').val();
        const confirm_password = form.find('input[name="confirm_password"]').val();
        const profile_img = form.find('input[name="profile_img"]')[0].files[0];

        let errors = [];

        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/.test(username)) {
            errors.push('Username must contain letters and numbers only.');
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Invalid email format.');
        }

        if (!/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)) {
            errors.push('Password must be at least 8 characters with letters, numbers, and special characters.');
        }

        if (password !== confirm_password) {
            errors.push('Passwords do not match.');
        }

        if (profile_img) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(profile_img.type)) {
                errors.push('Profile image must be JPG, PNG, or GIF.');
            }
        }

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        const formData = new FormData(this);

        $.ajax({
            url: 'login/register.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    form[0].reset();
                    form.hide();
                    $('#login-form').show();
                    alert('Registration successful! Please log in.');
                } else {
                    alert(data.message);
                }
            },
            error: function (error) {
                alert('AJAX error: ' + error);
            }
        });
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