;(function( $ ) {
    'use strict';

    $( '#wampum_user_password_form' ).submit(function(e){

        e.preventDefault();

        console.log('Test');

        // Hide any notices
        $( '#wampum_user_password_form' ).find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                password: $( '#wampum_user_password_form' ).find( '#wampum_user_password' ).val(),
                password_confirm: $( '#wampum_user_password_form' ).find( '#wampum_user_password_confirm' ).val(),
                user_id: $( '#wampum_user_password_form' ).find( '#wampum_user_id' ).val(),
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_password.root + 'wampum/v1/password/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_password.nonce );
            },
            success: function( response ) {
                if ( response.success == true ) {
                    // Display success message
                    $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                        // Refresh/redirect
                        window.location.replace( $( '#wampum_user_password_form' ).find( 'input[name="redirect_to"]' ).val() );
                    });
                } else {
                    // Display error message
                    $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice success">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                $('#wampum_user_password_form').hide().prepend('<div class="wampum-notice success">' + wampum_user_password.failure + '</div>').fadeIn('fast');
            }
        });

    });

})( jQuery );
