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

	public function setup() {
		register_activation_hook( __FILE__,   array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_stylesheets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		// Add new load point for ACF json field groups
		add_filter( 'acf/settings/load_json', array( $this, 'acf_json_load_point' ) );
		// Custom 'none' location for field groups
		add_filter( 'acf/location/rule_types', 								array( $this, 'acf_none_rule_type' ) );
		add_filter( 'acf/location/rule_values/none', 						array( $this, 'acf_none_location_rules_values' ) );
		// Don't save form values
		add_filter('acf/pre_save_post' , array( $this, 'remove_form_values' ), 10, 1 );

		// Validate username
		add_action( 'acf/validate_value/name=wampum_login_username', array( $this, 'validate_login' ), 10, 4 );
		// Validate password reset
		add_action( 'acf/validate_value/name=wampum_user_password', array( $this, 'validate_password' ), 10, 4 );

		add_action( 'get_header', array( $this, 'maybe_do_user_forms' ) );

	}

	public function activate() {
	}
	/**
	 * Deactivates the plugin if Genesis isn't running
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
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
	public function register_stylesheets() {
	    wp_register_style( 'wampum-user-forms', WAMPUM_USER_FORMS_PLUGIN_URL . 'css/slim-user-shortcodes.css', array(), WAMPUM_USER_FORMS_VERSION );
	}

	/**
	 * Register scripts for later use
	 *
	 * Use via wp_enqueue_script('magnific-popup'); in a template
	 *
	 * @since  1.0.0
	 *
	 * @return null
	 */
	public function register_scripts() {
	}

	/**
	 * Add the new load point for ACF JSON files in the plugin
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function acf_json_load_point( $paths ) {
	    $paths[] = WAMPUM_USER_FORMS_INCLUDES_DIR . 'acf-json';
	    return $paths;
	}

	/**
	 * ACF custom location rule for 'none'
	 * Allows a field group to be created solely for use elsewhere (via acf_form() )
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function acf_none_rule_type( $choices ) {
	    $choices['None']['none'] = 'None';
	    return $choices;
	}

	public function acf_none_location_rules_values( $choices ) {
		return array(
			'none' => 'None',
		);
	}

	public function remove_form_values( $post_id ) {

		$forms = array(
			'wampum_user_login',
			'wampum_user_password',
		);

		if ( ! in_array( $post_id, $forms ) ) {
	        return $post_id;
	    }
	    // No ID to save to, woot!
	    return '';
	}

	public function validate_login( $valid, $value, $field, $input ) {
		if ( ! $valid ) {
			return $valid;
		}
		$user		= get_user_by( 'login', $value );
		$password	= $_POST['acf']['field_581951bde7d77'];

		// If no user exists
		if ( ! $user ) {
			$valid = '<strong>' . __( 'ERROR', 'wampum' ) . '</strong>: ' . __( 'Invalid username', 'wampum' ) . '<a href="' . wp_lostpassword_url($clean_url) . '">' . __( 'Lost your password?', 'wampum' ) . '</a>';
		}
		// If user exists but password is wrong
		elseif ( $user && ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			$valid = '<strong>' . __( 'ERROR', 'wampum' ) . '</strong>: ' . __( 'The password you entered for the username', 'wampum' ) . ' <strong>jivedig</strong> ' . __( 'is incorrect', 'wampum' ) . '<a href="' . wp_lostpassword_url($clean_url) . '">' . __( 'Lost your password?', 'wampum' ) . '</a>';
		}

		// global $wp;
		elseif ( $user && wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			wp_setcookie( $value, $password, true);
			wp_set_current_user( $user->ID, $value );
			do_action( 'wp_login', $value );
			// wp_redirect( home_url(remove_query_arg( 'user' ) ) ); exit;
		}
		// wp_redirect( remove_query_arg( 'user' ) ); exit;

		// Still need to return to force redirect set in acf_form()
		return $valid;
	}

	public function validate_password( $valid, $value, $field, $input ) {
		// Must use field key
		$password_confirm = 'field_58194312da6ff';
		// Check for password match
		if ( $value != $_POST['acf'][$password_confirm]) {
			$valid = 'Passwords do not match';
		}
		return $valid;
	}

	public function maybe_do_user_forms() {
	    // Bail if no user parameter set
	    if ( ! isset($_GET['user']) ) {
	        return;
	    }
	    $vars = array(
	    	'login',
	    	'password'
    	);
	    // Login form
	    if ( 'login' == $_GET['user'] && ! is_user_logged_in() ) {
            // ACF required
            acf_form_head();
            // Do the form
			add_action( 'wampum_popups', array( $this, 'do_login_form' ) );
        }
	    // Password form
	    elseif ( 'password' == $_GET['user'] && is_user_logged_in() ) {
            // ACF required
            acf_form_head();
			// Do the form
	    	add_action( 'wampum_popups', array( $this, 'do_password_form' ) );
	    }
	}

	public function do_login_form() {
		ob_start();
		$args = array(
			'field_groups'	=> array(39272),
			'post_id'		=> 'wampum_user_login',
			'return'		=> home_url(remove_query_arg('user')),
		);
		acf_form($args);
		$form = ob_get_contents();

		$content = '';
		$content .= '<h4>Login</h4>';
		$content .= $form;
        // Do popup
        wampum_popup( $content, array( 'hidden' => false ) );
	}

	public function do_password_form() {
		ob_start();
		$args = array(
			'field_groups'	=> array(39269),
			'post_id'		=> 'wampum_user_password',
			'return'		=> home_url(remove_query_arg('user')),
			// 'honeypot'		=> true,
		);
		acf_form($args);
		$form = ob_get_contents();

		$content = '';
		$content .= '<h4>Password</h4>';
		$content .= '<p>Set a new password for your account</p>';
		$content .= $form;
		// Do popup
        wampum_popup( $content, array( 'hidden' => false ) );
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
