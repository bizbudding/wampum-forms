;(function( $ ) {
    'use strict';

    var strength = {
        0: "Bad",
        1: "Bad",
        2: "Weak",
        3: "Good",
        4: "Strong",
    }

    // var password_field = $( '#wampum_user_password_form' ).find( '#wampum_user_password' );
    var password_field = document.getElementById('wampum_user_password');
    var meter = document.getElementById('password-strength-meter');
    var text  = document.getElementById('password-strength-text');

    password_field.addEventListener( 'input', function() {

        // console.log(password_field);

        // if ( password_field.val() === 0 ) {
        //     meter.removeClass('hasvalue');
        // } else {
        //     meter.addClass('hasvalue');
        // }

        var val    = password_field.value;
        var result = zxcvbn(val);

        // Update the password strength meter
        meter.value = result.score;

        // Update the text indicator
        if ( val !== "" ) {
            text.innerHTML = strength[result.score];
        } else {
            text.innerHTML = "";
        }

    });

    $( '#wampum_user_password_form' ).submit(function(e){

        e.preventDefault();

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
