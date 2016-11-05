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
 * Plugin URI:         TBD
 * Author:             Mike Hemberger
 * Author URI:         https://bizbudding.com
 * Text Domain:        wampum
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Version:            1.0.0
 * GitHub Plugin URI:  TBD
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

		// Do the things
		add_action( 'rest_api_init', 	  array( $this, 'register_rest_endpoint' ) );

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_stylesheets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		add_action( 'wp_footer', array( $this, 'maybe_do_user_forms' ) );

	}

	function activate() {
	}
	/**
	 * Deactivates the plugin if Genesis isn't running
	 *
	 * @since 1.0.0
	 */
	function deactivate() {
	}

	/**
	 * Register rest endpoint
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	function register_rest_endpoint() {

	    register_rest_route( 'wampum/v1', '/login/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'login' ),
	    ));

	}

	/**
	 * Login a user
	 *
	 * @since   1.0.0
	 *
	 * @param 	array  $data  Associative array of data to process
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
	 * Register stylesheets for later use
	 *
	 * Use via wp_enqueue_style('wampum-user-forms'); in a template
	 *
	 * @since  1.0.0
	 *
	 * @return null
	 */
	function register_stylesheets() {
	    wp_register_style( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'css/slim-user-shortcodes.css', array(), WAMPUM_USER_FORMS_VERSION );
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
		// Login
        wp_register_script( 'wampum-user-login', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-login.js', array('jquery'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-login', 'wampum_user_login', array(
			'root'		=> esc_url_raw( rest_url() ),
			'nonce'		=> wp_create_nonce( 'wp_rest' ),
			'failure'	=> __( 'Something went wrong, please try again.', 'wampum' ),
        ) );

        // Password
        wp_register_script( 'wampum-zxcvbn', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/zxcvbn.js', array('jquery'), '4.4.1', true );
        wp_register_script( 'wampum-user-password', WAMPUM_USER_FORMS_PLUGIN_URL . 'js/wampum-user-password.js', array('wampum-zxcvbn'), WAMPUM_USER_FORMS_VERSION, true );
        wp_localize_script( 'wampum-user-password', 'wampum_user_password', array(
			'root'				=> esc_url_raw( rest_url() ),
			'nonce'				=> wp_create_nonce( 'wp_rest' ),
			'current_user_id'	=> get_current_user_id(),
			'mismatch'			=> __( 'Passwords do not match', 'wampum' ),
			'failure'			=> __( 'Something went wrong, please try again.', 'wampum' ),
        ) );
	}

	function maybe_do_user_forms() {
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

    	wp_enqueue_script('wampum-user-login');

		$content = '';
		$content .= '<h4>' . __( 'Login', 'wampum' ) . '</h4>';
		$content .= wampum_get_login_form( $this->get_login_form_args() );
		wampum_popup( $content, array( 'hidden' => false ) );
	}

	function do_password_form() {

    	wp_enqueue_script('wampum-zxcvbn');
    	wp_enqueue_script('wampum-user-password');

		$content = '';
		$content .= '<h4>' . __( 'Set A New Password', 'wampum' ) . '</h4>';
		$content .= wampum_get_password_form( $this->get_password_form_args() );
        // Do popup
        wampum_popup( $content, array( 'hidden' => false ) );
	}

	function get_login_form_args() {
		return array(
			'redirect' => home_url( remove_query_arg('user') ),
		);
	}

	function get_password_form_args() {
		return array(
			'redirect' => home_url( remove_query_arg('user') ),
		);
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
