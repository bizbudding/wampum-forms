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
	// return Wampum_Forms_Builder()->forms->get_form( $args );
}

/**
 * Get a login form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_login_form( $args = array() ) {
	// return Wampum_Forms_Builder()->forms->login_form_callback( $args );
}

/**
 * Get a registration form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_register_form( $args = array() ) {
	// return Wampum_Forms_Builder()->forms->register_form_callback( $args );
}

/**
 * Get a password form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_password_form( $args = array() ) {
	// return Wampum_Forms_Builder()->forms->password_form_callback( $args );
}

/**
 * Get a subscribe form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_subscribe_form( $args = array() ) {
	// return Wampum_Forms_Builder()->forms->subscribe_form_callback( $args );
}


/**
 * Get a membership form
 *
 * @param  array   $args	 Args to configure form
 *
 * @return string  The form
 */
function wampum_get_membership_form( $args = array() ) {
	// return Wampum_Forms_Builder()->forms->membership_form_callback( $args );
}

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
	 * Wampum Rest API Object
	 *
	 * @since 1.0.0
	 *
	 * @var object | Wampum_Forms_Rest_API
	 */
	public $rest_api;

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
			// Instantiate Classes
			self::$instance->forms 	  = Wampum_Forms::instance();
			self::$instance->rest_api = Wampum_Forms_Rest_API::instance();
			self::$instance->settings = Wampum_Form_Settings::instance();
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
			define( 'WAMPUM_FORMS_VERSION', '1.0.0' );
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
		require_once WAMPUM_FORMS_INCLUDES_DIR . 'class-form.php';
		require_once WAMPUM_FORMS_INCLUDES_DIR . 'class-forms.php';
		require_once WAMPUM_FORMS_INCLUDES_DIR . 'class-rest-api.php';
		require_once WAMPUM_FORMS_INCLUDES_DIR . 'class-settings.php';
		require_once WAMPUM_FORMS_INCLUDES_DIR . 'helpers.php';
    	// Vendor
    	require_once WAMPUM_FORMS_INCLUDES_DIR . 'vendor/activecampaign-api-php/includes/ActiveCampaign.class.php';

	}

	/******************
	 *
	 * EVERYTHING BELOW HERE SHOULD GET DELETED AFTER TESTING
	 *
	 * ****************/

	/**
	 * Get a login form
	 * Increment the internal counter
	 * Enqueue scripts
	 *
	 * @since  1.0.0
	 *
	 * @return string  the form
	 */
	function get_login_form_og( $args ) {

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
	function get_register_form_og( $args ) {

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

	function get_subscribe_form_og( $args ) {
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
	function get_password_form_og( $args ) {

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
	function get_membership_form_og( $args ) {

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
