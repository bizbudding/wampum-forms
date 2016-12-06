;(function( $ ) {
    'use strict';

    var PasswordForm = $( '#wampum_user_password_form' );

    var strength = {
        0: "Weak",
        1: "Weak",
        2: "Okay",
        3: "Good",
        4: "Great",
    }

    var field = document.getElementById('wampum_user_password');
    var meter = PasswordForm.find( '.password-strength-meter' );
    var text  = PasswordForm.find( '.password-strength-text' );

    field.addEventListener( 'input', function() {

        var val    = field.value;
        var result = zxcvbn(val);

        // Update the password strength meter
        meter.attr('data-strength', result.score);

        // Update the text indicator
        if ( val !== "" ) {
            text.html(strength[result.score]);
        } else {
            text.html("");
        }

    });

    PasswordForm.submit(function(e){

        e.preventDefault();

        // Set button as a variable
        var button = PasswordForm.find( '#wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
        PasswordForm.find('.wampum-notice').fadeOut('fast');

        var password = PasswordForm.find( '#wampum_user_password' ).val();
        var confirm  = PasswordForm.find( '#wampum_user_password_confirm' ).val();

        if ( password !== confirm ) {
            PasswordForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_password.mismatch + '</div>').fadeIn('fast');
            // Re-enable the button
            button.html(button_html).attr( 'disabled', false );
            // Stop the submission!
            return false;
        }

        // Setup our form data array
        var data = {
                password: PasswordForm.find( '#wampum_user_password' ).val(),
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_password.root + 'wp/v2/users/' + wampum_user_password.current_user_id,
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_password.nonce );
            },
            success: function( response ) {
                // Display success message
                PasswordForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                    // Refresh/redirect
                    window.location.replace( PasswordForm.find( 'input[name="redirect_to"]' ).val() );
                });
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                PasswordForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_password.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
                // Re-enable the button
                button.html(button_html).attr( 'disabled', false );
            }
        });

    });

})( jQuery );
