<?php

class WampumForm {

	/**
	 * Stores all form HTML.
	 * The main property to output the form.
	 */
	public $form = '';

	// The form arguments
	protected $settings = array();

	// Stores all form inputs
	protected $fields = array();

	protected $has_fields = false;

	protected $has_password_confirm = false;

	// protected $inline = false;

	// Does this form have a submit value?
	protected $has_submit = false;

	function __construct() {
		$this->settings = array(
			'hidden' => false,
			'inline' => false,
		);
	}

	function has_password_confirm() {
		return $this->has_password_confirm;
	}

	function render( $echo = true ) {

		// trace( 'render' );
		trace( $this->form );

		$output = '';

		$form_fields = $this->fields;

		// Bail if no form fields
		if ( empty( $form_fields ) ) {
			return $output;
		}

		// $output .= $this->open();
		$output .= $this->form;
		// $output .= $this->close();

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
	 * $form = new WampumForm();
	 * $form->set( 'inline', true );
	 *
	 * @param string  $property  The argument to add/edit.
	 * @param mixed   $value     The value of the property
	 */
	function set( $property, $value ) {
		$this->settings[$property] = $value;
	}

	/**
	 * Build the opening form HTML
	 *
	 * @param   array   $atts  	 The form attributes
	 *
	 * @return  string  The form opening HTML
	 */
	function open( $atts ) {

		// Default form attributes
		$defaults = array(
			'action'	=> '',
			'method'	=> 'post',
			'enctype'	=> '',
			'class'		=> '',
			'id'		=> '',
		);
		$atts = wp_parse_args( $atts, $defaults );

		// Opening form HTML
		$this->form .= sprintf( '<form %s>', wampum_attr( $atts ) );

		// Maybe add Flexington row classes
		if ( $this->settings['inline'] ) {
			$this->form .= '<div class="row gutter-10 bottom-xs">';
		}

	}

	/**
	 * Build the closing form HTML
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
	 * 		'name'			=> 'wampum_field_name',
	 * 		'id'			=> '',
	 * 		'class'			=> 'wampum-field',
	 * 		'placeholder'	=> false,
	 * 		'autofocus'		=> false,
	 * 		'checked'		=> false,
	 * 		'required'		=> false,
	 * 		'selected'		=> false,
	 * 	), array(
	 * 		'label'	=> __( 'Field Label', 'wampum' ),
	 * 		'value'	=> '',
	 * 	) );
	 *
	 * @param  string  $type  The input type (required)
	 * @param  array   $atts  The input attributes ('name' is required)
	 * @param  array   $args  Array of args to customize field
	 */
	function add_field( $type, $atts, $args = array() ) {

		// Bail if not a valid field type
		if ( ! in_array( $type, $this->get_available_field_types() ) ) {
			return;
		}

		// Parse attributes
		$defaults = array(
			'name'			=> '',
			'id'			=> '',
			'class'			=> '',
			'placeholder'	=> false, // false or string
			'autofocus'		=> false, // bool
			'checked'		=> false, // bool
			'required'		=> false, // bool
			'selected'		=> false, // bool
		);
		$atts = wp_parse_args( $atts, $defaults );

		// Bail if no field name
		if ( empty( $atts['name'] ) ) {
			return;
		}

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

		// trace( 'test' );

		// Add field to the form markup
		$this->form .= $this->get_field_html( $type, $atts, $args );

	}

	/**
	 * Return all available field types thus far.
	 * KISS.
	 *
	 * @return  array  Available field types.
	 */
	function get_available_field_types() {
		return array(
			'checkbox',
			'hidden',
			'password',
			'text',
			'submit',
		);
	}

	function get_field_html( $type, $atts, $args ) {
        // trace( 'test' );
        switch ( $type ) {
            case 'checkbox':
                $field = $this->get_field_checkbox( $atts, $args );
                break;
            case 'hidden':
                $field = $this->get_field_hidden( $atts, $args );
                break;
            case 'password':
                $field = $this->get_field_password( $atts, $args );
                break;
            case 'text':
                $field = $this->get_field_text( $atts, $args );
                break;
            case 'submit':
                $field = $this->get_field_button( $atts, $args );
                break;
            default:
                $field = '';
                break;
        }
        // Bail if no value
        if ( empty( $field ) ) {
        	return;
        }
        // trace( $field );
        return $this->get_field_open( $atts, $args ) . $field . $this->get_field_close( $atts, $args );
	}

	function get_field_open( $atts, $args ) {
		$atts['class'] = trim( 'wampum-field ' . $atts['class'] );
		if ( $this->settings['inline'] ) {
			$atts['class'] = $atts['class'] . ' col col-xs';
		}
		return sprintf( '<p %s>', wampum_attr( $atts ) );
	}

	function get_field_close( $atts, $args ) {
		return '</p>';
	}

	function get_field_checkbox( $atts, $args ) {
		$atts['type'] = 'checkbox';
		return $this->get_field_input( $atts, $args ) . $this->get_field_label( $atts, $args );
	}

	function get_field_hidden( $atts, $args ) {
		$atts['type'] = 'hidden';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	function get_field_password( $atts, $args ) {
		$atts['type'] = 'password';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	function get_field_text( $atts, $args ) {
		$atts['type'] = 'text';
		return $this->get_field_label( $atts, $args ) . $this->get_field_input( $atts, $args );
	}

	function get_field_button( $atts, $args ) {
		$atts['type'] = 'submit';
		return sprintf( '<button %s>%s</button>', wampum_attr( $atts ), $args['label'] );
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

	function get_field_input( $atts, $args ) {
		return sprintf( '<input %s>', wampum_attr( $atts ) );
	}

}
