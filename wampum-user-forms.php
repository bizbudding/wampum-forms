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
function wampum_get_login_form( $args = array() ) {
	return Wampum_User_Forms()->login_form_callback( $args );
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
		if ( ! defined( 'WAMPUM_USER_FORMS_INCLUDES_DIR' ) ) {
			define( 'WAMPUM_USER_FORMS_INCLUDES_DIR', WAMPUM_USER_FORMS_PLUGIN_DIR . 'includes/' );
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
		add_shortcode( 'wampum_login_form', array( $this, 'login_form_callback' ) );
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

	    register_rest_route( 'wampum/v1', '/password/', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'status' ),
		));

	    register_rest_route( 'wampum/v1', '/membership-verify/', array(
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

	    register_rest_route( 'wampum/v1', '/password/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_password' ),
		));

	    register_rest_route( 'wampum/v1', '/membership-verify/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'membership_verify' ),
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
	function membership_verify( $data ) {

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

	    	$user_created = false;

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

	        // Set username. Set as variable first, cause we need it later for wp_signon()
            $username = ( isset($data['username']) && $data['username'] ) ? $data['username'] : $email;
            $userdata['user_login'] = $username;

	        // Set password. Set as variable first, cause we need it later for wp_signon()
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

	        // Log them in!
	        $signon_data = array(
				'user_login'	=> $username,
				'user_password'	=> $password,
				'remember'		=> true,
	    	);
			$user = wp_signon( $signon_data );

	        // If it's an error, return it
	        if ( is_wp_error( $user ) ) {
				return array(
					'success' => false,
					'message' => $user->get_error_message(),
				);
	        }

	        // Set user as logged in
			wp_set_current_user( $user->ID );
			if ( wp_validate_auth_cookie( '', 'logged_in' ) != $user->ID ) {
			    wp_set_auth_cookie( $user->ID, true );
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
			$to			= trim(sanitize_text_field($data['notifications']));
			$subject	= get_bloginfo('name') . ' - ' . get_the_title($plan_id) . ' membership added';
			$body		= get_bloginfo('name') . ' - ' . get_the_title($plan_id) . ' membership added via Wampum form at ' . esc_url($data['current_url']);
			wp_mail( $to, $subject, $body );
        }

        // Success!
		return array(
			'success' => true,
			'user'	  => $user_id, // false|user_id If user was created in the process
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
        wp_register_script( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-forms.min.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
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
		return sprintf( '<div class="wampum-form">%s</div>', $this->get_login_form( $args ) );
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

		// Set password meter to true, so that script is loaded
		$this->password_meter = true;

		// Send it!
		return sprintf( '<div class="wampum-form">%s</div>', $this->get_password_form( $args ) );
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

		/**
		 * Bail if no membership form
		 * This happens when a logged in user is already a member
		 * and there is no notice to display for logged in  members ( via $args['member_message'] )
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

		// Increment the counter
		$this->form_counter++;

		$this->enqueue_scripts();

		$args = shortcode_atts( array(
			'hidden'		 => false,
			'title'			 => __( 'Login', 'wampum' ),
			'title_wrap'	 => 'h3',
			'button'		 => __( 'Log In', 'wampum' ),
			'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
			'remember'       => true,
			'value_username' => '',
			'value_remember' => true,
		), $args, 'wampum_login_form' );

		ob_start();

		$hidden  = '';
		if ( filter_var( $args['hidden'], FILTER_VALIDATE_BOOLEAN ) ) {
			$hidden = ' style="display:none;"';
		}
		?>
		<form<?php echo $hidden; ?> id="wampum_user_form_<?php echo $this->form_counter; ?>" class="wampum-user-login-form" name="wampum_user_form_<?php echo $this->form_counter; ?>" method="post">

			<?php echo $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : ''; ?>

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

		$args = shortcode_atts( array(
			'plan_id'			=> null, // required
			'title'				=> null,
			'title_wrap'		=> 'h3',
			'button'			=> __( 'Submit', 'wampum' ),
			'redirect'			=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],  // a url or null
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
