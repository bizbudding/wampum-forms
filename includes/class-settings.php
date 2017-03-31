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
