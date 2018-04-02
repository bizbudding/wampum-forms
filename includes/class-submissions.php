<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Wampum_Forms_Submissions Class.
 *
 * @since 1.1.0
 */
final class Wampum_Forms_Submissions {

	/**
	 * Singleton
	 * @var   Wampum_Forms_Submissions The one true Wampum_Forms_Submissions
	 * @since 1.1.0
	 */
	private static $instance;

	/**
	 * Main Wampum_Forms_Submissions Instance.
	 *
	 * Insures that only one instance of Wampum_Forms_Submissions exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   1.1.0
	 * @static  var array $instance
	 * @uses    Wampum_Forms_Submissions->setup() load the language files.
	 * @return  object | Wampum_Forms_Submissions The one true Wampum_Forms_Submissions
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new Wampum_Forms_Submissions;
			// Methods
			self::$instance->setup();
		}
		return self::$instance;
	}

	function setup() {

		// Register WP-API endpoint
		add_action( 'rest_api_init', array( $this, 'register_rest_endpoints' ) );

	}

	/**
	 * Register rest endpoint
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	function register_rest_endpoints() {

		/* *** *
		 * GET *
		 * *** */

		register_rest_route( 'wampum/v1', '/login/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/password/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/register/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/subscribe/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/user-available/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/membership-add/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

		register_rest_route( 'wampum/v1', '/active-campaign/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'is_active_campaign_connected' ),
		));

		/* **** *
		 * POST *
		 * **** */

		register_rest_route( 'wampum/v1', '/login/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'login' ),
		));

		register_rest_route( 'wampum/v1', '/password/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_password' ),
		));

		register_rest_route( 'wampum/v1', '/register/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'register' ),
		));

		register_rest_route( 'wampum/v1', '/subscribe/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'subscribe' ),
		));

		register_rest_route( 'wampum/v1', '/user-available/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'user_available' ),
		));

		register_rest_route( 'wampum/v1', '/membership-add/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'membership_add' ),
		));

	}

	/**
	 * This function displays a message when visiting the endpoint, to confirm it's actually registered
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	function status() {
		return array(
			'success' => true,
			'message' => 'All is well in the world of Wampum',
		);
	}

	/**
	 * Login a user
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  string  $username  Username
	 *      @type  string  $password  Password
	 *      @type  string  $remember  Stay logged in
	 *      @type  string  $say_what  Honeypot
	 * }
	 *
	 * @return  array
	 */
	function login( $data = array() ) {

		// Honeypot
		$spam = $this->validate_say_what($data);
		if ( false == $spam['success'] ) {
			return $spam;
		}

		// Validate submission
		$valid = $this->valid_submission( 'login', $data );

		// Return with message if not a valid submission
		if ( true !== $valid ) {
			return array(
				'success' => false,
				'message' => $valid,
			);
		}

		$user = wp_signon( $data );

		// If error
		if ( is_wp_error( $user ) ) {
			return array(
				'success' => false,
				'message' => $user->get_error_message(),
			);
		}
		// Success
		else {

			wp_set_current_user( $user->ID );
			if ( wp_validate_auth_cookie( '', 'logged_in' ) != $user->ID ) {
				wp_set_auth_cookie( $user->ID, true );
			}

			// Notifications
			$this->maybe_do_notifications( 'Login', $data );

			// ActiveCampaign
			$this->maybe_do_active_campaign( $data );

			// Hook to run custom code after successful submission (login forms only)
			do_action( 'wampum_form_after_login_submission', $data );

			// Hook to run custom code after successful submission (all forms)
			do_action( 'wampum_form_after_submission', $data );

			return array(
				'success' => true,
			);
		}
	}

	/**
	 * Save a user password
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  string  $password          Password
	 *      @type  string  $password_confirm  Password again
	 *      @type  string  $say_what          Honeypot
	 * }
	 *
	 * @return  array
	 */
	function save_password( $data = array() ) {

		// Honeypot
		$spam = $this->validate_say_what($data);
		if ( false == $spam['success'] ) {
			return $spam;
		}

		// Bail if not logged in. Not sure how anyone would get here, but let's be safe
		if ( ! is_user_logged_in() ) {
			return array(
				'success' => false,
				'message' => __( 'You must be logged in to save a password', 'wampum' ),
			);
		}

		// If both fields are empty
		if ( $data['password'] == '' || $data['password_confirm'] == '' ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a password and confirm it', 'wampum' ),
			);
		}

		// If passwords do not match
		if ( $data['password'] != $data['password_confirm'] ) {
			return array(
				'success' => false,
				'message' => __( 'Passwords do not match', 'wampum' ),
			);
		}

		// Validate submission
		$valid = $this->valid_submission( 'password', $data );

		// Return with message if not a valid submission
		if ( true !== $valid ) {
			return array(
				'success' => false,
				'message' => $valid,
			);
		}

		$user_data = array(
			'ID'        => get_current_user_id(),
			'user_pass' => $data['password']
		);
		$user_id = wp_update_user($user_data);

		// If error
		if ( is_wp_error( $user_id ) ) {
			return array(
				'success' => false,
				'message' => $user_id->get_error_message(),
			);
		}

		// Notifications
		$this->maybe_do_notifications( 'Password', $data );

		// ActiveCampaign
		$this->maybe_do_active_campaign( $data );

		// Hook to run custom code after successful submission (password forms only)
		do_action( 'wampum_form_after_password_submission', $data );

		// Hook to run custom code after successful submission (all forms)
		do_action( 'wampum_form_after_submission', $data );

		// Success
		return array(
			'success' => true,
		);
	}

	/**
	 * Register a user
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  string  $email        Email (required)
	 *      @type  string  $username     Username
	 *      @type  string  $first_name   First Name
	 *      @type  string  $last_name    Last Name
	 *      @type  string  $password     Password
	 *      @type  bool    $log_in       Whether to auto log user in after registration
	 *      @type  string  $ac_list_ids  Comma separated list IDs to add
	 *      @type  string  $ac_tags      Comma separated tags to add
	 *      @type  string  $say_what     Honeypot
	 *
	 * }
	 *
	 * @return  array
	 */
	function register( $data = array() ) {

		// Honeypot
		$spam = $this->validate_say_what($data);
		if ( false == $spam['success'] ) {
			return $spam;
		}

		// Bail and return error if no email
		if ( isset( $data['email'] ) && ! empty( $data['email'] ) ) {
			$email = $data['email'];
		} else {
			return array(
				'success' => false,
				'message' => __( 'Email is missing', 'wampum' ),
			);
		}

		/**
		 * If password and password_confirm are set.
		 * Need to check if isset because this is also called from membership flow
		 * and there is no password confirm there.
		 */
		if ( isset($data['password']) && isset($data['password_confirm']) ) {
			if ( empty($data['password']) && empty($data['password_confirm']) ) {
				return array(
					'success' => false,
					'message' => __( 'Please enter a password', 'wampum' ),
				);
			}
			// Bail and return error if passwords don't match.
			if ( $data['password'] != $data['password_confirm'] ) {
				return array(
					'success' => false,
					'message' => __( 'Passwords do not match', 'wampum' ),
				);
			}
		}

		// Validate submission
		$valid = $this->valid_submission( 'register', $data );

		// Return with message if not a valid submission
		if ( true !== $valid ) {
			return array(
				'success' => false,
				'message' => $valid,
			);
		}

		/**
		 * Start the new user data
		 * Email is the only field required to exist in the form
		 */
		$userdata = array(
			'user_email' => $email,
		);

		// If we have a first name, set it
		if ( $data['first_name'] ) {
			$userdata['first_name'] = $data['first_name'];
		}

		// If we have a last name, set it
		if ( $data['last_name'] ) {
			$userdata['last_name'] = $data['last_name'];
		}

		// Set username. Set as variable first, cause we may need it later for wp_signon()
		$username = ! empty( $data['username'] ) ? $data['username'] : $email;
		$userdata['user_login'] = $username;

		// Set password. Set as variable first, cause we may need it later for wp_signon()
		$password = ! empty( $data['password'] ) ? $data['password'] : wp_generate_password( $length = 12, $include_standard_special_chars = true );
		$userdata['user_pass'] = $password;

		/**
		 * Currently can't choose role, because
		 * we can't put that data as a hidden field
		 * or we risk a user changing it.
		 *
		 * So, let's add a filter!
		 */
		$userdata['role'] = apply_filters( 'wampum_register_form_user_role', 'subscriber' );

		// Create a new user.
		$user_id = wp_insert_user( $userdata );

		// If it's an error, return it
		if ( is_wp_error( $user_id ) ) {
			return array(
				'success' => false,
				'message' => $user_id->get_error_message(),
			);
		}

		// If log_in is true
		if ( filter_var( $data['log_in'], FILTER_VALIDATE_BOOLEAN ) ) {

			// Log them in!
			$signon_data = array(
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => true,
			);
			$user = wp_signon( $signon_data );

			if ( ! is_wp_error( $user ) ) {
				/**
				 * Set the current wp user.
				 * When using incognito window (or other situation i'm sure),
				 * the user wasn't fully logged in and would hit Content Restricted
				 * error when landing on a protected page (Woo Memberships).
				 */
				wp_set_current_user($user_id);
			} else {
				return array(
					'success' => false,
					'message' => $user->get_error_message(),
				);
			}

		}

		// Notifications
		$this->maybe_do_notifications( 'Register', $data );

		// ActiveCampaign
		$this->maybe_do_active_campaign( $data );

		// Hook to run custom code after successful submission (register forms only)
		do_action( 'wampum_form_after_register_submission', $data );

		// Hook to run custom code after successful submission (all forms)
		do_action( 'wampum_form_after_submission', $data );

		// Success
		return array(
			'success' => true,
			'user_id' => $user_id, // return user ID for use in membership flow
		);

	}

	/**
	 * Subscribe a user to something.
	 * Currently only ActiveCampaign supported.
	 *
	 * @since   1.1.0
	 *
	 * @param   array  $data  Array of data to check user
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  string  $email          Email (required)
	 *      @type  string  $first_name     First Name
	 *      @type  string  $last_name      Last Name
	 *      @type  string  $ac_list_ids    Comma separated list IDs to add
	 *      @type  string  $ac_tags        Comma separated tags to add
	 *      @type  string  $notifications  Comma separated tags to add
	 *      @type  string  $say_what       Honeypot
	 *
	 * }
	 *
	 *
	 * @return  bool|WP_Error  Whether a new user was created during the process
	 */
	function subscribe( $data = array() ) {

		// ActiveCampaign
		$ac = $this->maybe_do_active_campaign( $data );

		if ( true == $ac['success'] ) {

			// Validate submission
			$valid = $this->valid_submission( 'subscribe', $data );

			// Return with message if not a valid submission
			if ( true !== $valid ) {
				return array(
					'success' => false,
					'message' => $valid,
				);
			}

			// Notifications
			$this->maybe_do_notifications( 'Subscribe', $data );

			// Hook to run custom code after successful submission (subscribe forms only)
			do_action( 'wampum_form_after_subscribe_submission', $data );

			// Hook to run custom code after successful submission (all forms)
			do_action( 'wampum_form_after_submission', $data );

			// Success!
			return array(
				'success' => true,
			);

		}

		return $ac;

	}

	/**
	 * Verify a user account doesn't already exist
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $data  Array of data to check user
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  string   $email        (required) User email
	 *      @type  string   $username     Username
	 *      @type  string   $say_what     Honeypot
	 *      @type  string   $current_url  Current URL set in wp_localize_script() because calling here returns WP-API endpoing URL
	 * }
	 *
	 *
	 * @return  bool|WP_Error  Whether a new user was created during the process
	 */
	function user_available( $data ) {

		// Honeypot
		$spam = $this->validate_say_what($data);
		if ( false == $spam['success'] ) {
			return $spam;
		}

		// Email is required
		if ( ! ( isset($data['email']) || $data['email'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter your email address', 'wampum' ),
			);
		}

		// Validate submission
		$valid = $this->valid_submission( 'membership', $data );

		// Return with message if not a valid submission
		if ( true !== $valid ) {
			return array(
				'success' => false,
				'message' => $valid,
			);
		}

		$email = sanitize_email($data['email']);

		$email_exists = email_exists( $email );
		// Username is not required, so check it first
		$username_exists = isset($data['username']) ? username_exists( $data['username'] ) : false;

		// If the email or username is already a registered user
		if ( $email_exists || $username_exists ) {
			// Set in wp_localize_script() because calling here returns WP-API endpoing URL
			$current_url = $data['current_url'];
			// return error with link to login
			return array(
				'success' => false,
				'message' => __( 'This user account already exists.', 'wampum' ) . ' <a class="login-link" href="' . wp_login_url( $current_url ) . '" title="Log in">Log in?</a>',
			);
		}

		// Success!
		return array(
			'success' => true,
		);

	}

	/**
	 * Add membership to user
	 * If user doesn't exists, create one first
	 *
	 * @since   1.0.0
	 *
	 * @param   array  $data  Array of data when maybe creating a user and adding a membership to a user
	 *
	 * @param   array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 *      @type  integer  $plan_id        (required) The WooCommerce Memberships ID
	 *      @type  string   $email          (required) User email
	 *      @type  string   $first_name     First name
	 *      @type  string   $last_name      Last name
	 *      @type  string   $username       Username
	 *      @type  string   $password       Password
	 *      @type  string   $login          'yes' or 'no'
	 *      @type  string   $notifications  Comma-separated list of emails to notify upons successful submission
	 *      @type  string   $ac_list_ids    Comma separated list IDs to add
	 *      @type  string   $ac_tags        Comma separated tags to add
	 *      @type  string   $say_what       Honeypot field
	 *      @type  string   $current_url    Current URL set in wp_localize_script() because calling here returns WP-API endpoing URL
	 * }
	 *
	 * @return  bool|WP_Error  Whether a new user was created during the process
	 */
	function membership_add( $data ) {

		// Honeypot
		$spam = $this->validate_say_what($data);
		if ( false == $spam['success'] ) {
			return $spam;
		}

		// Bail if Woo Memberships is not active
		if ( ! function_exists( 'wc_memberships' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Membership is currently inactive', 'wampum' ),
			);
		}

		// Minimum data we need is a plan ID and user email
		if ( empty($data['plan_id']) || empty($data['email']) ) {
			return array(
				'success' => false,
				'message' => __( 'Email or membership plan is missing', 'wampum' ),
			);
		}

		// TODO: Check and set all variables here. Sanitize too?

		$email = sanitize_email($data['email']);

		// If user is logged in
		if ( is_user_logged_in() ) {
			/**
			 * Return error if they are trying to register another email
			 * Email field should be readonly, but a user may try to change this via dev tools
			 */
			$current_user = wp_get_current_user();
			if ( $email != $current_user->user_email ) {
				return array(
					'success' => false,
					'message' => __( 'You must use your current user email', 'wampum' ),
				);
			}

			$user_id = get_current_user_id();

		}
		// Not logged in
		else {

			// Validate submission, if logged in they would have been validated in user_available()
			$valid = $this->valid_submission( 'membership', $data );

			// Return with message if not a valid submission
			if ( true !== $valid ) {
				return array(
					'success' => false,
					'message' => $valid,
				);
			}

			$email_exists = email_exists( $email );
			// Username is not required, so check it first
			$username_exists = isset($data['username']) ? username_exists( $data['username'] ) : false;

			/**
			 * If the email or username is already a registered user
			 * Again, this shouldn't happen because field should be read only after the first form verified no user
			 */
			if ( $email_exists || $username_exists ) {
				// Set in wp_localize_script() because calling here returns WP-API endpoing URL
				$current_url = $data['current_url'];
				// return error with link to login
				return array(
					'success' => false,
					'message' => __( 'This user account already exists.', 'wampum' ) . ' <a class="login-link" href="' . wp_login_url( esc_url($current_url) ) . '" title="Log in">Log in?</a>',
				);
			}

			/**
			 * Register a user
			 * and maybe log them in.
			 */
			// $data['log_in'] = true;
			$register = $this->register( $data );

			// Bail if unsuccessful
			if ( false == $register['success'] ) {
				return $register;
			}

			$user_id = $register['user_id'];

		}

		$plan_id = absint($data['plan_id']);
		$user_id = absint($user_id);

		// If user is not an existing member of the plan
		if ( ! wc_memberships_is_user_member( $user_id, $plan_id ) ) {

			// Add the user to the membership
			$membership_args = array(
				'plan_id' => $plan_id,
				'user_id' => $user_id,
			);
			wc_memberships_create_user_membership( $membership_args );

			// Get the new membership
			$user_membership = wc_memberships_get_user_membership( $user_id, $membership_args['plan_id'] );
			// Get the note
			$note = 'Membership added via Wampum form at ' . esc_url($data['current_url']);
			// Add a note so we know how this was registered.
			$user_membership->add_note( sanitize_text_field($note) );
		}

		// Build notification message
		$message = get_bloginfo('name') . ' - ' . get_the_title($plan_id) . ' membership added via Wampum form at ' . esc_url($data['current_url']);
		if ( $data['first_name'] ) {
			$message .= ' - ' . $data['first_name'];
		}
		if ( $data['last_name'] ) {
			$message .= ' ' . $data['last_name'];
		}
		$message .= ' - ' . $email;

		// Notifications
		$this->maybe_do_notifications( 'Membership', $data, $message );

		// ActiveCampaign
		$this->maybe_do_active_campaign( $data );

		// Hook to run custom code after successful submission (membership forms only)
		do_action( 'wampum_form_after_membership_submission', $data );

		// Hook to run custom code after successful submission (all forms)
		do_action( 'wampum_form_after_submission', $data );

		// Success!
		return array(
			'success' => true,
			'user'    => $user_id, // false|user_id If user was created in the process
		);

	}

	/**
	 * Honeypot validation
	 * This field should be empty
	 * If it has a value, that means a bot probably tried to submit the form
	 *
	 * @since   1.0.0
	 *
	 * @return  array  The response
	 */
	function validate_say_what( $data ) {
		if ( ! empty($data['say_what']) ) {
			return array(
				'success' => false,
				'message' => __( 'Spam detected', 'wampum' ),
			);
		}
		return array(
			'success' => true,
		);
	}

	/**
	 * Allow custom validation.
	 * e.g. Return success false if not a valid submission based on custom data processing.
	 *
	 * @since   1.1.0
	 *
	 * @param   string  $form_type  The form type to validate
	 * @param   array   $data  		Form data submitted
	 *
	 * @return  array   The validation return data
	 */
	function valid_submission( $form_type, $data ) {
		switch ( $form_type ) {
			case 'login':
				$filter = 'wampum_forms_is_valid_login_submission';
			break;
			case 'password':
				$filter = 'wampum_forms_is_valid_password_submission';
			break;
			case 'register':
				$filter = 'wampum_forms_is_valid_register_submission';
			break;
			case 'subscribe':
				$filter = 'wampum_forms_is_valid_subscribe_submission';
			break;
			case 'membership':
				$filter = 'wampum_forms_is_valid_membership_submission';
			break;
			default:
				$filter = '';
			break;
		}

		// If no filter, nothing to validate. Return successsful.
		if ( empty( $filter ) ) {
			return true;
		}

		/**
		 * All filters via these methods use the following:
		 *
		 * @param    bool|string  $return  false or the error message
		 * @param    array  	  $data    Form data submitted
		 *
		 * @return   bool|string  $return  false or the error message
		 */
		return apply_filters( $filter, true, $data );
	}

	/**
	 * Maybe send email notificaitons of form submissions.
	 *
	 * @since   1.1.0
	 *
	 * @param   array   $data     Array of data to check user
	 * @param   string  $message  The email body
	 *
	 * @return  void
	 */
	function maybe_do_notifications( $type, $data, $message = '' ) {

		// Bail if no notifications
		if ( ! isset($data['notifications']) || empty($data['notifications']) ) {
			return;
		}

		// Make an array and trim spaces around each email
		$notifications = array_map( 'trim', ( explode( ',', $data['notifications'] ) ) );

		// Sanitize each email
		$notifications = array_map( 'sanitize_email', $notifications );

		$to      = $notifications;
		$subject = sprintf( '%s - New Wampum %s form submission', get_bloginfo('name'), $type );
		$body    = $message;

		if ( empty($body) ) {
			// Build the body
			$body = sprintf( '%s form details\r\n', ucwords($data['type']) );
			if ( ! empty( $data['first_name'] ) ) {
				$body .= sprintf( 'First Name: %s\r\n', $data['first_name'] );
			}
			if ( ! empty( $data['last_name'] ) ) {
				$body .= sprintf( 'Last Name: %s\r\n', $data['first_name'] );
			}
			if ( ! empty( $data['email'] ) ) {
				$body .= sprintf( 'Email: %s\r\n', $data['first_name'] );
			}
		}

		// Send it
		wp_mail( $to, $subject, $body );

	}

	/**
	 * Maybe send data to ActiveCampaign.
	 * This is also used in the Event Organiser integration.
	 *
	 * @since   1.1.0
	 *
	 * @param   array  $data   Array of data to check user
	 *
	 * @return  array  Associative array of success and maybe message
	 */
	function maybe_do_active_campaign( $data ) {

		// Bail if no email
		if ( ! $data['email'] ) {
			return;
		}

		// list ID or Tag
		if ( ! ( $data['ac_list_ids'] || $data['ac_tags'] ) ) {
			return;
		}

		// Setup AC
		$ac = $this->get_active_campaign_object();

		// If valid AC object
		if ( $ac ) {

			// Start the contact array, email is required in our form
			$contact = wampum_forms_setup_ac_contact( $data );

			// Do the thang
			$contact_sync = $ac->api( 'contact/sync', $contact );

			if ( $contact_sync->success ) {
				return array(
					'success' => true,
				);
			}

		}
		return array(
			'success' => false,
			'message' => __( 'Uh oh! Looks like there was an error.', 'wampum' ),
		);

	}

	/**
	 * Check if active campaign credentials are saved and valid.
	 * Uses base_url and key from Wampum Forms settings page.
	 *
	 * @since  1.1.0
	 *
	 * @return bool
	 */
	function is_active_campaign_connected() {

		$ac = $this->get_active_campaign_object();

		// If connection is valid
		if ( is_object($ac) ) {
			return true;
		}

		// No good
		return false;

	}

	/**
	 * Get the Active Campaign php object.
	 *
	 * @since   1.1.0
	 *
	 * @return  object|bool(false)
	 */
	function get_active_campaign_object() {
		// Settings
		$ac = get_option( 'wampum_forms_ac' );
		// If not what we need
		if ( empty($ac) || ! isset($ac['base_url']) || ! isset($ac['key']) ) {
			return false;
		}
		// Get AC object
		$ac = new ActiveCampaign( esc_url($ac['base_url']), sanitize_text_field($ac['key']) );
		// Test creds
		if ( is_object($ac) && $ac->credentials_test() ) {
			// If A-Okay bring it home
			return $ac;
		}
		// No good
		return false;

	}

	/**
	 * Test if connection to active campaign works.
	 *
	 * @param  object  $ac  the ac object via $ac = new ActiveCampaign( esc_url($ac_base_url), sanitize_text_field($ac_key) )
	 *
	 * @return bool
	 */
	function active_campaign_credentials_test( $ac ) {
		return $ac->credentials_test();
	}

	/**
	 * TODO!!!!!
	 *
	 * Maybe send data to ActiveCampaign
	 *
	 * @since  1.1.0
	 */
	function maybe_do_sharpspring( $data ) {

		// Bail if no email
		if ( ! $data['email'] ) {
			return;
		}

	}

}
