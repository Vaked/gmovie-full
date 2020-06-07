$(document).ready(function () {
    jQuery.validator.addMethod("passwordCheck",
        function (value) {
            if (!/[A-Z]/.test(value)) {
                return false;
            } else if (!/[a-z]/.test(value)) {
                return false;
            } else if (!/[0-9]/.test(value)) {
                return false;
            }

            return true;
        },
        "Password must have one uppercase letter, number and a special character!");

    $.validator.addMethod("minAge", function (value, element, min) {
        var today = new Date();
        var birthDate = new Date(value);
        var age = today.getFullYear() - birthDate.getFullYear();

        if (age > min + 1) {
            return true;
        }

        var m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age >= min;
    }, "You are not old enough!");

    $('.date-picker').datetimepicker({
        format: 'YYYY-MM-DD',
        defaultDate: moment(),

    });

    $("#login").validate({
        rules: {
            email: {
                email: true,
                required: true
            },
            password: {
                required: true
            }
        }
    });

    $("#registration").validate({
        rules: {
            "user[plainPassword][first]": {
                passwordCheck: true,
                required: true,
                minlength: 6
            },
            "user[email]": {
                required: true,
                email: true
            },
            "user[birthday]": {
                required: true,
                minAge: 12
            },
            "user[name]": {
                required: true
            }
            ,
            "user[surname]": {
                required: true
            },
            "user[address]": {
                required: true
            },
            "user[country]": {
                required: true
            },
            "user[city]": {
                required: true
            },
            "user[username]": {
                required: true
            }
        },
        messages: {
            "user[plainPassword][first]": {
                minlength: "Your password must be 6 characters long!"
            },
        }
    });

    $("#resetPassword").validate({
        rules: {
            "user[plainPassword][first]": {
                passwordCheck: true,
                required: true,
                minlength: 6
            }
        },
        messages: {
            "user[plainPassword][first]": {
                minlength: "Your password must be 6 characters long!"
            },
        }
    });

    $("#user_reset_password").validate({
        rules: {
            "change_password_form[plainPassword][first]": {
                passwordCheck: true,
                required: true,
                minlength: 6
            }
        },
        messages: {
            "change_password_form[plainPassword][first]": {
                minlength: "Your password must be 6 characters long!"
            },
        }
    });
});

