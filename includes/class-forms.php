<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Wampum_Forms Class.
 *
 * @since 1.1.0
 */
final class Wampum_Forms {

	/**
	 * Singleton
	 * @var   Wampum_Forms The one true Wampum_Forms
	 * @since 1.1.0
	 */
	private static $instance;

	// Set form counter
	private $form_counter = 0;

	// Whether to load password script or not
	private $password_meter = false;

	/**
	 * Main Wampum_Forms Instance.
	 *
	 * Insures that only one instance of Wampum_Forms exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   1.1.0
	 * @static  var array $instance
	 * @uses    Wampum_Forms->setup() Setup the hooks/filters
	 * @return  object | Wampum_Forms The one true Wampum_Forms
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new Wampum_Forms;
			// Methods
			self::$instance->setup();
		}
		return self::$instance;
	}

	/**
	 * CLass setup actions/filters/hooks
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	function setup() {

		// Enqueue styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Shortcodes
		add_shortcode( 'wampum_form',            array( $this, 'get_form' ) );
		add_shortcode( 'wampum_login_form',      array( $this, 'login_form_callback' ) );
		add_shortcode( 'wampum_register_form',   array( $this, 'register_form_callback' ) );
		add_shortcode( 'wampum_password_form',   array( $this, 'password_form_callback' ) );
		add_shortcode( 'wampum_subscribe_form',  array( $this, 'subscribe_form_callback' ) );
		add_shortcode( 'wampum_membership_form', array( $this, 'membership_form_callback' ) );

	}

	/**
	 * Register scripts for later use
	 *
	 * Use via wp_enqueue_script('wampum-login'); in a template
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		if ( ( $this->form_counter > 0 ) ) {
			// CSS
			wp_enqueue_style( 'wampum-forms', WAMPUM_FORMS_PLUGIN_URL . 'css/wampum-forms.min.css', array(), WAMPUM_FORMS_VERSION );
			// JS
			if ( $this->password_meter ) {
				wp_enqueue_script( 'wampum-zxcvbn', WAMPUM_FORMS_PLUGIN_URL . 'js/zxcvbn.js', array('jquery'), '4.4.2', true );
			}
			// All Forms
			wp_enqueue_script( 'wampum-forms', WAMPUM_FORMS_PLUGIN_URL . 'js/wampum-forms.js', array('jquery'), WAMPUM_FORMS_VERSION, true );
			wp_localize_script( 'wampum-forms', 'wampumFormVars', array(
				'root'        => esc_url_raw( rest_url() ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'failure'     => __( 'Something went wrong, please try again.', 'wampum' ),
				'current_url' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // For login URL if email/username exists and SharpSpring
				'login'       => array(
					'empty' => __( 'Username and password fields are empty', 'wampum' ),
				),
				'password' => array(
					'mismatch' => __( 'Passwords do not match', 'wampum' ),
				),
			) );
		}
	}

	/**
	 * Get a login form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function login_form_callback( $args ) {
		// Bail if already logged in
		if ( is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'login';
		return $this->get_form( $args );
	}

	/**
	 * Get a registration form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function register_form_callback( $args ) {
		// Bail if already logged in
		if ( is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'register';
		return $this->get_form( $args );
	}

	/**
	 * Get a subscribe form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @return string  the form HTML
	 */
	function subscribe_form_callback( $args ) {
		$args['type'] = 'subscribe';
		return $this->get_form( $args );
	}

	/**
	 * Get a password form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function password_form_callback( $args ) {
		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'password';
		return $this->get_form( $args );
	}

	/**
	 * Get a membership form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function membership_form_callback( $args ) {
		// Bail if WooCommerce Memberships is not active.
		if ( ! function_exists( 'wc_memberships' ) ) {
			return;
		}
		$args['type'] = 'membership';
		return $this->get_form( $args );
	}

	/**
	 * Get a form, by type.
	 *
	 * @since  1.1.0
	 *
	 * @param  array  $args  {
	 *
	 *      Associative array of args to build form.
	 *
	 *      @type  string   $type                       (required) The type of form to return
	 *      @type  bool     $hidden                     Whether to hide the form by default (display:none; inline style)
	 *      @type  bool     $inline                     Display the form fields in a row
	 *      @type  string   $title                      The form title to display
	 *      @type  string   $title_wrap                 The title wrap element
	 *      @type  string   $desc                       The form description to display
	 *      @type  bool     $first_name                 Whether to show first name field
	 *      @type  bool     $last_name                  Whether to show last name field
	 *      @type  bool     $email                      Whether to show email field
	 *      @type  bool     $username                   Whether to show username field
	 *      @type  bool     $password                   Whether to show password field
	 *      @type  bool     $password_confirm           Whether to show password confirm field
	 *      @type  bool     $password_strength          Whether to show password strength meter
	 *      @type  string   $first_name_label           The label of the first name field
	 *      @type  string   $last_name_label            The label of the last name field
	 *      @type  string   $email_label                The label of the email field
	 *      @type  string   $username_label             The label of the username field
	 *      @type  string   $password_label             The label of the password field
	 *      @type  string   $password_confirm_label     The label of the password confirm field
	 *      @type  string   $password_strength_label    The label of the password strength meter
	 *      @type  string   $button                     The button text to display
	 *      @type  string   $notifications              Comma-separated list of emails to notify upons successful submission
	 *      @type  string   $redirect                   URL to redirect after form submission
	 *      @type  integer  $ac_list_ids                Comma-separated list of ActiveCampaign list IDs to add a contact to
	 *      @type  integer  $ac_tags                    Comma-separated list of ActiveCampaign tag IDs to add a contact to
	 *
	 *      // Login-specific form params
	 *
	 *      @type  bool     $remember                   Whether to remember the values and stay logged in
	 *      @type  string   $username_label             The label of the username field
	 *      @type  bool     $value_remember             Whether to start the 'remember' checkbox as checked
	 *
	 *      // Register-specific form params
	 *
	 *      @type  bool     $log_in                     Whether to log user in after registration
	 *
	 *      // Membership-specific form params
	 *
	 *      @type  integer  $plan_id                    (required) The WooCommerce Memberships ID
	 *      @type  string   $member_message             Message to display in place of the form if a logged in user is already a member
	 *
	 * }
	 *
	 * @return  bool|WP_Error  Whether a new user was created during the process
	 */
	function get_form( $args ) {

		/**
		 * Set all the default args.
		 * Some args are specific to a form type.
		 * Some args will be forced depending on form type.
		 */
		$args = shortcode_atts( array(
			'type'                    => '',
			'hidden'                  => false,
			'inline'                  => false,
			'title'                   => '',
			'title_wrap'              => 'h3',
			'desc'                    => '',
			'first_name'              => false,
			'last_name'               => false,
			'email'                   => false,
			'username'                => false,
			'password'                => false,
			'password_confirm'        => false,
			'password_strength'       => false,
			'first_name_label'        => '',
			'last_name_label'         => '',
			'email_label'             => '',
			'username_label'          => '',
			'password_label'          => '',
			'password_confirm_label'  => '',
			'password_strength_label' => '',
			'button'                  => __( 'Submit', 'wampum' ),
			'notifications'           => '',
			'redirect'                => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
			'ac_list_ids'             => '',
			'ac_tags'                 => '',
			'remember'                => true,
			'label_remember'          => '',
			'value_remember'          => true,
			'log_in'                  => false,
			'plan_id'                 => '',
			'member_message'          => '',
		), $args, 'wampum_form' );

		// Sanitize the args
		$args = array(
			'type'                    => sanitize_text_field( $args['type'] ),
			'hidden'                  => filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN ),
			'inline'                  => filter_var( $args['inline'], FILTER_VALIDATE_BOOLEAN ),
			'title'                   => sanitize_text_field( $args['title'] ),
			'title_wrap'              => sanitize_text_field( $args['title_wrap'] ),
			'desc'                    => sanitize_text_field( $args['desc'] ),
			'first_name'              => filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ),
			'last_name'               => filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ),
			'email'                   => filter_var( $args['email'], FILTER_VALIDATE_BOOLEAN ),
			'username'                => filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ),
			'password'                => filter_var( $args['password'], FILTER_VALIDATE_BOOLEAN ),
			'password_confirm'        => filter_var( $args['password_confirm'], FILTER_VALIDATE_BOOLEAN ),
			'password_strength'       => filter_var( $args['password_strength'], FILTER_VALIDATE_BOOLEAN ),
			'first_name_label'        => sanitize_text_field( $args['first_name_label'] ),
			'last_name_label'         => sanitize_text_field( $args['last_name_label'] ),
			'email_label'             => sanitize_text_field( $args['email_label'] ),
			'username_label'          => sanitize_text_field( $args['username_label'] ),
			'password_label'          => sanitize_text_field( $args['password_label'] ),
			'password_confirm_label'  => sanitize_text_field( $args['password_confirm_label'] ),
			'password_strength_label' => sanitize_text_field( $args['password_strength_label'] ),
			'button'                  => sanitize_text_field( $args['button'] ),
			'notifications'           => sanitize_text_field( $args['notifications'] ),
			'redirect'                => sanitize_text_field( $args['redirect'] ), // Can't esc_url() cause we may allow strings to check against,
			'ac_list_ids'             => sanitize_text_field( $args['ac_list_ids'] ),
			'ac_tags'                 => sanitize_text_field( $args['ac_tags'] ),
			'remember'                => filter_var( $args['remember'], FILTER_VALIDATE_BOOLEAN ),
			'label_remember'          => sanitize_text_field( $args['label_remember'] ),
			'value_remember'          => filter_var( $args['value_remember'], FILTER_VALIDATE_BOOLEAN ),
			'log_in'                  => filter_var( $args['log_in'], FILTER_VALIDATE_BOOLEAN ),
			'plan_id'                 => intval( $args['plan_id'] ),
			'member_message'          => sanitize_text_field( $args['member_message'] ),
		);

		$type = $args['type'];

		// Available form types
		$types = array( 'login', 'password', 'register', 'subscribe', 'membership' );

		// Bail if we don't have a valid form type
		if ( ! in_array( $type, $types ) ) {
			return;
		}

		// Get the form by type
		switch ( $type ) {
			case 'login':
				$form = $this->get_login_form( $args );
			break;
			case 'password':
				$form = $this->get_password_form( $args );
			break;
			case 'register':
				$form = $this->get_register_form( $args );
			break;
			case 'subscribe':
				$form = $this->get_subscribe_form( $args );
			break;
			case 'membership':
				$form = $this->get_membership_form( $args );
			break;
			default:
				$form = '';
			break;
		}

		// Bail if no form
		if ( empty($form) ) {
			return;
		}

		// Enqueue Scripts
		$this->enqueue_scripts();

		return $form;

	}

	/**
	 * Get a login form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function get_login_form( $args ) {

		// Get the form
		$form = new Wampum_Form();

		// Settings
		$form->set( 'hidden', $args['hidden'] );
		$form->set( 'inline', $args['inline'] );

		// Open
		$form->open( array(
			'data-form' => 'login',
		), $args );

		// Honeypot
		$form->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// Username
		$form->add_field( 'text', array(
			'name'     => 'username',
			'class'    => 'username',
			'required' => true,
		), array(
			'label' => ! empty( $args['username_label'] ) ? $args['username_label'] : __( 'Email/Username', 'wampum' ),
		) );

		// Password
		$form->add_field( 'password', array(
			'name'     => 'password',
			'class'    => 'password',
			'required' => true,
		), array(
			'label' => ! empty( $args['password_label'] ) ? $args['password_label'] : __( 'Password', 'wampum' ),
		) );

		// Remember
		if ( $args['remember'] ) {

			$form->add_field( 'checkbox', array(
				'name'    => 'rememberme',
				'class'   => 'remember',
				'checked' => $args['value_remember'],
				'value'   => 'forever',
			), array(
				'label' => __( 'Remember Me', 'wampum' ),
			) );

		}

		// Notifications
		if ( ! empty( $args['notifications'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'notifications',
				'value' => $args['notifications'],
			));

		}

		// Redirect
		$form->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => $args['redirect'],
		));

		// Submit
		$form->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => $args['button'],
		) );

		// Close
		$form->close();

		// Increment the counter
		$this->form_counter++;

		return sprintf( '<div class="wampum-form">%s</div>', $form->render( $args, false ) );

	}

	/**
	 * Get a password form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function get_password_form( $args ) {

		// Get the current page url
		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Get the form
		$form = new Wampum_Form();

		// Settings
		$form->set( 'hidden', $args['hidden'] );
		$form->set( 'inline', $args['inline'] );

		// Open
		$form->open( array(
			'data-form' => 'password',
		), $args );

		// Honeypot
		$form->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// Password
		$form->add_field( 'password', array(
			'name'     => 'password',
			'class'    => 'password',
			'required' => true,
		), array(
			'label' => ! empty( $args['password_label'] ) ? $args['password_label'] : __( 'Password', 'wampum' ),
		) );

		// Password confirm
		$form->add_field( 'password', array(
			'name'     => 'password_confirm',
			'class'    => 'password-confirm',
			'required' => true,
		), array(
			'label' => ! empty( $args['password_confirm_label'] ) ? $args['password_confirm_label'] : __( 'Confirm Password', 'wampum' ),
		) );

		// Load password strength script
		$this->password_meter = true;

		// Password strength
		$form->add_field( 'password_strength', array(
			'name'  => 'password_strength',
			'class' => 'password-strength',
			'style' => 'display:none;',
		), array(
			'label' => ! empty( $args['password_strength_label'] ) ? $args['password_strength_label'] : __( 'Strength', 'wampum' ),
		) );

		// Notifications
		if ( ! empty( $args['notifications'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'notifications',
				'value' => $args['notifications'],
			));

		}

		// Redirect
		$form->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => $args['redirect'],
		));

		// Submit
		$form->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => $args['button'],
		) );

		// Close
		$form->close();

		// Increment the counter
		$this->form_counter++;

		return sprintf( '<div class="wampum-form">%s</div>', $form->render( $args, false ) );

	}

	/**
	 * Get a register form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function get_register_form( $args ) {

		// Get the form
		$form = new Wampum_Form();

		// Settings
		$form->set( 'hidden', $args['hidden'] );
		$form->set( 'inline', $args['inline'] );

		// Open
		$form->open( array(
			'data-form' => 'register',
		), $args );

		// Honeypot
		$form->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// First Name
		if ( $args['first_name'] ) {

			// Default label
			$first_name_label = __( 'Name', 'wampum' );
			// If we have a custom label
			if ( $args['first_name_label'] ) {
				$first_name_label = $args['first_name_label'];
			}
			// Else if we are showing the last name
			elseif ( $args['last_name'] ) {
				$first_name_label = __( 'First Name', 'wampum' );
			}

			$form->add_field( 'text', array(
				'name'  => 'first_name',
				'class' => 'first-name',
				'value' => $first_name,
			), array(
				'label' => $first_name_label,
			) );

		}

		// Last Name
		if ( $args['last_name'] ) {

			$form->add_field( 'text', array(
				'name'  => 'last_name',
				'class' => 'last-name',
				'value' => $last_name,
			), array(
				'label' => ! empty( $args['last_name_label'] ) ? $args['last_name_label'] : __( 'Last Name', 'wampum' ),
			) );

		}

		// Email
		$form->add_field( 'email', array(
			'name'     => 'email',
			'class'    => 'email',
			'required' => true,
		), array(
			'label' => ! empty( $args['email_label'] ) ? $args['email_label'] : __( 'Email', 'wampum' ),
		) );

		// Username
		if ( $args['username'] ) {

			// Username
			$form->add_field( 'text', array(
				'name'     => 'username',
				'class'    => 'username',
				'required' => true,
			), array(
				'label' => ! empty( $args['username_label'] ) ? $args['username_label'] : __( 'Username', 'wampum' ),
			) );

		}

		// If password
		if ( $args['password'] ) {

			// Password
			$form->add_field( 'password', array(
				'name'     => 'password',
				'class'    => 'password',
				'required' => true,
			), array(
				'label' => ! empty( $args['password_label'] ) ? $args['password_label'] : __( 'Password', 'wampum' ),
			) );

			// Password confirm
			if ( $args['password_confirm'] ) {

				// Password confirm
				$form->add_field( 'password', array(
					'name'     => 'password_confirm',
					'class'    => 'password-confirm',
					'required' => true,
				), array(
					'label' => ! empty( $args['password_confirm_label'] ) ? $args['password_confirm_label'] : __( 'Password Confirm', 'wampum' ),
				) );

			}

			// If password strength not set, use it.
			if ( empty( $args['password_strength'] ) ) {
				$args['password_strength'] = true;
			}

			// Password strength
			if ( $args['password_strength'] ) {

				// Load password strength script
				$this->password_meter = true;

				// Password strength
				$form->add_field( 'password_strength', array(
					'name'  => 'password_strength',
					'class' => 'password-strength',
					'style' => 'display:none;',
				), array(
					'label' => ! empty( $args['password_strength_label'] ) ? $args['password_strength_label'] : __( 'Strength', 'wampum' ),
				) );

			}

		}

		// Active Campaign List IDs
		if ( ! empty( $args['ac_list_ids'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'ac_list_ids',
				'value' => $args['ac_list_ids'],
			));

		}

		// Active Campaign Tags
		if ( ! empty( $args['ac_tags'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'ac_tags',
				'value' => $args['ac_tags'],
			));

		}

		// Notifications
		if ( ! empty( $args['notifications'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'notifications',
				'value' => $args['notifications'],
			));

		}

		// Log In
		$form->add_field( 'hidden', array(
			'name'  => 'log_in',
			'value' => intval($args['log_in']), // use integers cause boolean won't show value via wampum_attr
		));

		// Redirect
		$form->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => $args['redirect'],
		));

		// Submit
		$form->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => $args['button'],
		) );

		// Close
		$form->close();

		// Increment the counter
		$this->form_counter++;

		return sprintf( '<div class="wampum-form">%s</div>', $form->render( $args, false ) );

	}

	/**
	 * Get a form strictly for subscribing a user to ActiveCampaign.
	 *
	 * @since   1.1.0
	 *
	 * @param   array   $args  array of form args
	 *
	 * @return  string  The form HTML
	 */
	function get_subscribe_form( $args ) {

		// Default vars
		$logged_in  = false;
		$first_name = $last_name = $email = '';

		// Logged in vars
		if ( is_user_logged_in() ) {
			$logged_in    = true;
			$current_user = wp_get_current_user();
			$first_name   = $current_user->first_name;
			$last_name    = $current_user->last_name;
			$email        = $current_user->user_email;
		}

		// Get the form
		$form = new Wampum_Form();

		// Settings
		$form->set( 'hidden', $args['hidden'] );
		$form->set( 'inline', $args['inline'] );

		// Open
		$form->open( array(
			'data-form' => 'subscribe',
		), $args );

		// Honeypot
		$form->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// First Name
		if ( $args['first_name'] ) {

			// Default label
			$first_name_label = __( 'Name', 'wampum' );
			// If we have a custom label
			if ( $args['first_name_label'] ) {
				$first_name_label = $args['first_name_label'];
			}
			// Else if we are showing the last name
			elseif ( $args['last_name'] ) {
				$first_name_label = __( 'First Name', 'wampum' );
			}

			$form->add_field( 'text', array(
				'name'  => 'first_name',
				'class' => 'first-name',
				'value' => $first_name,
			), array(
				'label' => $first_name_label,
			) );

		}

		// Last Name
		if ( $args['last_name'] ) {

			$form->add_field( 'text', array(
				'name'  => 'last_name',
				'class' => 'last-name',
				'value' => $last_name,
			), array(
				'label' => ! empty( $args['last_name_label'] ) ? $args['last_name_label'] : __( 'Last Name', 'wampum' ),
			) );

		}

		// Email
		$form->add_field( 'email', array(
			'name'     => 'email',
			'class'    => 'email',
			'required' => true,
			'value'    => $email,
		), array(
			'label' => ! empty( $args['email_label'] ) ? $args['email_label'] : __( 'Email', 'wampum' ),
		) );

		// Active Campaign List IDs
		if ( ! empty( $args['ac_list_ids'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'ac_list_ids',
				'value' => $args['ac_list_ids'],
			));

		}

		// Active Campaign Tags
		if ( ! empty( $args['ac_tags'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'ac_tags',
				'value' => $args['ac_tags'],
			));

		}

		// Notifications
		if ( ! empty( $args['notifications'] ) ) {

			$form->add_field( 'hidden', array(
				'name'  => 'notifications',
				'value' => $args['notifications'],
			));

		}

		// Redirect
		$form->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => $args['redirect'] ? $args['redirect'] : '',
		));

		// Submit
		$form->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => $args['button'],
		) );

		// Close
		$form->close();

		// Increment the counter
		$this->form_counter++;

		return sprintf( '<div class="wampum-form">%s</div>', $form->render( $args, false ) );

	}

	/**
	 * Get a membership form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @param  $args   the form args
	 *
	 * @return string  the form HTML
	 */
	function get_membership_form( $args ) {

		// Bail if no plan ID
		if ( empty( $args['plan_id'] ) ) {
			return;
		}

		// Bail if logged in user is already a member
		if ( is_user_logged_in() && wc_memberships_is_user_member( get_current_user_id(), absint($args['plan_id']) ) ) {
			// Show message if we have one
			if ( ! empty( $args['member_message'] ) ) {
				return sprintf( '<div class="wampum-form"><p class="member-message">%s</p></div>', sanitize_text_field($args['member_message']) );
			}
			return;
		}

		$html = '';

		/**
		 * User Available form
		 */
		if ( ! is_user_logged_in() ) {

			// Get the user available form
			$user_available = new Wampum_Form();

			// Settings
			$user_available->set( 'hidden', false );
			$user_available->set( 'inline', $args['inline'] );

			// Open
			$user_available->open( array(
				'data-form' => 'user-available',
			), $args );

			// Honeypot
			$user_available->add_field( 'url', array(
				'name'  => 'say_what',
				'class' => 'say-what',
			), array(
				'label' => 'URL',
			));

			// First Name
			if ( $args['first_name'] ) {

				// Default label
				$first_name_label = __( 'Name', 'wampum' );
				// If we have a custom label
				if ( $args['first_name_label'] ) {
					$first_name_label = $args['first_name_label'];
				}
				// Else if we are showing the last name
				elseif ( $args['last_name'] ) {
					$first_name_label = __( 'First Name', 'wampum' );
				}

				$user_available->add_field( 'text', array(
					'name'  => 'first_name',
					'class' => 'first-name',
				), array(
					'label' => $first_name_label,
				) );

			}

			// Last Name
			if ( $args['last_name'] ) {

				$user_available->add_field( 'text', array(
					'name'  => 'last_name',
					'class' => 'last-name',
				), array(
					'label' => ! empty( $args['last_name_label'] ) ? $args['last_name_label'] : __( 'Last Name', 'wampum' ),
				) );

			}

			// Email
			$user_available->add_field( 'email', array(
				'name'     => 'email',
				'class'    => 'email',
				'required' => true,
			), array(
				'label' => ! empty( $args['email_label'] ) ? $args['email_label'] : __( 'Email', 'wampum' ),
			) );

			// Username
			if ( $args['username'] ) {

				// Username
				$user_available->add_field( 'text', array(
					'name'     => 'username',
					'class'    => 'username',
					'required' => true,
				), array(
					'label' => ! empty( $args['username_label'] ) ? $args['username_label'] : __( 'Username', 'wampum' ),
				) );

			}

			// Submit
			$user_available->add_field( 'submit', array(
				'name'  => 'submit',
				'class' => 'submit',
			), array(
				'label' => $args['button'],
			) );

			// Close
			$user_available->close();

			// Increment the counter
			$this->form_counter++;

			// Add this form to the HTML to return
			$html .= $user_available->render( $args, false );

		} // not logged in

		/**
		 * Join Membership
		 */
		$join = new Wampum_Form();

		// Default vars
		$logged_in  = false;
		$first_name = $last_name = $email = '';

		// Logged in vars
		if ( is_user_logged_in() ) {
			$logged_in 		= true;
			$current_user	= wp_get_current_user();
			$first_name		= $current_user->first_name;
			$last_name		= $current_user->last_name;
			$email			= $current_user->user_email;
		}

		// Settings
		if ( ! is_user_logged_in() ) {
			$join->set( 'hidden', true );
		} else {
			$join->set( 'hidden', false );
		}
		$join->set( 'inline', $args['inline'] );

		// Open
		$join->open( array(
			'data-form' => 'join-membership',
		), $args );

		// Honeypot
		$join->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// First Name
		if ( $args['first_name'] ) {

			// Default label
			$first_name_label = __( 'Name', 'wampum' );
			// If we have a custom label
			if ( $args['first_name_label'] ) {
				$first_name_label = $args['first_name_label'];
			}
			$join->add_field( 'text', array(
				'name'  => 'first_name',
				'class' => 'first-name',
				'value' => $first_name,
			), array(
				'label' => $first_name_label,
			) );

		}

		// Last Name
		if ( $args['last_name'] ) {

			$join->add_field( 'text', array(
				'name'  => 'last_name',
				'class' => 'last-name',
				'value' => $last_name,
			), array(
				'label' => ! empty( $args['last_name_label'] ) ? $args['last_name_label'] : __( 'Last Name', 'wampum' ),
			) );

		}

		// Email
		$join->add_field( 'email', array(
			'name'     => 'email',
			'class'    => 'email',
			'required' => true,
			'value'    => $email,
			'readonly' => $logged_in ? true : false,
		), array(
			'label' => ! empty( $args['email_label'] ) ? $args['email_label'] : __( 'Email', 'wampum' ),
		) );

		// If not logged in
		if ( ! is_user_logged_in() ) {

			// Username
			if ( $args['username'] ) {

				// Username
				$join->add_field( 'text', array(
					'name'     => 'username',
					'class'    => 'username',
					'required' => true,
				), array(
					'label' => ! empty( $args['username_label'] ) ? $args['username_label'] : __( 'Username', 'wampum' ),
				) );

			}

			// Password
			$join->add_field( 'password', array(
				'name'     => 'password',
				'class'    => 'password',
				'required' => true,
			), array(
				'label' => ! empty( $args['password_label'] ) ? $args['password_label'] : __( 'Password', 'wampum' ),
			) );

			if ( $args['password_strength'] ) {

				// Load password strength script
				$this->password_meter = true;

				// Password strength
				$join->add_field( 'password_strength', array(
					'name'  => 'password_strength',
					'class' => 'password-strength',
					'style' => 'display:none;',
				), array(
					'label' => ! empty( $args['password_strength_label'] ) ? $args['password_strength_label'] : __( 'Strength', 'wampum' ),
				) );

			}

		}

		// Plan ID
		$join->add_field( 'hidden', array(
			'name'  => 'plan_id',
			'value' => $args['plan_id'],
		));

		// Log In
		$join->add_field( 'hidden', array(
			'name'  => 'log_in',
			'value' => intval($args['log_in']), // use integers cause boolean won't show value via wampum_attr
		));

		// Active Campaign List IDs
		if ( ! empty( $args['ac_list_ids'] ) ) {

			$join->add_field( 'hidden', array(
				'name'  => 'ac_list_ids',
				'value' => $args['ac_list_ids'],
			));

		}

		// Active Campaign Tags
		if ( ! empty( $args['ac_tags'] ) ) {

			$join->add_field( 'hidden', array(
				'name'  => 'ac_tags',
				'value' => $args['ac_tags'],
			));

		}

		// Notifications
		if ( ! empty( $args['notifications'] ) ) {

			$join->add_field( 'hidden', array(
				'name'  => 'notifications',
				'value' => $args['notifications'],
			));

		}

		// Redirect
		$join->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => $args['redirect'],
		));

		// Submit
		$join->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => $args['button'],
		) );

		// Close
		$join->close();

		// Increment the counter
		$this->form_counter++;

		// Add this form to the HTML to return
		$html .= $join->render( $args, false );

		/**
		 * Login Form
		 */
		// Get the form
		$login = new Wampum_Form();

		$args['title'] = __( 'Log In', 'wampum' );

		// Settings
		$login->set( 'hidden', true );
		$login->set( 'inline', $args['inline'] );

		// Open
		$login->open( array(
			'data-form' => 'login',
		), $args );

		// Honeypot
		$login->add_field( 'url', array(
			'name'  => 'say_what',
			'class' => 'say-what',
		), array(
			'label' => 'URL',
		));

		// Username
		$login->add_field( 'text', array(
			'name'     => 'username',
			'class'    => 'username',
			'required' => true,
		), array(
			'label' => ! empty( $args['username_label'] ) ? $args['username_label'] : __( 'Email/Username', 'wampum' ),
		) );

		// Password
		$login->add_field( 'password', array(
			'name'     => 'password',
			'class'    => 'password',
			'required' => true,
		), array(
			'label' => ! empty( $args['password_label'] ) ? $args['password_label'] : __( 'Password', 'wampum' ),
		) );

		// Remember
		if ( $args['remember'] ) {

			$login->add_field( 'checkbox', array(
				'name'    => 'rememberme',
				'class'   => 'remember',
				'checked' => $args['value_remember'],
				'value'   => 'forever',
			), array(
				'label' => ! empty( $args['label_remember'] ) ? $args['label_remember'] : __( 'Remember Me', 'wampum' ),
			) );

		}

		// Redirect
		$login->add_field( 'hidden', array(
			'name'  => 'redirect',
			'value' => 'membership_form', // Part of membership form flow so JS reload same page
		));

		// Submit
		$login->add_field( 'submit', array(
			'name'  => 'submit',
			'class' => 'submit',
		), array(
			'label' => __( 'Log In', 'wampum' ),
		) );

		// Close
		$login->close();

		// Increment the counter
		$this->form_counter++;

		// Add this form to the HTML to return
		$html .= $login->render( $args, false );

		// Bring it all home baby
		return sprintf( '<div class="wampum-form">%s</div>', $html );

	}

}
