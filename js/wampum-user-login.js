;(function( $ ) {
    'use strict';

    $( '#wampum_user_login_form' ).submit(function(e){

        e.preventDefault();

        // Hide any notices
        $( '#wampum_user_login_form' ).find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                user_login: $( '#wampum_user_login_form' ).find( '#wampum_user_login' ).val(),
                user_password: $( '#wampum_user_login_form' ).find( '#wampum_user_pass' ).val(),
                remember: $( '#wampum_user_login_form' ).find( '#wampum_rememberme' ).val(),
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_login.root + 'wampum/v1/login/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_login.nonce );
            },
            success: function( response ) {
                if ( response.success == true ) {
                    // Display success message
                    $('#wampum_user_login_form').hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                        // Refresh/redirect
                        window.location.replace( $( '#wampum_user_login_form' ).find( 'input[name="redirect_to"]' ).val() );
                    });
                } else {
                    // Display error message
                    $('#wampum_user_login_form').hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                $('#wampum_user_login_form').hide().prepend('<div class="wampum-notice error">' + wampum_user_login.failure + '</div>').fadeIn('fast');
            }
        });

    });

})( jQuery );
