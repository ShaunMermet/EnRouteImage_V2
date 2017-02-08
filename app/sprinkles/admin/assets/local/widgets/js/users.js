/**
 * Users widget.  Sets up dropdowns, modals, etc for a table of users.
 */

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachUserForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        // Set up any widgets inside the modal
        form.find(".select2").select2({
            width: '100%'
        });

        // Set up the form for submission
        form.ufForm({
            validators: page.validators
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}

/**
 * Enable/disable password fields when switch is toggled
 */
function toggleChangePasswordMode(el, userName, changePasswordMode) {
    if (changePasswordMode == 'link') {
        $(".controls-password").find("input[type='password']").prop('disabled', true);
        // Form submits password reset request
        var form = el.find("form");
        form
        .prop('method', 'POST')
        .prop('action', site.uri.public + '/api/users/u/' + userName + '/password-reset');

        var validator = form.validate();
        if (validator) {
            //Iterate through named elements inside of the form, and mark them as error free
            el.find("input[type='password']").each(function() {
              validator.successList.push(this); //mark as error free
            });
            validator.resetForm();//remove error class on name elements and clear history
            validator.reset();//remove all error and success data
        }
        el.find("input[type='password']").closest('.form-group')
        .removeClass('has-error has-success');
        el.find('.form-control-feedback').each(function () {
            $(this).remove();
        });
    } else {
        $(".controls-password").find("input[type='password']").prop('disabled', false);
        // Form submits direct password update
        el.find("form")
        .prop('method', 'PUT')
        .prop('action', site.uri.public + '/api/users/u/' + userName + '/password');
    }
}

/**
 * Update user field(s)
 */
function updateUser(userName, fieldName, fieldValue) {
	var data = {
        'value': fieldValue
    };

    data[site.csrf.keys.name] = site.csrf.name;
    data[site.csrf.keys.value] = site.csrf.value;

    var url = site.uri.public + '/api/users/u/' + userName + '/' + fieldName;

    return $.ajax({
        type: "PUT",
        url: url,
        data: data
	}).fail(function (response) {
        // Error messages
        if ((typeof site !== "undefined") && site.debug.ajax && response.responseText) {
            document.write(response.responseText);
            document.close();
        } else {
            if (base.options.DEBUG) {
                console.log("Error (" + response.status + "): " + response.responseText );
            }
        }

        return response;
    }).always(function (response) {
        window.location.reload();
    });
}

/**
 * Link user action buttons, for example in a table or on a specific user's page.
 */
 function bindUserButtons(el) {

    /**
     * Buttons that launch a modal dialog
     */
    // Edit general user details button
    el.find('.js-user-edit').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/edit",
            ajaxParams: {
                user_name: $(this).data('user_name')
            },
            msgTarget: $("#alerts-page")
        });

        attachUserForm();
    });

    // Manage user roles button
    el.find('.js-user-roles').click(function() {
        var userName = $(this).data('user_name');
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/roles",
            ajaxParams: {
                user_name: userName
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function (data) {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            // Set up collection widget
            var roleWidget = modal.find('.js-form-roles');
            roleWidget.ufCollection({
                dataUrl         : site.uri.public + '/api/roles',
                dropdownTemplate: modal.find('#user-roles-select-option').html(),
                rowTemplate     : modal.find('#user-roles-row').html(),
                placeholder     : "Select a role"
            });

            // Get current roles and add to widget
            $.getJSON(site.uri.public + '/api/users/u/' + userName + '/roles')
            .done(function (data) {
                $.each(data.rows, function (idx, role) {
                    role.text = role.name;
                    roleWidget.ufCollection('addRow', role);
                });
            });

            // Set up form for submission
            form.ufForm({
            }).on("submitSuccess.ufForm", function() {
                // Reload page on success
                window.location.reload();
            });
        });
    });

    // Change user password button
    el.find('.js-user-password').click(function() {
        var userName = $(this).data('user_name');
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/password",
            ajaxParams: {
                user_name: userName
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function (data) {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            // Set up form for submission
            form.ufForm({
                validators: page.validators
            }).on("submitSuccess.ufForm", function() {
                // Reload page on success
                window.location.reload();
            });

            toggleChangePasswordMode(modal, userName, 'link');

            // On submission, submit either the PUT request, or POST for a password reset, depending on the toggle state
            modal.find("input[name='change_password_mode']").click(function() {
                var changePasswordMode = $(this).val();
                toggleChangePasswordMode(modal, userName, changePasswordMode);
            });
        });
    });

    // Delete user button
    el.find('.js-user-delete').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/confirm-delete",
            ajaxParams: {
                user_name: $(this).data('user_name')
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function (data) {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
            .on("submitSuccess.ufForm", function() {
                // Reload page on success
                window.location.reload();
            });
        });
    });

    /**
     * Direct action buttons
     */
    el.find('.js-user-activate').click(function() {
        var btn = $(this);
        updateUser(btn.data('user_name'), 'flag_verified', '1');
    });

    el.find('.js-user-enable').click(function () {
        var btn = $(this);
        updateUser(btn.data('user_name'), 'flag_enabled', '1');
    });

    el.find('.js-user-disable').click(function () {
        var btn = $(this);
        updateUser(btn.data('user_name'), 'flag_enabled', '0');
    });
}

function bindUserCreationButton(el) {
    // Link create button
    el.find('.js-user-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/create",
            msgTarget: $("#alerts-page")
        });

        attachUserForm();
    });
};
