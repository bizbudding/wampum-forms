;(function( $ ) {
    'use strict';

    $( '#wampum_user_membership_form' ).submit(function(e){

        e.preventDefault();

        // Hide any notices
        $( '#wampum_user_membership_form' ).find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                plan_id: $( '#wampum_user_membership_form' ).find( '#wampum_plan_id' ).val(),
                first_name: $( '#wampum_user_membership_form' ).find( '#wampum_membership_name' ).val(),
                user_email: $( '#wampum_user_membership_form' ).find( '#wampum_membership_email' ).val(),
                say_what: $( '#wampum_user_membership_form' ).find( '#wampum_say_what' ).val(), // honeypot
                redirect: $( '#wampum_user_membership_form' ).find( 'input[name="redirect_to"]' ).val(),
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_membership.root + 'wampum/v1/membership/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_membership.nonce );
            },
            success: function( response ) {
                console.log(response);
                if ( response.success == true ) {
                    // Display success message
                    $('#wampum_user_membership_form').hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                        // Refresh/redirect
                        window.location.replace( response.redirect );
                    });
                } else {
                    // Display error message
                    $('#wampum_user_membership_form').hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                console.log(response);
                // Not sure when this would happen, but fallbacks!
                $('#wampum_user_membership_form').hide().prepend('<div class="wampum-notice error">' + wampum_user_membership.failure + '</div>').fadeIn('fast');
            }
        });

    });

})( jQuery );
