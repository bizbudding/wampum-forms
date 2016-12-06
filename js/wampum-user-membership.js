;(function( $ ) {
    'use strict';

    var MembershipForm = $( '#wampum_user_membership_form' );

    MembershipForm.submit(function(e){

        e.preventDefault();

        // Set button as a variable
        var button = MembershipForm.find( '#wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
        MembershipForm.find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                plan_id: MembershipForm.find( '#wampum_plan_id' ).val(),
                first_name: MembershipForm.find( '#wampum_membership_first_name' ).val(),
                last_name: MembershipForm.find( '#wampum_membership_last_name' ).val(),
                user_email: MembershipForm.find( '#wampum_membership_email' ).val(),
                username: MembershipForm.find( '#wampum_membership_username' ).val(),
                password: MembershipForm.find( '#wampum_membership_password' ).val(),
                say_what: MembershipForm.find( '#wampum_say_what' ).val(), // honeypot
                redirect: MembershipForm.find( 'input[name="redirect_to"]' ).val(),
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = MembershipForm.find( '#wampum_ss_baseuri' ).val().trim();
        var SharpSpringEndpoint = MembershipForm.find( '#wampum_ss_endpoint' ).val().trim();
        var urlParams = MembershipForm.serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            MembershipForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_membership.root + 'wampum/v1/membership/',
            data: data,
            // async: false,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_membership.nonce );
            },
            success: function( response ) {
                console.log(response);

                if ( response.success == true ) {

                    // If this is a SharpSpring form, send that data!
                    if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
                        // https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
                        $.ajax({
                            url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
                            contentType: "application/json",
                            dataType: 'jsonp',
                            success: function( response ) {
                                console.log(response);
                            }
                        });
                    }

                    // Display success message
                    MembershipForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                        // Refresh/redirect
                        window.location.replace( response.redirect );
                    });

                } else {
                    // Display error message
                    MembershipForm.hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                // console.log(response);
                // Not sure when this would happen, but fallbacks!
                MembershipForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_membership.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
                // Re-enable the button
                button.html(button_html).attr( 'disabled', false );
            }
        });

    });

})( jQuery );
