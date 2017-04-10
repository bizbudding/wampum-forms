<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Wampum_Form_Settings Class.
 *
 * @since 1.1.0
 */
class Wampum_Form_Settings {

    /**
     * Singleton
     * @var   Wampum_Form_Settings The one true Wampum_Form_Settings
     * @since 1.1.0
     */
    private static $instance;

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Main Wampum_Form_Settings Instance.
     *
     * Insures that only one instance of Wampum_Form_Settings exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since   1.1.0
     * @static  var array $instance
     * @return  object | Wampum_Form_Settings The one true Wampum_Form_Settings
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            // Setup the setup
            self::$instance = new Wampum_Form_Settings;
        }
        return self::$instance;
    }

    /**
     * Start up
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Wampum Forms',
            'manage_options',
            'wampum-forms',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     *
     * @since   1.1.0
     *
     * @return  string
     */
    public function create_admin_page() {

        // Enqueue our script on the settings page
        wp_enqueue_script( 'wampum-forms-admin-settings' );

        // Set class property
        $this->options = get_option( 'wampum_forms_ac' );
        ?>
        <div class="wrap">
            <h1>Wampum Forms Settings</h1>
            <form id="wampum-forms-settings" method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'wampum_forms' );

                do_settings_sections( 'wampum-forms' );

                submit_button();
                ?>
            </form>
            <hr>
            <h1>Instructions</h1>
            <h2>Description</h2>
            <p>Wampum forms creates login, password, register, subscribe, and free membership (w/ user registration) forms that use the WP-API form processing. With Wampum forms:</p>
            <ul>
                <li>Use simple shortcodes to create forms throughout your website</li>
                <li>Use multiple global parameters and form specific parameters to customize your forms</li>
                <li>Displays elegant error/success notices</li>
                <li>Integrate forms with ActiveCampaign - subscribe users to lists and add tags</li>
                <li>Use forms with the Wampum Popups plugin, available at https://github.com/JiveDig/wampum-popups</li>
                <li>Automatically update in the WordPress Dashboard via the GitHub Updater plugin available at https://github.com/afragen/github-updater</li>
            </ul>
            <h2>Shortcodes</h2>
            <table class="widefat">
                <thead>
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>[wampum_login_form]</td>
                        <td>Allows a logged out user to login.</td>
                    </tr>
                    <tr>
                        <td>wampum_register_form]</td>
                        <td>Allows a user to register.</td>
                    </tr>
                    <tr>
                        <td>[wampum_password_form]</td>
                        <td>Allows a logged in user to change their password.</td>
                    </tr>
                    <tr>
                        <td>[wampum_subscribe_form]</td>
                        <td>Allows a user to subscribe. Commonly used with ActiveCampaign to subscribe to a list.</td>
                    </tr>
                    <tr>
                        <td>* [wampum_membership_form]</td>
                        <td>Creates a clean and efficient onboarding flow for adding users to a WooCommerce membership.
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </tfoot>
            </table>
            <p>* Membership form requires WooCommerce and WooCommerce Memberships</p>
            <h2>Global Parameters</h2>
            <p>The following parameters are available to most forms. No parameters are required. Form specific parameters are covered in the following section</p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>hidden</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to hide the form by default (display:none; inline style)</td>
                        <td>hidden="true"</td>
                    </tr>
                    <tr>
                        <td>* inline</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Display the form fields in a row
                        </td>
                        <td>inline="false"</td>
                    </tr>
                    <tr>
                        <td>title</td>
                        <td>string</td>
                        <td>null</td>
                        <td>The form title to display</td>
                        <td>title="Form Title"</td>
                    </tr>
                    <tr>
                        <td>title_wrap</td>
                        <td>string</td>
                        <td>"h4"</td>
                        <td>The title wrap element</td>
                        <td>title_wrap="h4"</td>
                    </tr>
                    <tr>
                        <td>desc</td>
                        <td>string</td>
                        <td>null</td>
                        <td>The form description to display</td>
                        <td>desc="Form Description"</td>
                    </tr>
                    <tr>
                        <td>first_name</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show first name field</td>
                        <td>first_name="true"</td>
                    </tr>
                    <tr>
                        <td>last_name</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show last name field</td>
                        <td>last_name="false"</td>
                    </tr>
                    <tr>
                        <td>email</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show email field</td>
                        <td>email="true"</td>
                    </tr>
                    <tr>
                        <td>username</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show username field</td>
                        <td>username="false"</td>
                    </tr>
                    <tr>
                        <td>password</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show password field</td>
                        <td>password="true"</td>
                    </tr>
                    <tr>
                        <td>password_confirm</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show password confirm field</td>
                        <td>password_confirm="false"</td>
                    </tr>
                    <tr>
                        <td>password_strength</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>Whether to show password strength meter</td>
                        <td>password_strength="true"</td>
                    </tr>
                    <tr>
                        <td>first_name_label</td>
                        <td>string</td>
                        <td>"Name" or "First Name" (if last name field is used)</td>
                        <td>The label of the first name field</td>
                        <td>first_name_label="Please enter your first name"</td>
                    </tr>
                    <tr>
                        <td>last_name_label</td>
                        <td>string</td>
                        <td>"Last Name"</td>
                        <td>The label of the last name field</td>
                        <td>last_name_label="Please enter your last name"</td>
                    </tr>
                    <tr>
                        <td>email_label</td>
                        <td>string</td>
                        <td>"Email"</td>
                        <td>The label of the email field</td>
                        <td>email_label="Please enter your email address"</td>
                    </tr>
                    <tr>
                        <td>username_label</td>
                        <td>string</td>
                        <td>"Username"</td>
                        <td>The label of the username field</td>
                        <td>username_label="Please choose a username"</td>
                    </tr>
                    <tr>
                        <td>password_label</td>
                        <td>string</td>
                        <td>"Password"</td>
                        <td>The label of the password field</td>
                        <td>password_label="Please choose a secure password"</td>
                    </tr>
                    <tr>
                        <td>password_confirm_label</td>
                        <td>string</td>
                        <td>"Confirm Password"</td>
                        <td>The label of the password confirm field</td>
                        <td>password_confirm_label="Please confirm your password"</td>
                    </tr>
                    <tr>
                        <td>password_strength_label</td>
                        <td>string</td>
                        <td>"Strength"</td>
                        <td>The label of the password strength meter</td>
                        <td>password_strength_label="Your password strength is"</td>
                    </tr>
                    <tr>
                        <td>button</td>
                        <td>string</td>
                        <td>"Submit"</td>
                        <td>The button text to display</td>
                        <td>button="Join Now!"</td>
                    </tr>
                    <tr>
                        <td>notifications</td>
                        <td>string</td>
                        <td>null</td>
                        <td>Comma-separated list of emails to notify upon successful submission</td>
                        <td>notifications="joe@example.com, jane@example.com"</td>
                    </tr>
                    <tr>
                        <td>redirect</td>
                        <td>string</td>
                        <td>Current URL</td>
                        <td>URL to redirect after form submission</td>
                        <td>redirect="https://www.example.com"</td>
                    </tr>
                    <tr>
                        <td>** ac_list_ids</td>
                        <td>integer</td>
                        <td>null</td>
                        <td>Comma-separated list of ActiveCampaign list IDs to add a contact to (list must exist in ActiveCampaign)</td>
                        <td>ac_list_ids="1,2,3,4"</td>
                    </tr>
                    <tr>
                        <td>** ac_tags</td>
                        <td>integer</td>
                        <td>null</td>
                        <td>Comma-separated list of ActiveCampaign tag IDs to add a contact to</td>
                        <td>ac_tags="Tags, More Tags"</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </tfoot>
            </table>
            <p>* requires <a target="_blank" href="https://github.com/JiveDig/flexington">Flexington</a></p>
            <p>** Requires valid credentials in Settings > Wampum Forms</p>
            <h2>Form Specific Parameters</h2>
            <h3>Login Form</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>remember</td>
                        <td>true, false</td>
                        <td>true</td>
                        <td>Shows the "Remember Me" checkbox</td>
                        <td>remember="false"</td>
                    </tr>
                    <tr>
                        <td>value_remember</td>
                        <td>true, false</td>
                        <td>true</td>
                        <td>Default "Remember Me" checked or unchecked</td>
                        <td>value_remember="false"</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </tfoot>
            </table>
            <h3>Register Form</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>log_in</td>
                        <td>true, false</td>
                        <td>false</td>
                        <td>
                            <p>Whether to auto log in the user after successful registration</p>
                            <p><strong>Logged Out Users</strong></p>
                            <ul>
                                <li>&middot; If user tries to login with an existing username/email, they are asked to login first.</li>
                                <li>&middot; If they click the login link it displays the login form.</li>
                                <li>&middot; After successful login, the membership form is loaded and username/email is prefilled.</li>
                                <li>&middot; After successful submission a user account is created and (if password fields were not used) the password form is loaded.</li>
                                <li>&middot; The user must change their password (password was auto-generated) then they are redirected.</li>
                                <li><strong>Logged in users</strong></li>
                                <li>&middot; User fields are pre-filled, and username/email fields are readonly</li>
                                <li>&middot; After submission, user is redirected</li>
                            </ul>
                        </td>
                        <td>log_in="true"</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </tfoot>
            </table>
            <h3>Membership Form</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Plan_id (required)</td>
                        <td>integer</td>
                        <td>null</td>
                        <td>Membership ID that this form will add the user to</td>
                        <td>plan_id="1234"</td>
                    </tr>
                </tbody>
                <tr>
                    <td>member_message</td>
                    <td>string</td>
                    <td>null</td>
                    <td>Display a message in place of the form if a logged in user is already a member</td>
                    <td>member_message="Looks like you’re already a member."</td>
                </tr>
                <tfoot>
                    <tr>
                        <th>Parameter Name</th>
                        <th>Expected Value</th>
                        <th>Default State</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </tfoot>
            </table>
            <h2>Usage Examples</h2>
            <h3>Login Form</h3>
            <p><pre>[wampum_login_form title="Login Now!" redirect="https://bizbudding.com"]</pre></p>
            <h3>Password Form</h3>
            <p><pre>[wampum_password_form title="Set A New Password"]</pre></p>
            <h3>Membership Form</h3>
            <p><pre>[wampum_membership_form plan_id="26180" title="Join Now!" title_wrap="h3" desc="Fill out this form to get instant access." first_name=true last_name=false username=false member_message="Woot! You are already a member!" button="Join Now" notifications="mike@email.com, david@email.com" redirect="https://bizbudding.com/my-account/"]</pre></p>
        </div>
        <?php
    }

    /**
     * Register and add settings
     *
     * @since   1.1.0
     *
     * @return  void
     */
    public function page_init() {

        register_setting(
            'wampum_forms', // Option group
            'wampum_forms_ac', // Option name
            array( $this, 'sanitize_fields' ) // Sanitize
        );

        add_settings_section(
            'section_active_campaign', // ID
            'Active Campaign', // Title
            array( $this, 'section_active_campaign_callback' ), // Callback
            'wampum-forms' // Page
        );

        add_settings_field(
            'base_url', // ID
            'Base URL', // Title
            array( $this, 'base_url_callback' ), // Callback
            'wampum-forms', // Page
            'section_active_campaign' // Section
        );

        add_settings_field(
            'key',
            'Key',
            array( $this, 'key_callback' ),
            'wampum-forms',
            'section_active_campaign'
        );

        add_settings_field(
            'status',
            'Status',
            array( $this, 'status_callback' ),
            'wampum-forms',
            'section_active_campaign'
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @since   1.1.0
     *
     * @param   array $input Contains all settings fields as array keys
     *
     * @return  string
     */
    public function sanitize_fields( $input ) {

        $new_input = array();

        if ( isset( $input['base_url'] ) ) {
            $new_input['base_url'] = sanitize_text_field( $input['base_url'] );
        }

        if ( isset( $input['key'] ) ) {
            $new_input['key'] = sanitize_text_field( $input['key'] );
        }

        return $new_input;
    }

    /**
     * Print the Section text
     *
     * @since   1.1.0
     *
     * @return  string
     */
    public function section_active_campaign_callback() {
        echo 'Enter your Active Campaign info below';
    }

    /**
     * Output the base url field
     *
     * @since   1.1.0
     *
     * @return  string
     */
    public function base_url_callback() {
        printf( '<input type="text" id="base_url" class="regular-text" name="wampum_forms_ac[base_url]" value="%s" />',
            isset( $this->options['base_url'] ) ? esc_attr( $this->options['base_url']) : ''
        );
    }

    /**
     * Output the key field
     *
     * @since   1.1.0
     *
     * @return  string
     */
    public function key_callback() {
        printf( '<input type="text" id="key" class="regular-text" name="wampum_forms_ac[key]" value="%s" />',
            isset( $this->options['key'] ) ? esc_attr( $this->options['key']) : ''
        );
    }

    /**
     * Output the status value
     *
     * @since   1.1.0
     *
     * @return  string
     */
    public function status_callback() {
        $value = __( 'Add valid credentials', 'wampum' );
        if ( Wampum_Forms()->rest_api->is_active_campaign_connected() ) {
            $value = sprintf( '<span style="color:#46b450;">%s</span>', __( 'Connected') );
        }
        echo $value;
    }

}
