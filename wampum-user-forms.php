<?php
/**
 * @package   Wampum_User_Forms
 * @author    BizBudding, INC <mike@bizbudding.com>
 * @license   GPL-2.0+
 * @link      http://bizbudding.com.com
 * @copyright 2016 BizBudding, INC
 *
 * @wordpress-plugin
 * Plugin Name:        Wampum - User Forms
 * Description: 	   Create login, password, and free membership (w/ user registration) forms that use the WP-API form processing
 * Plugin URI:         https://github.com/JiveDig/wampum-user-forms
 * Author:             Mike Hemberger
 * Author URI:         https://bizbudding.com
 * Text Domain:        wampum
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Version:            1.0.0
 * GitHub Plugin URI:  https://github.com/JiveDig/wampum-user-forms
 * GitHub Branch:	   master
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get a login form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_form( $args = array() ) {
	return Wampum_User_Forms()->get_form( $args );
}

/**
 * Get a login form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_login_form( $args = array() ) {
	return Wampum_User_Forms()->login_form_callback( $args );
}

/**
 * Get a registration form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_register_form( $args = array() ) {
	return Wampum_User_Forms()->register_form_callback( $args );
}

/**
 * Get a password form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_password_form( $args = array() ) {
	return Wampum_User_Forms()->password_form_callback( $args );
}

/**
 * Get a membership form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_membership_form( $args = array() ) {
	return Wampum_User_Forms()->membership_form_callback( $args );
}

if ( ! class_exists( 'Wampum_User_Forms' ) ) :
/**
 * Main Wampum_User_Forms Class.
 *
 * @since 1.0.0
 */
final class Wampum_User_Forms {
	/**
	 * Singleton
	 * @var   Wampum_User_Forms The one true Wampum_User_Forms
	 * @since 1.0.0
	 */
	private static $instance;

	// Set form counter
	private $form_counter = 0;

	// Whether to load password script or not
	private $password_meter = false;

	/**
	 * Main Wampum_User_Forms Instance.
	 *
	 * Insures that only one instance of Wampum_User_Forms exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   1.0.0
	 * @static  var array $instance
	 * @uses    Wampum_User_Forms::setup_constants() Setup the constants needed.
	 * @uses    Wampum_User_Forms::includes() Include the required files.
	 * @uses    Wampum_User_Forms::load_textdomain() load the language files.
	 * @see     Wampum_User_Forms()
	 * @return  object | Wampum_User_Forms The one true Wampum_User_Forms
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new Wampum_User_Forms;
			// Methods
			self::$instance->setup_constants();
			self::$instance->setup();
		}
		return self::$instance;
	}
	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wampum' ), '1.0' );
	}
	/**
	 * Disable unserializing of the class.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wampum' ), '1.0' );
	}
	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'WAMPUM_USER_FORMS_VERSION' ) ) {
			define( 'WAMPUM_USER_FORMS_VERSION', '1.0.0' );
		}
		// Plugin Folder Path.
		if ( ! defined( 'WAMPUM_USER_FORMS_PLUGIN_DIR' ) ) {
			define( 'WAMPUM_USER_FORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
		// Plugin Includes Path
		if ( ! defined( 'WAMPUM_USER_FORMS_VENDOR_DIR' ) ) {
			define( 'WAMPUM_USER_FORMS_VENDOR_DIR', WAMPUM_USER_FORMS_PLUGIN_DIR . 'vendor/' );
		}
		// Plugin Folder URL.
		if ( ! defined( 'WAMPUM_USER_FORMS_PLUGIN_URL' ) ) {
			define( 'WAMPUM_USER_FORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		// Plugin Root File.
		if ( ! defined( 'WAMPUM_USER_FORMS_PLUGIN_FILE' ) ) {
			define( 'WAMPUM_USER_FORMS_PLUGIN_FILE', __FILE__ );
		}
		// Plugin Base Name
		if ( ! defined( 'WAMPUM_USER_FORMS_BASENAME' ) ) {
			define( 'WAMPUM_USER_FORMS_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}
	}

	/**
	 * Plugin hooks, filters, and shortcode
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	function setup() {

		// Register WP-API endpoint
		add_action( 'rest_api_init', 	  array( $this, 'register_rest_endpoints' ) );

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_stylesheets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		// Shortcodes
		add_shortcode( 'wampum_form', array( $this, 'get_form' ) );
		add_shortcode( 'wampum_login_form', array( $this, 'login_form_callback' ) );
		add_shortcode( 'wampum_register_form', array( $this, 'register_form_callback' ) );
		add_shortcode( 'wampum_password_form', array( $this, 'password_form_callback' ) );
		add_shortcode( 'wampum_membership_form', array( $this, 'membership_form_callback' ) );
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

	    register_rest_route( 'wampum/v1', '/register/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

	    register_rest_route( 'wampum/v1', '/password/', array(
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

		/* **** *
		 * POST *
		 * **** */

	    register_rest_route( 'wampum/v1', '/login/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'login' ),
		));

	    register_rest_route( 'wampum/v1', '/register/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'register' ),
		));

	    register_rest_route( 'wampum/v1', '/password/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_password' ),
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

	// This function displays a message when visiting the endpoint, to confirm it's actually registered
	function status() {
		return array(
			'success' => true,
			'message' => 'All is well in the world of Wampum'
		);
	}

	/**
	 * Login a user
	 *
	 * @since   1.0.0
	 *
	 * @param 	array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 * 		@type  string  $user_login 		Username
	 * 		@type  string  $user_password 	Password
	 * 		@type  string  $remember 		Stay logged in
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
			return array(
				'success' => true,
			);
		}
	}

	/**
	 * Register a user
	 *
	 * @since   1.0.0
	 *
	 * @param 	array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 * 		@type  string  		$user_email 		Email (required)
	 * 		@type  string  		$username 	 		Username
	 * 		@type  string  		$first_name 		First Name
	 * 		@type  string  		$last_name 			Last Name
	 * 		@type  string  		$password 	 		Password
	 * 		@type  bool    		$log_in 			Whether to auto log user in after registration
	 * 		@type  stringint 	$ac_list_ids 		The list ID to add
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
		if ( isset( $data['user_email'] ) && empty( $data['user_email'] ) ) {
			$email = $data['user_email'];
		} else {
			return array(
				'success' => false,
				'message' => __( 'Email is missing', 'wampum' ),
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
        $username = ( isset($data['username']) && $data['username'] ) ? $data['username'] : $email;
        $userdata['user_login'] = $username;

        // Set password. Set as variable first, cause we may need it later for wp_signon()
        $password = isset($data['password']) ? $data['password'] : wp_generate_password( $length = 12, $include_standard_special_chars = true );
        $userdata['user_pass'] = $password;

        // Create a new user
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
				'user_login'	=> $username,
				'user_password'	=> $password,
				'remember'		=> true,
	    	);
			$user = wp_signon( $signon_data );
        }

        // ActiveCampaign
        $this->maybe_do_active_campaign( $data );

		// Success
		return array(
			'success' => true,
		);

	}

	/**
	 * Save a user password
	 *
	 * @since   1.0.0
	 *
	 * @param 	array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 * 		@type  string  $password 		  Password
	 * 		@type  string  $password_confirm  Password again
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

		$user_data = array(
			'ID'		=> get_current_user_id(),
			'user_pass'	=> $data['password']
		);
		$user_id = wp_update_user($user_data);

		// If error
		if ( is_wp_error( $user_id ) ) {
			return array(
				'success' => false,
				'message' => $user_id->get_error_message(),
			);
		}
		// Success
		return array(
			'success' => true,
		);
	}

	/**
	 * Verify a user account doesn't already exist
	 *
	 * @since 	1.0.0
	 *
	 * @param   array  $data  Array of data to check user
	 *
	 * @param 	array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 * 		@type  string   $user_email 	(required) User email
	 * 		@type  string   $user_login 	Username
	 * 		@type  string   $say_what   	Username
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
	    if ( ! ( isset($data['user_email']) || $data['user_email'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter your email address', 'wampum' ),
			);
	    }

		$email = sanitize_email($data['user_email']);

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
	 * @since 	1.0.0
	 *
	 * @param   array  $data  Array of data when maybe creating a user and adding a membership to a user
	 *
	 * @param 	array  $data  {
	 *
	 *      Associative array of data to process
	 *
	 * 		@type  integer  $plan_id 		(required) The WooCommerce Memberships ID
	 * 		@type  string   $user_email 	(required) User email
	 * 		@type  string   $user_login 	Username
	 * 		@type  string   $user_pass 		Password
	 * 		@type  string   $first_name 	First name
	 * 		@type  string   $last_name	 	Last name
	 * 		@type  string   $note 		 	Note to add to membership during save
	 * 		@type  string   $notifications 	Comma-separated list of emails to notify upons successful submission
	 * 		@type  string   $say_what 		Honeypot field
	 * }
	 *
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
	    if ( ! ( $data['plan_id'] || $data['user_email'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Email or membership plan is missing', 'wampum' ),
			);
	    }

	    // TODO: Check and set all variables here. Sanitize too?

	    $email = sanitize_email($data['user_email']);

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

		    // Register a user, maybe logging them in.
	        $register = $this->register( $data );

	        // Bail if unsuccessful
	        if ( false == $register['success'] ) {
	        	return $register;
	        }

	    }

    	$plan_id = absint($data['plan_id']);

        // If user is not an existing member of the plan
        if ( ! wc_memberships_is_user_member( $user_id, $plan_id ) ) {

            // Add the user to the membership
            $membership_args = array(
                'plan_id'   => $plan_id,
                'user_id'   => $user_id,
            );
            wc_memberships_create_user_membership( $membership_args );

            // Get the new membership
            $user_membership = wc_memberships_get_user_membership( $user_id, $membership_args['plan_id'] );
            // Get the note
            $note = $data['note'] ? $data['note'] : 'Membership added via Wampum form at ' . esc_url($data['current_url']);
            // Add a note so we know how this was registered.
            $user_membership->add_note( sanitize_text_field($note) );
        }

        // If email notifications set, let's send away!
        if ( $data['notifications'] ) {

			$to		 = trim(sanitize_text_field($data['notifications']));
			$subject = get_bloginfo('name') . ' - ' . get_the_title($plan_id) . ' membership added';

			// Build the body
			$body = get_bloginfo('name') . ' - ' . get_the_title($plan_id) . ' membership added via Wampum form at ' . esc_url($data['current_url']);
	        if ( $data['first_name'] ) {
	            $body .= ' - ' . $data['first_name'];
	        }
	        if ( $data['last_name'] ) {
	            $body .= ' ' . $data['last_name'];
	        }
	        $body .= ' - ' .  $email;
	        // Send it
			wp_mail( $to, $subject, $body );
        }

        // ActiveCampaign
        $this->maybe_do_active_campaign( $data );

        // Success!
		return array(
			'success' => true,
			'user'	  => $user_id, // false|user_id If user was created in the process
		);

	}

	/**
	 * Maybe send data to ActiveCampaign
	 *
	 * @since  1.1.0
	 */
	function maybe_do_active_campaign( $data ) {

        // Bail if we have no email or list ID
        if ( ! ( $data['user_email'] || $data['ac_list_ids'] ) ) {
        	return;
        }

    	$list_ids = explode( ',', $data['ac_list_ids'] );

        /**
         * ActiveCampain data.
         * This should be admin only settings.
         * Do not expose publicly!
         */
		$ac_base_url	= 'https://bizbudding.api-us1.com';
		$ac_key			= '4bd29871bc566dfe7dccdf819cdc0001c59bec88d5270ad85905b341bb70b2d861928fa0';

        // If we have a URL and a key
        if ( $ac_base_url && $ac_key ) {

        	// Load the AC PHP library
        	require_once( WAMPUM_USER_FORMS_VENDOR_DIR . 'activecampaign-api-php/includes/ActiveCampaign.class.php' );

			// Setup AC
			$ac = new ActiveCampaign( esc_url($ac_base_url), sanitize_text_field($ac_key) );

			// Test API creds
			if ( (int) $ac->credentials_test() ) {

				// Start the contact array, email is required in our form
				$contact = array( 'email' => sanitize_email( $data['user_email'] ) );

				// Add first name if we have one
				if ( $data['first_name'] ) {
					$contact['first_name'] = sanitize_text_field( $data['first_name'] );
				}
				// Add last name if we have one
				if ( $data['last_name'] ) {
					$contact['last_name'] = sanitize_text_field( $data['last_name'] );
				}

				// Add user to existing ActiveCampaign lists
				foreach( $list_ids as $list_id ) {
					$contact["p[{$list_id}]"]	   = $list_id;
					$contact["status[{$list_id}]"] = 1; // "Active" status
				}

				// Do the thang
				$contact_sync = $ac->api( 'contact/sync', $contact );

			}

        }

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
	 * Register stylesheets for later use
	 *
	 * Use via wp_enqueue_style('wampum-user-forms'); in a template
	 *
	 * @since  1.0.0
	 *
	 * @return null
	 */
	function register_stylesheets() {
	    wp_register_style( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'css/wampum-user-forms.min.css', array(), WAMPUM_USER_FORMS_VERSION );
	}

	/**
	 * Register scripts for later use
	 *
	 * Use via wp_enqueue_script('wampum-login'); in a template
	 *
	 * @since  1.0.0
	 *
	 * @return null
	 */
	function register_scripts() {
		// All Forms
        wp_register_script( 'wampum-zxcvbn', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/zxcvbn.js', array('jquery'), '4.4.1', true );
        wp_register_script( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-forms.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        // wp_register_script( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-forms.min.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-forms', 'wampum_user_forms', array(
			'root'				=> esc_url_raw( rest_url() ),
			'nonce'				=> wp_create_nonce( 'wp_rest' ),
			'failure'			=> __( 'Something went wrong, please try again.', 'wampum' ),
			'current_url'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // For login URL if email/username exists and SharpSpring
			'login'	=> array(
				'empty'	=> __( 'Username and password fields are empty', 'wampum' ),
			),
			'password' => array(
				'mismatch'	=> __( 'Passwords do not match', 'wampum' ),
			),
        ) );
	}

	/**
	 * Enqueue scripts if there are forms present
	 * This needs to be called right in the form method
	 * Since it will be too early on 'wp_enqueue_scripts' hook
	 *
	 * @since  1.0.0
	 *
	 * @return null
	 */
	function enqueue_scripts() {
		if ( ( $this->form_counter > 0 ) ) {
			// CSS
			wp_enqueue_style('wampum-user-forms');
			// JS
			if ( $this->password_meter ) {
				wp_enqueue_script('wampum-zxcvbn');
			}
			wp_enqueue_script('wampum-user-forms');
		}
	}

	/**
	 * Get a form, by type.
	 *
	 * @since 	1.1.0
	 *
	 * @param 	array  $args  {
	 *
	 *      Associative array of args to build form.
	 *
	 * 		@type  string   $type 		 	 	(required) The type of form to return
	 * 		@type  bool 	$hidden			 	Whether to hide the form by default (display:none; inline style)
	 * 		@type  bool 	$inline			 	Display the form fields in a row
	 * 		@type  string   $title 			 	The form title to display
	 * 		@type  string   $title_wrap 	 	The title wrap element
	 * 		@type  string   $desc 			 	The form description to display
	 * 		@type  bool 	$first_name 	 	Whether to show first name field
	 * 		@type  bool 	$last_name	 	 	Whether to show last name field
	 * 		@type  bool 	$email  	 	 	Whether to show email field
	 * 		@type  bool 	$username 	 	 	Whether to show username field
	 * 		@type  bool 	$password 			Whether to show password field
	 * 		@type  bool 	$password_confirm   Whether to show password confirm field
	 * 		@type  bool 	$password_strength   Whether to show password strength meter
	 * 		@type  bool 	$require_first_name Whether to make the first name field required
	 * 		@type  bool 	$require_last_name 	Whether to make the last name field required
	 * 		@type  bool 	$require_email 		Whether to make the email field required
	 * 		@type  bool 	$require_username 	Whether to make the username field required
	 * 		@type  bool 	$require_password 	Whether to make the password field required
	 * 		@type  string 	$label_email 	    The label of the email field
	 * 		@type  string 	$value_email 	    The value to load in the email field
	 * 		@type  string 	$readonly_email	    Whether to set the email field as readonly
	 * 		@type  string 	$button	 		 	The button text to display
	 * 		@type  string   $notifications 	 	Comma-separated list of emails to notify upons successful submission
	 * 		@type  string   $redirect 		 	URL to redirect after form submission
	 * 		@type  integer 	$ac_list_ids	 	Comma-separated list of ActiveCampaign list IDs to add a contact to
	 * 		@type  integer 	$ac_tags	 	 	Comma-separated list of ActiveCampaign tag IDs to add a contact to
	 *
	 * 		// Login-specific form params
	 * 		@type  string 	$label_username 	The label of the username field
	 * 		@type  string  	$value_username	 	The value to load in the username field
	 * 		@type  bool  	$remember 		 	Whether to remember the values and stay logged in
	 * 		@type  bool  	$value_remember	 	Whether to start the 'remember' checkbox as checked
	 *
	 * 		// Register-specific form params
	 * 		@type  bool  	$log_in	 		 	Whether to log user in after registration
	 *
	 * 		// Membership-specific form params
	 * 		@type  integer  $plan_id 		 	(required) The WooCommerce Memberships ID
	 * 		@type  string  	$member_message	 	Message to display in place of the form if a logged in user is already a member
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
			'type'					=> '',
			'hidden'				=> false,
			'inline'				=> false,
			'title'					=> '',
			'title_wrap'			=> 'h3',
			'desc'					=> '',
			'first_name'			=> false,
			'last_name'				=> false,
			'email'					=> false,
			'username'				=> false,
			'password'				=> false,
			'password_confirm'		=> false,
			'password_strength'		=> false,
			'require_first_name'	=> false,
			'require_last_name'		=> false,
			'require_email'			=> true,
			'require_username'		=> false,
			// 'require_password'		=> false,
			'label_email'			=> __( 'Email', 'wampum' ),
			'value_email'			=> '',
			'readonly_email'		=> false,
			'button'				=> __( 'Submit', 'wampum' ),
			'notifications'			=> '',
			'redirect'				=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
			'ac_list_ids'			=> '',
			'ac_tags'				=> '',
			// Login-specific form params
			'label_username'		=> '',
			'value_username'		=> '',
			'remember'				=> true,
			'value_remember'		=> true,
			// Register-specific form params
			'log_in'				=> false,
			// Membership-specific form params
			'plan_id'				=> '',
			'member_message'		=> '',
		), $args, 'wampum_form' );

		// Sanitize the form type
		$type  = sanitize_text_field( $args['type'] );

		// Available form types
		$types = array( 'login', 'password', 'register', 'subscribe', 'user-available', 'join-membership', 'membership' );

		// Bail if we don't have a valid form type
		if ( ! in_array( $args['type'], $types ) ) {
			return;
		}

		/**
		 * Set default username field label.
		 * We do this late to give the registration form
		 * a chance to set it differently than the rest.
		 */
		if ( ! $args['label_username'] ) {
			if ( 'login' == $args['type'] ) {
				$args['label_username'] = __( 'Username/Email', 'wampum' );
			} else {
				$args['label_username'] = __( 'Username', 'wampum' );
			}
		}

		// Start the output
		$form = '';

		/**
		 * Force some form defaults
		 * foreach form type.
		 */

		// Login form
		if ( 'login' == $args['type'] ) {

			// Labels
			if ( empty( $args['title'] ) ) {
				$args['title'] = __( 'Log In', 'wampum' );
			}

			// Force
			$args['username']			= true;
			$args['password']			= true;
			$args['require_username']	= true;
			$args['first_name']			= false;
			$args['last_name']			= false;
			$args['email']				= false;
			$args['password_confirm']	= false;
			$args['password_strength']	= false;

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// Password form
		if ( 'password' == $args['type'] ) {

			// Labels
			if ( empty( $args['title'] ) ) {
				$args['title'] = __( 'Set A New Password', 'wampum' );
			}
			if ( empty( $args['button'] ) ) {
				$args['button'] = __( 'Save Password', 'wampum' );
			}

			// Force show
			$args['password']			= true;
			$args['password_confirm']	= true;
			$args['password_strength']	= true;

			// Force hide
			$args['first_name']	= false;
			$args['last_name']	= false;
			$args['email']		= false;
			$args['username']	= false;
			$args['remember']	= false;

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// Register form
		if ( 'register' == $args['type'] ) {

			// Labels
			if ( empty( $args['title'] ) ) {
				$args['title'] = __( 'Register Now', 'wampum' );
			}

			// Force show
			$args['email'] = true;

			// Force hide
			$args['remember'] = false;

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// Subscribe form
		if ( 'subscribe' == $args['type'] ) {

			// Labels
			if ( empty( $args['button'] ) ) {
				$args['button'] = __( 'Subscribe', 'wampum' );
			}

			// Force show
			$args['email'] = true;

			// Force hide
			$args['remember'] = false;

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// User available
		if ( 'user-available' == $args['type'] ) {

			// Force show
			$args['email']	  = true;
			$args['username'] = ( ! is_user_logged_in() && filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) ? true : false;

			// Force hide
			$args['password']			= false;
			$args['password_confirm']	= false;
			$args['remember']			= false;

			if ( is_user_logged_in() ) {
				$current_user			= wp_get_current_user();
				$args['first_name']		= $current_user->first_name;
				$args['last_name']		= $current_user->last_name;
				$args['value_email']	= $current_user->user_email;
			}

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// Join Membership
		if ( 'join-membership' == $args['type'] ) {

			// Force show
			$args['email'] = true;

			if ( is_user_logged_in() ) {
				$current_user			= wp_get_current_user();
				$args['first_name']		= $current_user->first_name;
				$args['last_name']		= $current_user->last_name;
				$args['value_email']	= $current_user->user_email;
				$args['readonly_email']	= true;
			} else {
				$args['hidden']		= true;
				$args['password']	= true;
			}

			$args['username'] = ( ! is_user_logged_in() && filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) ? true : false;

			// Increment the counter
			$this->form_counter++;

			// Get the form
			$form .= $this->get_form_html( $args );

		}

		// Membership form, includes User Available, Login, and Join Membership
		if ( 'membership' == $args['type'] ) {

			// Bail if WooCommerce Memberships is not active
			if ( ! function_exists( 'wc_memberships' ) ) {
				return;
			}

			// Bail if no plan ID
			if ( ! $args['plan_id'] ) {
				return;
			}

			// Return (with an optional message) if user is a logged in member of the plan
			if ( is_user_logged_in() && wc_memberships_is_user_member( get_current_user_id(), (int)$args['plan_id'] ) ) {
				$form .= $args['member_message'] ? wpautop($args['member_message']) : '';
			} else {

				/**
				 * Membership form is multi-step flow.
				 * We need to alter some args for each form
				 * and load them all in the same wrap.
				 */

				$args['remember'] = false;

				/* ************** *
				 * User Available *
				 * ************** */

				if ( ! is_user_logged_in() ) {

					// Increment the counter
					$this->form_counter++;

					// Get the form
					$args['type']	= 'user-available';
					$args['email']	= true;
					$form .= $this->get_form_html( $args );

				}

				/* *************** *
				 * Join Membership *
				 * *************** */

				// Increment the counter
				$this->form_counter++;

				// Get the form
				$args['type']	= 'join-membership';
				$args['hidden']	= false;
				if ( ! is_user_logged_in() ) {
					$args['hidden'] = true;
				}
				$form .= $this->get_form_html( $args );

				/* ***** *
				 * Login *
				 * ***** */

				// Increment the counter
				$this->form_counter++;

				$args['type']		= 'login';
				$args['title']		= __( 'Log In', 'wampum' );
				$args['hidden']		= true;
				$args['redirect']	= 'membership_form'; // Forces page reload via JS
				$form .= $this->get_form_html( $args );

			}

		}

		// If form is displaying a password field
		if ( $args['password'] ) {
			// Set password meter to true, so that script is loaded
			$this->password_meter = true;
		}

		// Scripts
		$this->enqueue_scripts();

		// Go get em tiger
		$attributes['class'] = 'wampum-form';
		return sprintf( '<div %s>%s</div>', genesis_attr( 'wampum-form-wrap', $attributes ), $form );

	}

	/**
	 *
	 */
	function get_form_html( $args ) {

		// Sanitize all the things
		$type				= sanitize_text_field( $args['type'] );
		$hidden				= filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN );
		$inline				= filter_var( $args['inline'], FILTER_VALIDATE_BOOLEAN );
		$title				= sanitize_text_field( $args['title'] );
		$title_wrap			= sanitize_text_field( $args['title_wrap'] );
		$desc				= sanitize_text_field( $args['desc'] );
		$first_name			= filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN );
		$last_name			= filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN );
		$email				= filter_var( $args['email'], FILTER_VALIDATE_BOOLEAN );
		$username			= filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN );
		$password			= filter_var( $args['password'], FILTER_VALIDATE_BOOLEAN );
		$password_confirm	= filter_var( $args['password_confirm'], FILTER_VALIDATE_BOOLEAN );
		$password_strength	= filter_var( $args['password_strength'], FILTER_VALIDATE_BOOLEAN );
		$require_first_name	= filter_var( $args['require_first_name'], FILTER_VALIDATE_BOOLEAN );
		$require_last_name	= filter_var( $args['require_last_name'], FILTER_VALIDATE_BOOLEAN );
		$require_email		= filter_var( $args['require_email'], FILTER_VALIDATE_BOOLEAN );
		$require_username	= filter_var( $args['require_username'], FILTER_VALIDATE_BOOLEAN );
		// $require_password	= filter_var( $args['require_password'], FILTER_VALIDATE_BOOLEAN );
		$value_email		= sanitize_text_field( $args['value_email'] );
		$label_email		= sanitize_text_field( $args['label_email'] );
		$readonly_email		= filter_var( $args['readonly_email'], FILTER_VALIDATE_BOOLEAN );
		$button				= sanitize_text_field( $args['button'] );
		$notifications		= array_map( 'trim', ( explode( ',', $args['notifications'] ) ) ); // Trim spaces around each email
		$notifications		= array_map( 'sanitize_email', $notifications ); // Sanitize the email
		$redirect			= sanitize_text_field( $args['redirect'] ); // Can't esc_url() cause we may allow strings to check against?
		$ac_list_ids		= sanitize_text_field( $args['ac_list_ids'] );
		$ac_tags			= sanitize_text_field( $args['ac_tags'] );
		$label_username		= sanitize_text_field( $args['label_username'] );
		$value_username		= sanitize_text_field( $args['value_username'] );
		$remember			= filter_var( $args['remember'], FILTER_VALIDATE_BOOLEAN );
		$value_remember		= filter_var( $args['value_remember'], FILTER_VALIDATE_BOOLEAN );
		$log_in				= filter_var( $args['log_in'], FILTER_VALIDATE_BOOLEAN );
		$plan_id			= intval( $args['plan_id'] );
		$member_message		= sanitize_text_field( $args['member_message'] );

		// Start the output
		$output = '';

		// Nothing to see here
		$row = $col = '';

		// If displaying inline
		if ( $inline ) {
			$row = 'row gutter-10 bottom-xs';
			$col = ' col col-xs-12 col-sm';
		}

		$required_asterisk = '<span class="required">*</span>';

		$type_attribute = sanitize_html_class( $type );

		// Default form attributes
		$attributes = array(
			'id'		=> 'wampum_user_form_' . $this->form_counter,
			'class'		=> 'wampum-user-form-' . $type_attribute,
			'name'		=> 'wampum_user_form_' . $this->form_counter,
			'data-form' => $type_attribute,
			'method'	=> 'post',
		);

		// Maybe hide the form
		if ( $hidden ) {
	        $attributes['style'] = 'display:none;';
		}

        // Open the form
        $output .= sprintf( '<form %s>', genesis_attr( 'wampum-form', $attributes ) );

        	// Title
			$output .= $title ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $title_wrap, $title, $title_wrap ) : '';

			// Description
			$output .= $desc ? sprintf( '<p class="wampum-form-desc">%s</p>', $desc ) : '';

			// Empty element to add form notices via JS
			$output .= '<div style="display:none;" class="wampum-notice"></div>';

			// Maybe start the row
			if ( $inline ) {
				$output .= sprintf( '<div class="%s">', $row );
			}

			// Honeypot
			$output .= '<p class="wampum-field wampum-say-what">';
				$output .= '<label for="wampum_say_what">Say What?</label>';
				$output .= '<input type="text" class="wampum_say_what" name="wampum_say_what" value="">';
			$output .= '</p>';

			// First name
			if ( $first_name ) {

				// Maybe require field
				$required		= $require_first_name ? ' required' : '';
				$required_html	= $require_first_name ? $required_asterisk : '';

				$output .= sprintf( '<p class="wampum-field wampum-first-name%s%s">', $col, $required );
					$output .= sprintf( '<label for="wampum_first_name">%s%s</label>', __( 'First Name', 'wampum' ), $required_html );
					$output .= sprintf( '<input type="text" class="wampum_first_name" name="wampum_first_name" value=""%s>', $required );
				$output .= '</p>';

			}

			// Last name
			if ( $last_name ) {

				// Maybe require field
				$required		= $require_last_name ? ' required' : '';
				$required_html	= $require_last_name ? $required_asterisk : '';

				$output .= sprintf( '<p class="wampum-field wampum-last-name%s">', $col );
					$output .= sprintf( '<label for="wampum_last_name">%s%s</label>', __( 'Last Name', 'wampum' ), $required_html );
					$output .= sprintf( '<input type="text" class="wampum_last_name" name="wampum_last_name" value=""%s>', $required );
				$output .= '</p>';

			}

			// Email
			if ( $email ) {

				$readonly = $readonly_email ? ' readonly' : '';

				// Maybe require field
				$required		= $require_email ? ' required' : '';
				$required_html	= $require_email ? $required_asterisk : '';

				// Email
				$output .= sprintf( '<p class="wampum-field wampum-email%s%s">', $col, $readonly );
					$output .= sprintf( '<label for="wampum_user_email">%s%s</label>', $label_email, $required_html );
					$output .= sprintf( '<input type="text" class="wampum_user_email" name="wampum_user_email" value="%s"%s%s>', $value_email, $readonly, $required );
				$output .= '</p>';

			}

			// Username
			if ( $username ) {

				// Maybe require field
				$required		= $require_username ? ' required' : '';
				$required_html	= $require_username ? $required_asterisk : '';

				$output .= sprintf( '<p class="wampum-field wampum-username%s">', $col );
					$output .= sprintf( '<label for="wampum_username">%s%s</label>', __( 'Username', 'wampum' ), $required_html );
					$output .= sprintf( '<input type="text" class="wampum_username" name="wampum_username" value="%s"%s>', $value_username, $required );
				$output .= '</p>';

			}

			// Password
			if ( $password ) {

				// Password (always required if shown)
				$output .= sprintf( '<p class="wampum-field wampum-password%s">', $col );
					$output .= sprintf( '<label for="wampum_user_password">%s%s</label>', __( 'Password', 'wampum' ), $required_asterisk );
					$output .= '<input type="password" name="wampum_user_password" class="wampum_user_password" value="" require>';
				$output .= '</p>';

				// Password confirm
				if ( $password_confirm ) {

					// Confirm password (always required if shown)
					$output .= '<p class="wampum-field password-confirm">';
						$output .= sprintf( '<label for="wampum_user_password_confirm">%s%s</label>', __( 'Confirm Password', 'wampum' ), $required_asterisk );
						$output .= '<input type="password" name="wampum_user_password_confirm" class="wampum_user_password_confirm" value="" required>';
					$output .= '</p>';

				}

				// Password strength meter
				if ( $password_strength ) {

					$output .= '<p style="display:none;" class="wampum-field password-strength">';
						$output .= '<span class="password-strength-meter" data-strength="">';
							$output .= '<span class="password-strength-color">';
								$output .= '<span class="password-strength-text"></span>';
							$output .= '</span>';
						$output .= '</span>';
					$output .= '</p>';

				}

			}

			// Remember
			if ( $remember ) {

				$output .= sprintf( '<p class="wampum-field wampum-remember%s">', $col );
					$output .= sprintf( '<label><input name="rememberme" type="checkbox" class="wampum_rememberme" value="forever" checked="checked">%s</label>', __( 'Remember Me', 'wampum' ) );
				$output .= '</p>';

			}

			// Submit
			$output .= sprintf( '<p class="wampum-field wampum-submit%s">', $col );
				$output .= sprintf( '<button class="wampum_submit button" type="submit" form="wampum_user_form_%s">%s</button>', $this->form_counter, $button );
				$output .= sprintf( '<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="%s">', $redirect );
			$output .= '</p>';

			// Maybe end the row
			if ( $inline ) {
				$output .= '</div>';
			}

		$output .= '</form>';

		// Hide the honeypot field
		if ( $this->form_counter > 0 ) {
			$output .= '<style media="screen" type="text/css">.wampum-say-what { display: none; visibility: hidden; }</style>';
		}

		return $output;

	}

	/**
	 * Get a login form, with the wrapper
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function login_form_callback( $args ) {
		// Bail if already logged in
		if ( is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'login';
		return $this->get_form( $args );
		// return sprintf( '<div class="wampum-form">%s</div>', $this->get_login_form( $args ) );
	}

	/**
	 * Get a registration form, with the wrapper
	 *
	 * @since  1.1.0
	 *
	 * @return string  the form
	 */
	function register_form_callback( $args ) {
		// Bail if already logged in
		if ( is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'register';
		return $this->get_form( $args );
		// return sprintf( '<div class="wampum-form">%s</div>', $this->get_register_form( $args ) );
	}

	// TODO: Docs
	function subscribe_form_callback( $args ) {
		$args['type'] = 'subscribe';
		return $this->get_form( $args );
	}

	/**
	 * Get a password form, with the wrapper
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function password_form_callback( $args ) {
		// Bail if user is not logged in
		if ( ! is_user_logged_in() ) {
			return;
		}
		$args['type'] = 'password';
		return $this->get_form( $args );

		// Set password meter to true, so that script is loaded
		// $this->password_meter = true;

		// Send it!
		// return sprintf( '<div class="wampum-form">%s</div>', $this->get_password_form( $args ) );
	}

	/**
	 * Get a membership form, with the wrapper
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function membership_form_callback( $args ) {
		// Bail if WooCommerce Memberships is not active
		if ( ! function_exists( 'wc_memberships' ) ) {
			return;
		}

		// The full $args list is parsed in get_{name}_form() method
		$defaults = array(
			'plan_id' => false, // required
		);
		$args = wp_parse_args( $args, $defaults );

		// Bail if no plan ID
		if ( in_array( $args['plan_id'], array( false, 'false' ) ) ) {
			return;
		}

		$args['type'] = 'membership';
		return $this->get_form( $args );

		/**
		 * Bail if no membership form.
		 * This happens when a logged in user is already a member
		 * and there is no notice to display for logged in members ( via $args['member_message'] ).
		 */
		$membership_form = $this->get_membership_form( $args );
		if ( ! $membership_form ) {
			return;
		}

		// Set password meter to true, so that script is loaded
		$this->password_meter = true;

		// Send it!
		return sprintf( '<div class="wampum-form">%s%s</div>',
			$membership_form,
			$this->get_login_form( array( 'hidden' => true ) ) // If form used in membership on-boarding, this tells us to refresh to current page
		);
	}

	/**
	 * Get a login form
	 * Increment the internal counter
	 * Enqueue scripts
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function get_login_form( $args ) {

		ob_start();

		$hidden  = '';
		if ( filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN ) ) {
			$hidden = ' style="display:none;"';
		}
		?>
		<form<?php echo $hidden; ?> id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-login-form" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

			<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>
			<?php echo $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : ''; ?>

			<div style="display:none;" class="wampum-notice"></div>

			<p class="wampum-field login">
				<label for="wampum_user_login"><?php _e( 'Username/Email', 'wampum' ); ?><span class="required">*</span></label>
				<input type="text" name="wampum_user_login" class="wampum_user_login" value="<?php echo $args['value_username']; ?>" required>
			</p>

			<p class="wampum-field password">
				<label for="wampum_user_pass"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="wampum_user_pass" class="wampum_user_pass" value="" required>
			</p>

			<?php if ( filter_var( $args['remember'], FILTER_VALIDATE_BOOLEAN ) ) { ?>
				<p class="wampum-field remember">
					<label><input name="rememberme" type="checkbox" class="wampum_rememberme" value="forever" checked="checked"> <?php _e( 'Remember Me', 'wampum' ); ?></label>
				</p>
			<?php } ?>

			<p class="wampum-field wampum-submit login-submit">
				<button class="wampum_submit button" type="submit" form="wampum_user_form_<?php echo $this->form_counter; ?>"><?php echo $args['button']; ?></button>
				<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
			</p>

		</form>
		<?php
		return ob_get_clean();

	}

	/**
	 * Get a registration form
	 * Increment the internal counter
	 * Enqueue scripts
	 *
	 * @since  1.1.0
	 *
	 * @return string  the form
	 */
	function get_register_form( $args ) {

		// Increment the counter
		$this->form_counter++;

		$this->enqueue_scripts();

		$args = shortcode_atts( array(
			'hidden'		=> false,
			'title'			=> __( 'Register', 'wampum' ),
			'title_wrap'	=> 'h3',
			'desc'			=> '',
			'button'		=> __( 'Submit', 'wampum' ),
			'redirect'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
			'first_name'	=> false,
			'last_name'		=> false,
			'username'		=> false,
			'password'		=> false,
			'log_in'		=> false, // Whether to log user in after register
			'ac_list_ids' 	=> false, // Comma separated list of IDs
		), $args, 'wampum_register_form' );

		ob_start();

		$hidden  = '';
		if ( filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN ) ) {
			$hidden = ' style="display:none;"';
		}
		?>
		<form<?php echo $hidden; ?> id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-register-form" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

			<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>
			<?php echo $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : ''; ?>

			<div style="display:none;" class="wampum-notice"></div>

			<p class="wampum-field wampum-say-what">
				<label for="wampum_say_what">Say What?</label>
				<input type="text" class="wampum_say_what" name="wampum_say_what" value="">
			</p>

			<?php if ( filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field register-name register-first-name">
					<label for="wampum_register_first_name"><?php _e( 'First Name', 'wampum' ); ?></label>
					<input type="text" class="wampum_first_name" name="wampum_register_first_name" value="">
				</p>

			<?php } ?>

			<?php if ( filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field register-name register-last-name">
					<label for="wampum_register_last_name"><?php _e( 'Last Name', 'wampum' ); ?></label>
					<input type="text" class="wampum_last_name" name="wampum_register_last_name" value="">
				</p>

			<?php } ?>

			<p class="wampum-field register-email">
				<label for="wampum_user_email"><?php _e( 'Email', 'wampum' ); ?><span class="required">*</span></label>
				<input type="text" name="wampum_user_email" class="wampum_user_email" value="" required>
			</p>

			<?php if ( filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field register-username">
					<label for="wampum_register_user_login"><?php _e( 'Username', 'wampum' ); ?></label>
					<input type="text" class="wampum_user_login" name="wampum_register_user_login" value="">
				</p>

			<?php } ?>

			<?php if ( filter_var( $args['password'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field register-password">
					<label for="wampum_user_pass"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
					<input type="password" name="wampum_user_pass" class="wampum_user_pass" value="" required>
				</p>

			<?php } ?>

			<p class="wampum-field wampum-submit login-submit">
				<button class="wampum_submit button" type="submit" form="wampum_user_form_<?php echo $this->form_counter; ?>"><?php echo $args['button']; ?></button>
				<?php if ( $args['log_in'] ) { ?>
					<input type="hidden" name="wampum_log_in" class="wampum_log_in" value="<?php echo filter_var( $args['log_in'], FILTER_VALIDATE_BOOLEAN ); ?>">
				<?php } ?>
				<?php if ( $args['ac_list_ids'] ) { ?>
					<input type="hidden" name="wampum_ac_list_ids" class="wampum_ac_list_ids" value="<?php echo absint( $args['ac_list_ids'] ); ?>">
				<?php } ?>
				<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
			</p>

		</form>
		<style media="screen" type="text/css">.wampum-say-what { display: none; visibility: hidden; }</style>
		<?php
		return ob_get_clean();

	}

	function get_subscribe_form( $args ) {
		// TODO: All the things
		return;
	}

	/**
	 * Get a password form
	 * Increment the internal counter
	 * Enqueue scripts
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function get_password_form( $args ) {

		// Increment the counter
		$this->form_counter++;

		$this->enqueue_scripts();

		$args = shortcode_atts( array(
			'hidden'		=> false,
			'title'			=> __( 'Set A New Password', 'wampum' ),
			'title_wrap'	=> 'h3',
			'desc'			=> '',
			'button'		=> __( 'Save Password', 'wampum' ),
			'redirect'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
		), $args, 'wampum_password_form' );

		ob_start();

		$hidden  = '';
		if ( filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN ) ) {
			$hidden = ' style="display:none;"';
		}
		?>
		<form<?php echo $hidden; ?> id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-password-form" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

			<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>
			<?php echo $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : ''; ?>

			<div style="display:none;" class="wampum-notice"></div>

			<p class="wampum-field password">
				<label for="wampum_password"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="log" class="wampum_password" value="" required>
			</p>

			<p class="wampum-field password-confirm">
				<label for="wampum_password_confirm"><?php _e( 'Confirm Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="wampum_password_confirm" class="wampum_password_confirm" value="" required>
			</p>

			<p style="display:none;" class="wampum-field password-strength">
				<span class="password-strength-meter" data-strength="">
					<span class="password-strength-color">
						<span class="password-strength-text"></span>
					</span>
				</span>
			</p>

			<p class="wampum-field wampum-submit password-submit">
				<button class="wampum_submit button" type="submit" form="wampum_user_form_<?php echo $this->form_counter; ?>"><?php echo $args['button']; ?></button>
				<input type="hidden" name="wampum_user_id" class="wampum_user_id" value="<?php echo get_current_user_id(); ?>">
				<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
			</p>

		</form>
		<?php
		return ob_get_clean();

	}

	/**
	 * Get a membership form
	 * Increment the internal counter
	 * Enqueue scripts
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function get_membership_form( $args ) {

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$args = shortcode_atts( array(
			'plan_id'			=> null, // required
			'title'				=> null,
			'title_wrap'		=> 'h3',
			'desc'				=> '',
			'button'			=> __( 'Submit', 'wampum' ),
			'redirect'			=> $current_url,  // a url or null
			'first_name'		=> true,
			'last_name'			=> false,
			'username'			=> false,
			'member_message'	=> '',
			'notifications'		=> null, // mike@bizbudding.com, dave@bizbudding.com
			'ss_baseuri'		=> '', 	 // 'https://app-3QMU9AFX44.marketingautomation.services/webforms/receivePostback/MzawMDE2MjCwAAA/'
			'ss_endpoint'		=> '', 	 // 'b19a2e43-3904-4b80-b587-353767f56849'
		), $args, 'wampum_membership_form' );

		// Bail if no plan ID
		if ( in_array( $args['plan_id'], array( false, 'false' ) ) ) {
			return;
		}

		// Return (with an optional message) if user is a logged in member of the plan
		if ( is_user_logged_in() && wc_memberships_is_user_member( get_current_user_id(), (int)$args['plan_id'] ) ) {
			return $args['member_message'] ? wpautop($args['member_message']) : '';
		}

		$first_name = $last_name = $email = $readonly = '';
		if ( is_user_logged_in() ) {
			$current_user	= wp_get_current_user();
			$first_name		= $current_user->first_name;
			$last_name		= $current_user->last_name;
			$email			= $current_user->user_email;
			$readonly		= ' readonly';
		}
		?>

		<?php ob_start(); ?>

		<?php if ( ! is_user_logged_in() ) { ?>

			<?php
			// Increment the counter
			$this->form_counter++;
			?>

			<form id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-membership-form-verify" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

				<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>
				<?php echo $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : ''; ?>

				<div style="display:none;" class="wampum-notice"></div>

				<p class="wampum-field wampum-say-what">
					<label for="wampum_say_what">Say What?</label>
					<input type="text" class="wampum_say_what" name="wampum_say_what" value="">
				</p>

				<?php if ( filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-first-name">
						<label for="wampum_membership_first_name"><?php _e( 'First Name', 'wampum' ); ?></label>
						<input type="text" class="wampum_first_name" name="wampum_membership_first_name" value="<?php echo $first_name; ?>">
					</p>

				<?php } ?>

				<?php if ( filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-last-name">
						<label for="wampum_membership_last_name"><?php _e( 'Last Name', 'wampum' ); ?></label>
						<input type="text" class="wampum_last_name" name="wampum_membership_last_name" value="<?php echo $last_name; ?>">
					</p>

				<?php } ?>

				<p class="wampum-field<?php echo $readonly; ?> membership-email">
					<label for="wampum_membership_email"><?php _e( 'Email', 'wampum' ); ?><span class="required">*</span></label>
					<input type="email" class="wampum_email" name="wampum_membership_email" value="<?php echo $email; ?>" required<?php echo $readonly; ?>>
				</p>

				<?php if ( ! is_user_logged_in() && filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-username">
						<label for="wampum_membership_username"><?php _e( 'Username', 'wampum' ); ?><span class="required">*</span></label>
						<input type="text" class="wampum_username" name="wampum_membership_username" value="" required>
					</p>

				<?php }	?>

				<p class="wampum-field wampum-submit membership-submit">
					<button class="wampum_submit button<?php echo is_user_logged_in() ? '' : ' paged'; ?>" type="submit" form="wampum_user_form_<?php echo $this->form_counter; ?>"><?php echo $args['button']; ?></button>
					<input type="hidden" class="wampum_membership_success" name="wampum_membership_success" value="0">
					<input type="hidden" class="wampum_current_url" name="wampum_current_url" value="<?php echo $current_url; ?>">
					<?php
					if ( $args['ss_baseuri'] && $args['ss_endpoint'] ) {
						// SharpSpring baseURI
						echo '<input type="hidden" class="wampum_ss_baseuri" name="wampum_ss_baseuri" value="' . sanitize_text_field($args['ss_baseuri']) . '">';
						// SharpSpring endpoint
						echo '<input type="hidden" class="wampum_ss_endpoint" name="wampum_ss_endpoint" value="' . sanitize_text_field($args['ss_endpoint']) . '">';
					}
					?>
				</p>

			</form>
		<?php } ?>

		<?php
		// Increment the counter
		$this->form_counter++;

		$this->enqueue_scripts();

		$hidden  = '';
		if ( ! is_user_logged_in() ) {
			$hidden = ' style="display:none;"';
		}
		?>
		<form<?php echo $hidden; ?> id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-membership-form" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

			<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>
			<?php echo $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : ''; ?>

			<div style="display:none;" class="wampum-notice"></div>

			<p class="wampum-field wampum-say-what">
				<label for="wampum_say_what">Say What?</label>
				<input type="text" class="wampum_say_what" name="wampum_say_what" value="">
			</p>

			<?php if ( filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field membership-name membership-first-name">
					<label for="wampum_membership_first_name"><?php _e( 'First Name', 'wampum' ); ?></label>
					<input type="text" class="wampum_first_name" name="wampum_membership_first_name" value="<?php echo $first_name; ?>">
				</p>

			<?php } ?>

			<?php if ( filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field membership-name membership-last-name">
					<label for="wampum_membership_last_name"><?php _e( 'Last Name', 'wampum' ); ?></label>
					<input type="text" class="wampum_last_name" name="wampum_membership_last_name" value="<?php echo $last_name; ?>">
				</p>

			<?php } ?>

			<p class="wampum-field<?php echo $readonly; ?> membership-email">
				<label for="wampum_membership_email"><?php _e( 'Email', 'wampum' ); ?><span class="required">*</span></label>
				<input type="email" class="wampum_email" name="wampum_membership_email" value="<?php echo $email; ?>" required<?php echo $readonly; ?>>
			</p>

			<?php if ( ! is_user_logged_in() && filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

				<p class="wampum-field membership-username">
					<label for="wampum_membership_username"><?php _e( 'Username', 'wampum' ); ?><span class="required">*</span></label>
					<input type="text" class="wampum_username" name="wampum_membership_username" value="" required>
				</p>

			<?php }	?>

			<?php if ( ! is_user_logged_in() ) { ?>

				<p class="wampum-field membership-password">
					<label for="wampum_password"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
					<input type="password" class="wampum_password" name="wampum_password" value="" required>
				</p>

				<p style="display:none;" class="wampum-field password-strength">
					<span class="password-strength-meter" data-strength="">
						<span class="password-strength-color">
							<span class="password-strength-text"><?php _e( 'Strength', 'wampum' ); ?></span>
						</span>
					</span>
				</p>

			<?php }	?>

			<p class="wampum-field wampum-submit membership-submit">
				<button class="wampum_submit button<?php echo is_user_logged_in() ? '' : ' paged'; ?>" type="submit" form="wampum_user_form_<?php echo $this->form_counter; ?>"><?php echo $args['button']; ?></button>
				<input type="hidden" class="wampum_membership_success" name="wampum_membership_success" value="1">
				<input type="hidden" class="wampum_notifications" name="wampum_notifications" value="<?php echo $args['notifications']; ?>">
				<input type="hidden" class="wampum_plan_id" name="wampum_plan_id" value="<?php echo $args['plan_id']; ?>">
				<input type="hidden" class="wampum_current_url" name="wampum_current_url" value="<?php echo $current_url; ?>">
				<input type="hidden" class="wampum_redirect" name="wampum_redirect" value="<?php echo $args['redirect']; ?>">
				<?php
				if ( $args['ss_baseuri'] && $args['ss_endpoint'] ) {
					// SharpSpring baseURI
					echo '<input type="hidden" class="wampum_ss_baseuri" name="wampum_ss_baseuri" value="' . sanitize_text_field($args['ss_baseuri']) . '">';
					// SharpSpring endpoint
					echo '<input type="hidden" class="wampum_ss_endpoint" name="wampum_ss_endpoint" value="' . sanitize_text_field($args['ss_endpoint']) . '">';
				}
				?>
			</p>

		</form>
		<style media="screen" type="text/css">.wampum-say-what { display: none; visibility: hidden; }</style>
		<?php
		return ob_get_clean();
	}

}
endif; // End if class_exists check.
/**
 * The main function for that returns Wampum_User_Forms
 *
 * The main function responsible for returning the one true Wampum_User_Forms
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wampum_user_forms = Wampum_User_Forms(); ?>
 *
 * @since 1.0.0
 *
 * @return object|Wampum_User_Forms The one true Wampum_User_Forms Instance.
 */
function Wampum_User_Forms() {
	return Wampum_User_Forms::instance();
}
// Get Wampum_User_Forms Running.
Wampum_User_Forms();
