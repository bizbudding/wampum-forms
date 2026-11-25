<?php

/**
 * Get a login form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_form( $args = [] ) {
	return Wampum_Forms()->forms->get_form( $args );
}

/**
 * Get a login form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_login_form( $args = [] ) {
	return Wampum_Forms()->forms->login_form_callback( $args );
}

/**
 * Get a registration form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_register_form( $args = [] ) {
	return Wampum_Forms()->forms->register_form_callback( $args );
}

/**
 * Get a password form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_password_form( $args = [] ) {
	return Wampum_Forms()->forms->password_form_callback( $args );
}

/**
 * Get a subscribe form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_subscribe_form( $args = [] ) {
	return Wampum_Forms()->forms->subscribe_form_callback( $args );
}


/**
 * Get a membership form
 *
 * @since  1.0.0
 *
 * @param  array   $args  Args to configure form
 *
 * @return string  The form
 */
function wampum_get_membership_form( $args = [] ) {
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
function wampum_attr( $attributes = [] ) {
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

/**
 * Setup the Active Campaign contact data array.
 * We need a helper function to help sanitize fields,
 * and properly format comma separated IDs and tags to the way AC expects the data.
 *
 * @since   1.3.0
 *
 * @param   array  $data  The contact data array.
 *
 * @return  array        [description]
 */
function wampum_forms_setup_ac_contact( $data ) {

	$contact = [];

	if ( isset( $data['email'] ) && $data['email'] ) {
		$contact = [ 'email' => sanitize_email( $data['email'] ) ];
	}

	if ( isset( $data['first_name'] ) && $data['first_name'] ) {
		$contact['first_name'] = sanitize_text_field( $data['first_name'] );
	}

	if ( isset( $data['last_name'] ) && $data['last_name'] ) {
		$contact['last_name'] = sanitize_text_field( $data['last_name'] );
	}

	if ( isset( $data['ac_list_ids'] ) && $data['ac_list_ids'] ) {

		$list_ids = explode( ',', $data['ac_list_ids'] );

		// If we have list(s)
		if ( ! empty( $list_ids ) ) {
			// Add user to existing ActiveCampaign lists
			foreach( $list_ids as $list_id ) {
				$list_id = trim( $list_id );
				$contact["p[{$list_id}]"]      = $list_id;
				$contact["status[{$list_id}]"] = 1; // "Active" status
			}
		}

	}

	if ( isset( $data['ac_tags'] ) && $data['ac_tags'] ) {

		$tags = explode( ',', $data['ac_tags'] );

		// If we have tags
		if ( ! empty( $tags ) ) {
			// Add tags to user
			foreach( $tags as $tag ) {
				$tag = trim( $tag );
				$contact["tags[{$tag}]"] = sanitize_text_field( $tag );
			}
		}
	}

	return $contact;
}

/**
 * Get the signature for the values a form sets itself.
 *
 * The form outputs this in a hidden wampum_sig field, and the REST route
 * builds it again from the request. Both sides must use this function,
 * so the two can never build it differently.
 *
 * @since  1.5.1
 *
 * @param  string  $form_type  The form type: login, password, register, subscribe or membership.
 * @param  array   $values     The signed values, keyed by field name. Missing keys count as empty.
 *
 * @return string  The signature.
 */
function wampum_forms_get_signature( string $form_type, $values ): string {
	$parts = [ $form_type ];

	foreach ( wampum_forms_get_signed_fields() as $field ) {
		$value   = $values[ $field ] ?? '';
		$parts[] = is_scalar( $value ) ? (string) $value : '';
	}

	// JSON keeps each value whole, so a "|" inside one value cannot shift text into the next.
	return wp_hash( (string) wp_json_encode( $parts ) );
}

/**
 * Get the field names whose values the site sets and the request must not change.
 *
 * @since  1.5.1
 *
 * @return array
 */
function wampum_forms_get_signed_fields(): array {
	return [ 'plan_id', 'ac_list_ids', 'ac_tags', 'notifications' ];
}
