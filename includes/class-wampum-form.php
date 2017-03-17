<?php

class WampumForm {

	// Stores all form HTML
	protected $form = '';

	// The form arguments
	protected $settings = array();

	// Stores all form inputs
	protected $fields = array();

	// protected $has_fields = false;

	// protected $inline = false;

	// Does this form have a submit value?
	protected $has_submit = false;

	function __construct() {
		$this->settings = array(
			'hidden' => false,
			'inline' => false,
		);
	}

	function render( $echo = true ) {

		$output = '';

		$form_fields = $this->fields;

		// Bail if no form fields
		if ( empty( $form_fields ) ) {
			return $output;
		}

		return $this->form;

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
	 * @param   string  $action  The form action
	 * @param   array   $atts  	 The form attributes
	 * @param   array   $args  	 The form arguments
	 *
	 * @return  string  The form opening HTML
	 */
	function open( $action = 'post', $atts = array(), $args = array() ) {

		// Default form attributes
		$defaults = array(
			'action'	=> $action,
			'method'	=> 'post',
			'enctype'	=> 'application/x-www-form-urlencoded',
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
		if ( ! $this->form_has_fields ) {
			$this->form_has_fields = true;
		}

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
		);
	}

	function get_field_html( $type, $atts, $args ) {
        switch ( $type ) {
            case 'checkbox':
                $field = $this->get_field_checkbox( $atts, $args );
                break;
            case 'hidden':
                $field = $this->get_field_input( $atts, $args );
                break;
            case 'password':
                $field = $this->get_field_input( $atts, $args );
                break;
            case 'text':
                $field = $this->get_field_input( $atts, $args );
                break;
            default:
                $field = '';
                break;
        }
        return $this->get_field_open( $atts, $args ) . $this->get_field_label( $atts ) . $field . $this->get_field_close( $atts, $args );
	}

	function get_field_open( $atts, $args ) {
		$atts['class'] = trim( 'wampum-field ' . $atts['class'] );
		if ( $this->settings['inline'] ) {
			$atts['class'] = $atts['class'] . ' col col-xs';
		}
		return sprintf( '<p %s', wampum_attr( $atts ) );
	}

	function get_field_close( $atts, $args ) {
		return '</p>';
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
	function get_field_label( $atts ) {
		$label = '';
		if ( ! empty( $atts['label'] ) ) {
			$label = $atts['label'];
			if ( $atts['required'] ) {
				$label = '<span class="required">*</span>' . $label;
			}
			$label = sprintf( '<label for="%s">%s%s</label>', $atts['name'], $atts['label'] );
		}
		return $label;
	}

	function get_field_input( $atts, $args ) {
		return sprintf( '<input %s>', wampum_attr( $atts ) );
	}

	function get_field_checkbox( $atts, $args ) {
		// TODO
		return;
	}

	/**
	 * Build the HTML for the form based on the input queue
	 *
	 * @param bool $echo Should the HTML be echoed or returned?
	 *
	 * @return string
	 */
	function build_form( $echo = true ) {

		$output = '';

		if ( $this->form['has_element'] ) {
			$output .= '<form method="' . $this->form['method'] . '"';

			if ( ! empty( $this->form['enctype'] ) ) {
				$output .= ' enctype="' . $this->form['enctype'] . '"';
			}

			if ( ! empty( $this->form['action'] ) ) {
				$output .= ' action="' . $this->form['action'] . '"';
			}

			if ( ! empty( $this->form['id'] ) ) {
				$output .= ' id="' . $this->form['id'] . '"';
			}

			if ( count( $this->form['class'] ) > 0 ) {
				$output .= $this->_output_classes( $this->form['class'] );
			}

			$output .= '>';
		}

		// Add honeypot anti-spam field
		if ( $this->form['add_honeypot'] ) {
			$this->add_input( 'Leave blank to submit', array(
				'name'             => 'honeypot',
				'slug'             => 'honeypot',
				'id'               => 'form_honeypot',
				'wrap_tag'         => 'div',
				'wrap_class'       => array( 'form_field_wrap', 'hidden' ),
				'wrap_id'          => '',
				'wrap_style'       => 'display: none',
				'request_populate' => false
			) );
		}

		// Add a WordPress nonce field
		if ( $this->form['add_nonce'] && function_exists( 'wp_create_nonce' ) ) {
			$this->add_input( 'WordPress nonce', array(
				'value'            => wp_create_nonce( $this->form['add_nonce'] ),
				'add_label'        => false,
				'type'             => 'hidden',
				'request_populate' => false
			) );
		}

		// Iterate through the input queue and add input HTML
		foreach ( $this->inputs as $val ) :

			$min_max_range = $element = $end = $attr = $field = $label_html = '';

			// Automatic population of values using $_REQUEST data
			if ( $val['request_populate'] && isset( $_REQUEST[ $val['name'] ] ) ) {

				// Can this field be populated directly?
				if ( ! in_array( $val['type'], array( 'html', 'title', 'radio', 'checkbox', 'select', 'submit' ) ) ) {
					$val['value'] = $_REQUEST[ $val['name'] ];
				}
			}

			// Automatic population for checkboxes and radios
			if (
				$val['request_populate'] &&
				( $val['type'] == 'radio' || $val['type'] == 'checkbox' ) &&
				empty( $val['options'] )
			) {
				$val['checked'] = isset( $_REQUEST[ $val['name'] ] ) ? true : $val['checked'];
			}

			switch ( $val['type'] ) {

				case 'html':
					$element = '';
					$end     = $val['label'];
					break;

				case 'title':
					$element = '';
					$end     = '
					<h3>' . $val['label'] . '</h3>';
					break;

				case 'textarea':
					$element = 'textarea';
					$end     = '>' . $val['value'] . '</textarea>';
					break;

				case 'select':
					$element = 'select';
					$end     .= '>';
					foreach ( $val['options'] as $key => $opt ) {
						$opt_insert = '';
						if (
							// Is this field set to automatically populate?
							$val['request_populate'] &&

							// Do we have $_REQUEST data to use?
							isset( $_REQUEST[ $val['name'] ] ) &&

							// Are we currently outputting the selected value?
							$_REQUEST[ $val['name'] ] === $key
						) {
							$opt_insert = ' selected';

						// Does the field have a default selected value?
						} else if ( $val['selected'] === $key ) {
							$opt_insert = ' selected';
						}
						$end .= '<option value="' . $key . '"' . $opt_insert . '>' . $opt . '</option>';
					}
					$end .= '</select>';
					break;

				case 'radio':
				case 'checkbox':

					// Special case for multiple check boxes
					if ( count( $val['options'] ) > 0 ) :
						$element = '';
						foreach ( $val['options'] as $key => $opt ) {
							$slug = $this->_make_slug( $opt );
							$end .= sprintf(
								'<input type="%s" name="%s[]" value="%s" id="%s"',
								$val['type'],
								$val['name'],
								$key,
								$slug
							);
							if (
								// Is this field set to automatically populate?
								$val['request_populate'] &&

								// Do we have $_REQUEST data to use?
								isset( $_REQUEST[ $val['name'] ] ) &&

								// Is the selected item(s) in the $_REQUEST data?
								in_array( $key, $_REQUEST[ $val['name'] ] )
							) {
								$end .= ' checked';
							}
							$end .= $this->field_close();
							$end .= ' <label for="' . $slug . '">' . $opt . '</label>';
						}
						$label_html = '<div class="checkbox_header">' . $val['label'] . '</div>';
						break;
					endif;

				// Used for all text fields (text, email, url, etc), single radios, single checkboxes, and submit
				default :
					$element = 'input';
					$end .= ' type="' . $val['type'] . '" value="' . $val['value'] . '"';
					$end .= $val['checked'] ? ' checked' : '';
					$end .= $this->field_close();
					break;

			}

			// Added a submit button, no need to auto-add one
			if ( $val['type'] === 'submit' ) {
				$this->has_submit = true;
			}

			// Special number values for range and number types
			if ( $val['type'] === 'range' || $val['type'] === 'number' ) {
				$min_max_range .= ! empty( $val['min'] ) ? ' min="' . $val['min'] . '"' : '';
				$min_max_range .= ! empty( $val['max'] ) ? ' max="' . $val['max'] . '"' : '';
				$min_max_range .= ! empty( $val['step'] ) ? ' step="' . $val['step'] . '"' : '';
			}

			// Add an ID field, if one is present
			$id = ! empty( $val['id'] ) ? ' id="' . $val['id'] . '"' : '';

			// Output classes
			$class = $this->_output_classes( $val['class'] );

			// Special HTML5 fields, if set
			$attr .= $val['autofocus'] ? ' autofocus' : '';
			$attr .= $val['checked'] ? ' checked' : '';
			$attr .= $val['required'] ? ' required' : '';

			// Build the label
			if ( ! empty( $label_html ) ) {
				$field .= $label_html;
			} elseif ( $val['add_label'] && ! in_array( $val['type'], array( 'hidden', 'submit', 'title', 'html' ) ) ) {
				if ( $val['required'] ) {
					$val['label'] .= ' <strong>*</strong>';
				}
				$field .= '<label for="' . $val['id'] . '">' . $val['label'] . '</label>';
			}

			// An $element was set in the $val['type'] switch statement above so use that
			if ( ! empty( $element ) ) {
				if ( $val['type'] === 'checkbox' ) {
					$field = '
					<' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end .
					         $field;
				} else {
					$field .= '
					<' . $element . $id . ' name="' . $val['name'] . '"' . $min_max_range . $class . $attr . $end;
				}
			// Not a form element
			} else {
				$field .= $end;
			}

			// Parse and create wrap, if needed
			if ( $val['type'] != 'hidden' && $val['type'] != 'html' ) {

				$wrap_before = $val['before_html'];
				if ( ! empty( $val['wrap_tag'] ) ) {
					$wrap_before .= '<' . $val['wrap_tag'];
					$wrap_before .= count( $val['wrap_class'] ) > 0 ? $this->_output_classes( $val['wrap_class'] ) : '';
					$wrap_before .= ! empty( $val['wrap_style'] ) ? ' style="' . $val['wrap_style'] . '"' : '';
					$wrap_before .= ! empty( $val['wrap_id'] ) ? ' id="' . $val['wrap_id'] . '"' : '';
					$wrap_before .= '>';
				}

				$wrap_after = $val['after_html'];
				if ( ! empty( $val['wrap_tag'] ) ) {
					$wrap_after = '</' . $val['wrap_tag'] . '>' . $wrap_after;
				}

				$output .= $wrap_before . $field . $wrap_after;
			} else {
				$output .= $field;
			}

		endforeach;

		// Auto-add submit button
		if ( ! $this->has_submit && $this->form['add_submit'] ) {
			$output .= '<div class="form_field_wrap"><input type="submit" value="Submit" name="submit"></div>';
		}

		// Close the form tag if one was added
		if ( $this->form['has_element'] ) {
			$output .= '</form>';
		}

		// Output or return?
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	// Easy way to auto-close fields, if necessary
	function field_close() {
		return '>';
	}

	// Validates id and class attributes
	// TODO: actually validate these things
	private function _check_valid_attr( $string ) {

		$result = true;

		// Check $name for correct characters
		// "^[a-zA-Z0-9_-]*$"

		return $result;

	}

	// Create a slug from a label name
	private function _make_slug( $string ) {

		$result = '';

		$result = str_replace( '"', '', $string );
		$result = str_replace( "'", '', $result );
		$result = str_replace( '_', '-', $result );
		$result = preg_replace( '~[\W\s]~', '-', $result );

		$result = strtolower( $result );

		return $result;

	}

	// Parses and builds the classes in multiple places
	private function _output_classes( $classes ) {

		$output = '';


		if ( is_array( $classes ) && count( $classes ) > 0 ) {
			$output .= ' class="';
			foreach ( $classes as $class ) {
				$output .= $class . ' ';
			}
			$output .= '"';
		} else if ( is_string( $classes ) ) {
			$output .= ' class="' . $classes . '"';
		}

		return $output;
	}
}