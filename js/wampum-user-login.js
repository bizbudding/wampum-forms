;(function( $ ) {
    'use strict';

    var LoginForm = $( '#wampum_user_login_form' );

    LoginForm.submit(function(e){

        e.preventDefault();

        // Set button as a variable
        var button = LoginForm.find( '#wampum_submit' );
        // Disable the button
        // NOTE: We can't do the fun loading icon because it's not a button, it's an input[type="submit"]
        button.attr( 'disabled', true );

        // Hide any notices
        LoginForm.find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                user_login: LoginForm.find( '#wampum_user_login' ).val(),
                user_password: LoginForm.find( '#wampum_user_pass' ).val(),
                remember: LoginForm.find( '#wampum_rememberme' ).val(),
            };

        // Display an error if username and password fields are emmpty. Why is those fields not required in WP core?
        if ( ! ( data.user_login && data.user_password ) ) {
            LoginForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_login.empty + '</div>').fadeIn('fast');
            // Re-enable the button
            button.attr( 'disabled', false );
            // Stop the submission!
            return false;
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_login.root + 'wampum/v1/login/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_login.nonce );
            },
            success: function( response ) {
                console.log(response);
                if ( response.success == true ) {
                    // Display success message
                    LoginForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                        // Refresh/redirect
                        window.location.replace( LoginForm.find( 'input[name="redirect_to"]' ).val() );
                    });
                } else {
                    // Display error message
                    LoginForm.hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                LoginForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_login.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
                // Re-enable the button
                button.html(button_html).attr( 'disabled', false );
            }
        });

    });

})( jQuery );
