<?php

/**
 * Build list of attributes into a string and apply contextual filter on string.
 *
 * The contextual filter is of the form `genesis_attr_{context}_output`.
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
		if ( true === $value ) {
			$output .= esc_html( $key ) . ' ';
		} else {
			$output .= sprintf( '%s="%s" ', esc_html( $key ), esc_attr( $value ) );
		}
	}
	return trim( $output );
}
