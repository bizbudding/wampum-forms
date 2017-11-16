<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Wampum_Form Builder Class.
 *
 * @since 1.1.0
 */
class Wampum_Form {

	/**
	 * Stores all form HTML.
	 * The main property to output the form.
	 */
	public $form = '';

	// The form arguments
	protected $settings = array();

	// Stores all form inputs
	protected $fields = array();

	// Stores hidden fields to be included in submit button wrap
	protected $hidden_fields = '';

	// Whether the form has fields
	protected $has_fields = false;

	// protected $inline = false;

	// Does this form have a submit value?
	protected $has_submit = false;

	/**
	 * Start up
	 *
	 * @since   1.1.0
	 *
	 * @return  void
	 */
	function __construct() {
		$this->settings = array(
			'hidden' => false,
			'inline' => false,
		);
	}

	/**
	 * Render the full form HTML
	 *
	 * @since   1.1.0
	 *
	 * @param  array   $args  Form args from shortcode or helper function/method
	 * @param  boolean $echo  Whether to echo or return
	 *
	 * @return string|HTML
	 */
	function render( $args, $echo = true ) {

		// Bail if the form has no fields
		if ( ! $this->has_fields ) {
			return;
		}

		$output = $this->form;

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

	/**
	 * Set a specific form settings.
	 *
	 * Make and inline form:
	 * $form = new Wampum_Form();
	 * $form->set( 'inline', true );
	 *
	 * @since   1.1.0
	 *
	 * @param string  $property  The argument to add/edit.
	 * @param mixed   $value     The value of the property
	 *
	 * @return  void
	 */
	function set( $property, $value ) {
		$this->settings[$property] = $value;
	}

	/**
	 * Build the opening form HTML
	 *
	 * @since   1.1.0
	 *
	 * @param   array   $atts  	 The form attributes
	 *
	 * @return  string  The form opening HTML
	 */
	function open( $atts, $args ) {

		// Default form attributes
		$defaults = array(
			'style'   => '',
			'action'  => '',
			'method'  => 'post',
			'enctype' => '',
			'class'   => '',
			'id'      => '',
		);
		$atts = wp_parse_args( $atts, $defaults );

		// If hidden, add inline style
		if ( filter_var( $this->settings['hidden'], FILTER_VALIDATE_BOOLEAN ) ) {
			$atts['style'] .= 'display:none;';
		}

		// Opening form HTML
		$this->form .= sprintf( '<form %s>', wampum_attr( $atts ) );

		// Form title/description
		$this->form .= $args['title'] ? sprintf( '<%s class="wampum-form-heading">%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
		$this->form .= $args['desc'] ? sprintf( '<p class="wampum-form-desc">%s</p>', $args['desc'] ) : '';

		// Add empty notice div before row, so it displays above all columns if inline
		$this->form .= '<div style="display:none;" class="wampum-notice"></div>';

		// Maybe add Flexington row classes

		if ( filter_var( $this->settings['inline'], FILTER_VALIDATE_BOOLEAN ) ) {
			$this->form .= '<div class="row gutter-10 bottom-xs">';
		}

	}

	/**
	 * Build the closing form HTML
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The form closing HTML
	 */
	function close() {
		if ( $this->settings['inline'] ) {
			$this->form .= '</div>';
		}
		$this->form .= '</form>';
	}

	/**
	 * Add an input field to the form for outputting later
	 *
	 * $form->add_field( 'text', array(
	 *      'name'        => 'wampum_field_name',
	 *      'id'          => '',
	 *      'class'       => 'wampum-field',
	 *      'style'       => '',
	 *      'placeholder' => false,
	 *      'autofocus'   => false,
	 *      'checked'     => false,
	 *      'required'    => false,
	 *      'selected'    => false,
	 * ), array(
	 *      'label' => __( 'Field Label', 'wampum' ),
	 *      'value' => '',
	 * ) );
	 *
	 * @since   1.1.0
	 *
	 * @param   string  $type  The input type (required)
	 * @param   array   $atts  The input attributes ('name' is required)
	 * @param   array   $args  Array of args to customize field
	 *
	 * @return  string  The field HTML
	 */
	function add_field( $type, $atts, $args = array() ) {

		// Bail if not a valid field type
		if ( ! in_array( $type, $this->get_available_field_types() ) ) {
			return;
		}

		// Parse attributes
		$defaults = array(
			'name'        => '',
			'id'          => '',
			'class'       => '',
			'style'       => '',
			'placeholder' => false, // false or string
			'autofocus'   => false, // bool
			'checked'     => false, // bool
			'required'    => false, // bool
			'selected'    => false, // bool
		);
		$atts = wp_parse_args( $atts, $defaults );

		// Bail if no field name
		if ( empty( $atts['name'] ) ) {
			return;
		}

		// Add wampum_ prefix to name
		// $atts['name'] = 'wampum_' . $atts['name'];

		// Parse args
		$defaults = array(
			'label'	=> '',
			'value'	=> '',
		);
		$args = wp_parse_args( $args, $defaults );

		// TODO: Sanitize the above arrays?

		// Set property to true if it's not already
		if ( ! $this->has_fields ) {
			$this->has_fields = true;
		}

		// Add field to the form markup
		$this->form .= $this->get_field_html( $type, $atts, $args );

	}

	/**
	 * Return all available field types thus far.
	 * KISS.
	 *
	 * @since   1.1.0
	 *
	 * @return  array  Available field types.
	 */
	function get_available_field_types() {
		return array(
			'checkbox',
			'email',
			'hidden',
			'password',
			'password_strength',
			'text',
			'url', // For spam.
			'submit',
		);
	}

	/**
	 * Get the field HTML, including wrap.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_html( $type, $atts, $args ) {

		switch ( $type ) {
			case 'checkbox':
				$field = $this->get_field_checkbox( $atts, $args );
			break;
			case 'email':
				$field = $this->get_field_email( $atts, $args );
			break;
			case 'hidden':
				$field = $this->get_field_hidden( $atts, $args );
			break;
			case 'password':
				$field = $this->get_field_password( $atts, $args );
			break;
			case 'password_strength':
				$field = $this->get_field_password_strength( $atts, $args );
			break;
			case 'text':
				$field = $this->get_field_text( $atts, $args );
			break;
			case 'url':
				$field = $this->get_field_url( $atts, $args );
			break;
			case 'submit':
				$field = $this->get_field_submit( $atts, $args );
			break;
			default:
				$field = '';
			break;
		}
		// Bail if no value
		if ( empty( $field ) ) {
			return;
		}
		// Bail if hidden field as these get added in submit field wrap
		if ( 'hidden' === $type ) {
			return;
		}

		return $this->get_field_open( $type, $atts, $args ) . $field . $this->get_field_close( $type, $atts, $args );
	}

	/**
	 * Get the field opening HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field opening HTML
	 */
	function get_field_open( $type, $atts, $args ) {
		// New atts array so atts meant for the field itself don't get applied to the wrap
		$new_atts = array();
		$classes  = '';
		// If we have inline styles
		if ( ! empty( $atts['style'] ) ) {
			$new_atts['style'] = $atts['style'];
		}
		/**
		 * Add classes with wampum-field default class.
		 * Trim incase we don't have additional classes
		 */
		$new_atts['class'] = trim( 'wampum-field ' . $atts['class'] );
		// If form is inline
		if ( $this->settings['inline'] ) {
			$sm = 'col-sm';
			// Auto width for inline submit button
			if ( 'submit' == $type ) {
				$sm = 'col-sm-auto';
			}
			// Add Flexington classes
			$new_atts['class'] = $new_atts['class'] . ' col col-xs-12 ' . $sm;
		}
		return sprintf( '<p %s>', wampum_attr( $new_atts ) );
	}

	/**
	 * Get the field closing HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field closing HTML
	 */
	function get_field_close( $type, $atts, $args ) {
		return '</p>';
	}

	/**
	 * Get checkbox field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_checkbox( $atts, $args ) {
		$atts['type'] = 'checkbox';
		return $this->get_field_input( $atts, $args ) . $this->get_field_label( $atts, $args );
	}

	/**
	 * Get email field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_email( $atts, $args ) {
		$atts['type'] = 'email';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	/**
	 * Get hidden field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_hidden( $atts, $args ) {
		$atts['type'] = 'hidden';
		$this->hidden_fields .= $this->get_field_input( $atts, $args );
	}

	/**
	 * Get password field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_password( $atts, $args ) {
		$atts['type'] = 'password';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	/**
	 * Get password strength field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_password_strength( $atts, $args ) {
		$field = '';
		$field .= '<span class="password-strength-meter" data-strength="">';
			$field .= '<span class="password-strength-color">';
				$field .= sprintf( '<span class="password-strength-text">%s</span>', $args['label'] );
			$field .= '</span>';
		$field .= '</span>';
		return $field;
	}

	/**
	 * Get text field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_text( $atts, $args ) {
		$atts['type'] = 'text';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	/**
	 * Get url field HTML.
	 *
	 * @since   1.3.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_url( $atts, $args ) {
		$atts['type'] = 'url';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	/**
	 * Get submit button/field HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field HTML
	 */
	function get_field_submit( $atts, $args ) {
		$atts['type'] = 'submit';
		// Return hidden fields plus submit button
		return $this->hidden_fields . sprintf( '<button %s>%s</button>', wampum_attr( $atts ), $args['label'] );
	}

	/**
	 * Get a field label.
	 * $atts['name'] is required in add_field,
	 * so we don't need to check.
	 *
	 * @param   array   $atts  Field atts.
	 *
	 * @return  string  Label HTML.
	 */
	function get_field_label( $atts, $args ) {
		$label = '';
		if ( ! empty( $args['label'] ) ) {
			$label = $args['label'];
			if ( $atts['required'] ) {
				$label = $label . '<span class="required">*</span>';
			}
			$label = sprintf( '<label for="%s">%s</label>', $atts['name'], $label );
		}
		return $label;
	}

	/**
	 * Get field input HTML
	 *
	 * @since   1.1.0
	 *
	 * @return  string  The field input HTML
	 */
	function get_field_input( $atts, $args ) {
		return sprintf( '<input %s>', wampum_attr( $atts ) );
	}

}
