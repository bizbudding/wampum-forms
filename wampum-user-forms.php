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
 * Description: 	   Add login and password forms that use the WP-API form processing
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
			self::$instance->includes();
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
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		// Vendor
		require_once WAMPUM_USER_FORMS_INCLUDES_DIR . '/forms.php';
	}

	function setup() {
		register_activation_hook( __FILE__,   array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register WP-API endpoint
		add_action( 'rest_api_init', 	  array( $this, 'register_rest_endpoints' ) );

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_stylesheets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		add_action( 'wp_footer', array( $this, 'query_var_forms' ) );
	}

	function activate() {
	}
	function deactivate() {
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

	    register_rest_route( 'wampum/v1', '/membership/', array(
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

	    register_rest_route( 'wampum/v1', '/membership/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'add_to_membership' ),
	    ));

	}

	// This function displays a message when visiting the endpoint, to confirm it's actually registered
	function status() {
		return array( 'success' => 'All is well in the world of Wampum' );
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
			return array(
				'success' => true,
			);
		}
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
	 * 		@type  string   $redirect 		URL to redirect after form submission
	 * 		@type  string   $say_what 		Honeypot field
	 * }
	 *
	 *
	 * @return  bool|WP_Error  Whether a new user was created during the process
	 */
	function add_to_membership( $data ) {

	    // Bail if Woo Memberships is not active
	    if ( ! function_exists( 'wc_memberships' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Membership is currently inactive', 'wampum' ),
			);
	    }

	    // Honeypot
		$this->validate_say_what($data);

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
	    	 * Email field should be disabled, but just incase...
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

		    // If the email or username is already a registered user
		    if ( $email_exists || $username_exists ) {
		    	// Set in wp_localize_script() because calling here returns WP-API endpoing URL
	    		$current_url = $data['current_url'];
		    	if ( function_exists('wampum_popup') ) {
		    		// Call our login form in a popup
		    		$login_url = add_query_arg( 'user', 'login', $current_url );
		    	} else {
		    		// Go to login url and redirect back here
			    	$login_url = wp_login_url( $current_url );
		    	}
				return array(
					'success' => false,
					'message' => __( 'A user account already exists with that info.', 'wampum' ) . ' <a class="login-link" href="' . esc_url($login_url) . '" title="Log in">Log in?</a>',
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

			/**
			 * If user was not logged in and there was no password field
			 * If this were the case, a password was generated, so we should make them change it
			 */
			if ( function_exists('wampum_popup') ) {
				if ( ! ( isset($data['password']) || $data['password'] ) ) {
					$data['redirect'] = add_query_arg( 'user', 'password', $data['redirect'] );
				}
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

            // If we have a note, save it to the user membership
            if ( $data['note'] ) {
                // Get the new membership
                $user_membership = wc_memberships_get_user_membership( $user_id, $membership_args['plan_id'] );
                // Add a note so we know how this was registered.
                $user_membership->add_note( sanitize_text_field($data['note']) );
            }
        }

        // Success!
		return array(
			'success'  => true,
			'redirect' => esc_url($data['redirect']),
		);

	}

	function validate_say_what( $data ) {
		if ( '' != $data['say_what '] ) {
			return array(
				'success' => false,
				'message' => __( 'Spam detected', 'wampum' ),
			);
		}
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
	    wp_register_style( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'css/wampum-user-forms.css', array(), WAMPUM_USER_FORMS_VERSION );
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
        wp_register_script( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-forms.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-forms', 'wampum_user_forms', array(
			'root'				=> esc_url_raw( rest_url() ),
			'nonce'				=> wp_create_nonce( 'wp_rest' ),
			'failure'			=> __( 'Something went wrong, please try again.', 'wampum' ),
			'current_url'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // For login URL if email/username exists
			'current_user_id'	=> get_current_user_id(), // For rest endpoint 'wp/v2/users/123'
			'login'	=> array(
				'form'	=> wampum_get_login_form(),
				'empty'	=> __( 'Username and password fields are empty', 'wampum' ), // Why are these fields not required in WP?!?!
			),
			'password' => array(
				'form'		=> wampum_get_password_form(),
				'mismatch'	=> __( 'Passwords do not match', 'wampum' ),
			),
			'membership' => array(
				'form' => wampum_get_membership_form(),
			),
        ) );

		// Login
        wp_register_script( 'wampum-user-login', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-login.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-login', 'wampum_user_login', array(
			'root'			=> esc_url_raw( rest_url() ),
			'nonce'			=> wp_create_nonce( 'wp_rest' ),
			'empty'			=> __( 'Username and password fields are empty', 'wampum' ), // Why are these fields not required in WP?!?!
			'failure'		=> __( 'Something went wrong, please try again.', 'wampum' ),
        ) );

        // Password
        wp_register_script( 'wampum-zxcvbn', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/zxcvbn.js', array('jquery'), '4.4.1', true );
        wp_register_script( 'wampum-user-password', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-password.js', array('wampum-zxcvbn'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-password', 'wampum_user_password', array(
			'root'				=> esc_url_raw( rest_url() ),
			'nonce'				=> wp_create_nonce( 'wp_rest' ),
			'current_user_id'	=> get_current_user_id(), // For rest endpoint 'wp/v2/users/123'
			'mismatch'			=> __( 'Passwords do not match', 'wampum' ),
			'failure'			=> __( 'Something went wrong, please try again.', 'wampum' ),
        ) );

		// Membership
        wp_register_script( 'wampum-user-membership', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-membership.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-membership', 'wampum_user_membership', array(
			'root'			=> esc_url_raw( rest_url() ),
			'nonce'			=> wp_create_nonce( 'wp_rest' ),
			'current_url'	=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // For login URL if email/username exists
			'failure'		=> __( 'Something went wrong, please try again.', 'wampum' ),
        ) );

	}

	function query_var_forms() {
	    if ( ! function_exists('wampum_popup') ) {
	    	return;
	    }
	    // Bail if no user parameter set
	    if ( ! isset($_GET['user']) ) {
	        return;
	    }
	    $vars = array(
	    	'login',
	    	'password'
    	);
    	if ( ! in_array( $_GET['user'], $vars ) ) {
    		return;
    	}
	    // Login form
	    if ( 'login' == $_GET['user'] && ! is_user_logged_in() ) {
	    	add_action( 'wampum_popups', array( $this, 'do_login_form') );
        }
	    // Password form
	    elseif ( 'password' == $_GET['user'] && is_user_logged_in() ) {
	    	add_action( 'wampum_popups', array( $this, 'do_password_form') );
	    }
	}

	function do_login_form() {
		$args = array(
			'title'		=> __( 'Login', 'wampum' ),
			'redirect'	=> home_url( remove_query_arg('user') ),
		);
		$content = wampum_get_login_form( $args );
		if ( ! $content ) {
			return;
		}
		wampum_popup( $content, array( 'hidden' => false ) );
	}

	function do_password_form() {
		$args = array(
			'title'		=> __( 'Set A New Password', 'wampum' ),
			'redirect'	=> home_url( remove_query_arg('user') ),
		);
		$content = wampum_get_password_form( $args );
		if ( ! $content ) {
			return;
		}
        wampum_popup( $content, array( 'hidden' => false ) );
	}

	/**
	 * TODO
	 * Do membership form ?user=membership&plan_id=1234
	 *
	 * TODO - Don't forget we must confirm membership is FREE incase they change ID of query_var
	 *
	 * @return [type] [description]
	 */
	function do_membership_form() {
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
