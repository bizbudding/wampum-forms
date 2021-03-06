<?php
/**
 * @package   Wampum_Forms_Setup
 * @author    BizBudding, INC <mike@bizbudding.com>
 * @license   GPL-2.0+
 * @link      http://bizbudding.com.com
 * @copyright 2016 BizBudding, INC
 *
 * @wordpress-plugin
 * Plugin Name:        Wampum - Forms
 * Description:        Create login, password, and free membership (w/ user registration) forms that use the WP-API form processing
 * Plugin URI:         https://github.com/bizbudding/wampum-forms
 * Author:             Mike Hemberger
 * Author URI:         https://bizbudding.com
 * Text Domain:        wampum
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Version:            1.4.0
 *
 * GitHub Plugin URI:  https://github.com/bizbudding/wampum-forms
 * GitHub Branch:      master
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Wampum_Forms_Setup' ) ) :

/**
 * Main Wampum_Forms_Setup Class.
 *
 * @since 1.0.0
 */
final class Wampum_Forms_Setup {

	/**
	 * Singleton
	 * @var   Wampum_Forms_Setup The one true Wampum_Forms_Setup
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Wampum Forms Object
	 *
	 * @since 1.0.0
	 *
	 * @var object | Wampum_Forms
	 */
	public $forms;

	/**
	 * Wampum Submissions Object
	 *
	 * @since 1.0.0
	 *
	 * @var object | Wampum_Forms_Submissions
	 */
	public $submissions;

	/**
	 * Main Wampum_Forms_Setup Instance.
	 *
	 * Insures that only one instance of Wampum_Forms_Setup exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   1.0.0
	 * @static  var array $instance
	 * @uses    Wampum_Forms_Setup::setup_constants() Setup the constants needed.
	 * @uses    Wampum_Forms_Setup::includes() Include the required files.
	 * @uses    Wampum_Forms_Setup::load_textdomain() load the language files.
	 * @see     Wampum_Forms_Setup()
	 * @return  object | Wampum_Forms_Setup The one true Wampum_Forms_Setup
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new Wampum_Forms_Setup;
			// Methods
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
			// Instantiate Classes
			self::$instance->forms       = Wampum_Forms::instance();
			self::$instance->submissions = Wampum_Forms_Submissions::instance();
			self::$instance->settings    = Wampum_Form_Settings::instance();
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
	 * @since  1.0.0
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'WAMPUM_FORMS_VERSION' ) ) {
			define( 'WAMPUM_FORMS_VERSION', '1.4.0' );
		}
		// Plugin Folder Path.
		if ( ! defined( 'WAMPUM_FORMS_PLUGIN_DIR' ) ) {
			define( 'WAMPUM_FORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}
		// Plugin Includes Path
		if ( ! defined( 'WAMPUM_FORMS_INCLUDES_DIR' ) ) {
			define( 'WAMPUM_FORMS_INCLUDES_DIR', WAMPUM_FORMS_PLUGIN_DIR . 'includes/' );
		}
		// Plugin Folder URL.
		if ( ! defined( 'WAMPUM_FORMS_PLUGIN_URL' ) ) {
			define( 'WAMPUM_FORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
		// Plugin Root File.
		if ( ! defined( 'WAMPUM_FORMS_PLUGIN_FILE' ) ) {
			define( 'WAMPUM_FORMS_PLUGIN_FILE', __FILE__ );
		}
		// Plugin Base Name
		if ( ! defined( 'WAMPUM_FORMS_BASENAME' ) ) {
			define( 'WAMPUM_FORMS_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		foreach ( glob( WAMPUM_FORMS_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   1.4.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'admin_init', [ $this, 'updater' ] );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @uses    https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return  void
	 */
	public function updater() {

		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/bizbudding/wampum-forms/', __FILE__, 'wampum-forms' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}
	}
}
endif; // End if class_exists check.

/**
 * The main function for that returns Wampum_Forms_Setup
 *
 * The main function responsible for returning the one true Wampum_Forms_Setup
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $wampum_forms = Wampum_Forms(); ?>
 *
 * @since 1.0.0
 *
 * @return object|Wampum_Forms_Setup The one true Wampum_Forms_Setup Instance.
 */
function Wampum_Forms() {
	return Wampum_Forms_Setup::instance();
}

// Get Wampum_Forms_Setup Running.
Wampum_Forms();
