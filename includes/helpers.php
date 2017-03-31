<?php

/**
 * Get a login form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_form( $args = array() ) {
	return Wampum_Forms()->forms->get_form( $args );
}

/**
 * Get a login form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_login_form( $args = array() ) {
	return Wampum_Forms()->forms->login_form_callback( $args );
}

/**
 * Get a registration form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_register_form( $args = array() ) {
	return Wampum_Forms()->forms->register_form_callback( $args );
}

/**
 * Get a password form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_password_form( $args = array() ) {
	return Wampum_Forms()->forms->password_form_callback( $args );
}

/**
 * Get a subscribe form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_subscribe_form( $args = array() ) {
	return Wampum_Forms()->forms->subscribe_form_callback( $args );
}


/**
 * Get a membership form
 *
 * @since  1.0.0
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_membership_form( $args = array() ) {
	return Wampum_Forms()->forms->membership_form_callback( $args );
}

/**
 * Build list of attributes into a string and apply contextual filter on string.
 *
 * The contextual filter is of the form `genesis_attr_{context}_output`.
 *
 * @since  1.1.0
 *
 * @param  array   $attributes  Optional. Extra attributes to merge with defaults.
 *
 * @return string  String of HTML attributes and values.
 */
function wampum_attr( $attributes = array() ) {
	$output	= '';
	// Cycle through attributes, build tag attribute string.
	foreach ( $attributes as $key => $value ) {
		if ( ! $value ) {
			continue;
		}
		/**
		 * if true (not 'true')
		 * some params, like 'log_in' we want the value to be "true"
		 */
		if ( true === $value ) {
			$output .= esc_html( $key ) . ' ';
		} else {
			$output .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
		}
	}
	return trim( $output );
}
