<?php

/**
 * Register custom taxonomies if Event Organiser Pro is active.
 *
 * @since   1.3.0
 *
 * @return  void.
 */
add_action( 'init', 'wampum_forms_register_custom_taxonomies' );
function wampum_forms_register_custom_taxonomies() {

	// Bail if Event Organiser Pro is not active.
	if ( ! function_exists( 'eventorganiser_pro_load_files' ) ) {
		return;
	}

	$labels = array(
		'name'                       => _x( 'AC List IDs', 'AC List IDs', 'wampum-forms' ),
		'singular_name'              => _x( 'AC List ID', 'AC List IDs', 'wampum-forms' ),
		'menu_name'                  => __( 'AC List IDs', 'wampum-forms' ),
		'all_items'                  => __( 'All Items', 'wampum-forms' ),
		'parent_item'                => __( 'Parent Item', 'wampum-forms' ),
		'parent_item_colon'          => __( 'Parent Item:', 'wampum-forms' ),
		'new_item_name'              => __( 'New Item Name', 'wampum-forms' ),
		'add_new_item'               => __( 'Add New Item', 'wampum-forms' ),
		'edit_item'                  => __( 'Edit Item', 'wampum-forms' ),
		'update_item'                => __( 'Update Item', 'wampum-forms' ),
		'view_item'                  => __( 'View Item', 'wampum-forms' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'wampum-forms' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'wampum-forms' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wampum-forms' ),
		'popular_items'              => __( 'Popular Items', 'wampum-forms' ),
		'search_items'               => __( 'Search Items', 'wampum-forms' ),
		'not_found'                  => __( 'Not Found', 'wampum-forms' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
		'description' => 'This show up?',
	);
	register_taxonomy( 'ac_list_id', array( 'event' ), $args );

	$labels = array(
		'name'                       => _x( 'AC Tags', 'AC Tags', 'wampum-forms' ),
		'singular_name'              => _x( 'AC Tag', 'AC Tags', 'wampum-forms' ),
		'menu_name'                  => __( 'AC Tags', 'wampum-forms' ),
		'all_items'                  => __( 'All Items', 'wampum-forms' ),
		'parent_item'                => __( 'Parent Item', 'wampum-forms' ),
		'parent_item_colon'          => __( 'Parent Item:', 'wampum-forms' ),
		'new_item_name'              => __( 'New Item Name', 'wampum-forms' ),
		'add_new_item'               => __( 'Add New Item', 'wampum-forms' ),
		'edit_item'                  => __( 'Edit Item', 'wampum-forms' ),
		'update_item'                => __( 'Update Item', 'wampum-forms' ),
		'view_item'                  => __( 'View Item', 'wampum-forms' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'wampum-forms' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'wampum-forms' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wampum-forms' ),
		'popular_items'              => __( 'Popular Items', 'wampum-forms' ),
		'search_items'               => __( 'Search Items', 'wampum-forms' ),
		'not_found'                  => __( 'Not Found', 'wampum-forms' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'ac_tag', array( 'event' ), $args );

}

/**
 * Send EO Booking data to AC.
 *
 * @since   1.3.0
 *
 * @link    http://codex.wp-event-organiser.com/function-eo_get_bookings.html
 *
 * @return  void.
 */
add_action( 'eventorganiser_confirmed_booking', 'wampum_forms_confirmed_booking' );
function wampum_forms_confirmed_booking( $booking_id ) {

	// Bail if AC is not connected.
	if ( ! Wampum_Forms()->submissions->is_active_campaign_connected() ) {
		return;
	}

	// Get the event ID.
	$email = eo_get_booking_meta( $booking_id, 'bookee_email', true );

	// Bail if no email.
	if ( ! $email ) {
		return;
	}

	// Start the data array.
	$data = array( 'email' => eo_get_booking_meta( $booking_id, 'bookee_email', true ) );

	$first_name = eo_get_booking_meta( $booking_id, 'bookee_first_name', true );
	$last_name  = eo_get_booking_meta( $booking_id, 'bookee_last_name', true );
	$event_id   = eo_get_booking_meta( $booking_id, 'event_id', true );

	// Maybe add first name.
	if ( $first_name ) {
		$data['first_name'] = $first_name;
	}

	// Maybe add last name.
	if ( $last_name ) {
		$data['last_name'] = $last_name;
	}

	// Maybe add AC List IDs.
	$lists = get_the_terms( $event_id, 'ac_list_id' );
	if ( ! empty( $lists ) ) {
		$ac_list_ids = '';
		foreach ( $lists as $list ) {
			$ac_list_ids .= $list->name . ', ';
		}
		$data['ac_list_ids'] = rtrim( $ac_list_ids, ', ' );
	}

	// Maybe add AC Tags.
	$tags = get_the_terms( $event_id, 'ac_tag' );
	if ( ! empty( $tags ) ) {
		$ac_tags = '';
		foreach ( $tags as $tag ) {
			$ac_tags .= $tag->name . ', ';
		}
		$data['ac_tags'] = rtrim( $ac_tags, ', ' );
	}

	// Setup AC
	$ac = Wampum_Forms()->submissions->get_active_campaign_object();

	// If valid AC object
	if ( $ac ) {

		// Start the contact array, email is required in our form
		$contact = wampum_forms_setup_ac_contact( $data );

		// // Maybe add Event ID field.
		// $event_id_field = array(
		// 	'title'   => 'Event ID',
		// 	'type'    => 1,
		// 	'req'     => 0,
		// 	'perstag' => 'EVENT_ID',
		// 	'p[0]'    => 0,
		// );
		// $maybe_add_event_id_field = $ac->api( 'list/field_add', $event_id_field );

		// // Maybe add Event Title field.
		// $event_title_field = array(
		// 	'title'   => 'Event Title',
		// 	'type'    => 1,
		// 	'req'     => 0,
		// 	'perstag' => 'EVENT_TITLE',
		// 	'p[0]'    => 0,
		// );
		// $maybe_add_event_title_field = $ac->api( 'list/field_add', $event_title_field );

		// Add Event ID custom field.
		// $contact['field[%EVENT_ID%,0]'] = $event_id;

		// Add Event Title custom field.
		// $contact['field[%EVENT_TITLE%,0]'] = get_the_title( $event_id );

		/**
		 * Add filter so devs can add additional data to the $contact array to send to AC.
		 *
		 * @var  array   $contact  The contact data to send to AC.
		 * @var  object  $ac       The AC object.
		 */
		$contact = apply_filters( 'wampum_forms_booking_data', $contact, $ac );

		// Do the thang
		$contact_sync = $ac->api( 'contact/sync', $contact );

	}

}
