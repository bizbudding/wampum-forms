;(function( $ ) {
    'use strict';

    var strength = {
        0: "Weak",
        1: "Weak",
        2: "Okay",
        3: "Good",
        4: "Great",
    }

    var field = document.getElementById('wampum_user_password');
    var meter = $( '#wampum_user_password_form' ).find( '.password-strength-meter' );
    var text  = $( '#wampum_user_password_form' ).find( '.password-strength-text' );

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


    $( '#wampum_user_password_form' ).submit(function(e){

        e.preventDefault();

        // Set button as Working...

        // Hide any notices
        $( '#wampum_user_password_form' ).find('.wampum-notice').fadeOut('fast');

        var password = $( '#wampum_user_password_form' ).find( '#wampum_user_password' ).val();
        var confirm  = $( '#wampum_user_password_form' ).find( '#wampum_user_password_confirm' ).val();

        if ( password !== confirm ) {
            $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice error">' + wampum_user_password.mismatch + '</div>').fadeIn('fast');
            return false;
        }

        // Setup our form data array
        var data = {
                password: $( '#wampum_user_password_form' ).find( '#wampum_user_password' ).val(),
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
                $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                    // Refresh/redirect
                    window.location.replace( $( '#wampum_user_password_form' ).find( 'input[name="redirect_to"]' ).val() );
                });
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice error">' + wampum_user_password.failure + '</div>').fadeIn('fast');
            }
        });

    });

})( jQuery );
