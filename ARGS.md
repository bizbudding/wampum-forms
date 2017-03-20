// Shortcode atts

'type'					=> '',
'hidden'				=> false,
'inline'				=> false,
'title'					=> '',
'title_wrap'			=> 'h3',
'desc'					=> '',
'first_name'			=> false,
'last_name'				=> false,
'email'					=> false,
'username'				=> false,
'password'				=> false,
'password_confirm'		=> false,
'password_strength'		=> false,
'require_first_name'	=> false,
'require_last_name'		=> false,
'require_email'			=> true,
'require_username'		=> false,
// 'require_password'		=> false,
'label_email'			=> __( 'Email', 'wampum' ),
'value_email'			=> '',
'readonly_email'		=> false,
'button'				=> __( 'Submit', 'wampum' ),
'notifications'			=> '',
'redirect'				=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // a url or null
'ac_list_ids'			=> '',
'ac_tags'				=> '',
// Login-specific form params
'label_username'		=> '',
'value_username'		=> '',
'remember'				=> true,
'value_remember'		=> true,
// Register-specific form params
'log_in'				=> false,
// Membership-specific form params
'plan_id'				=> '',
'member_message'		=> '',

$form->add_field( 'text', array(
	'name'			=> '',
	'id'			=> '',
	'class'			=> '',
	'placeholder'	=> false, // false or string
	'autofocus'		=> false, // bool
	'checked'		=> false, // bool
	'required'		=> false, // bool
	'selected'		=> false, // bool
), array(
	'label'	=> '',
	'value'	=> '',
) );


// Field atts

'name'			=> '',
'id'			=> '',
'class'			=> '',
'placeholder'	=> false, // false or string
'autofocus'		=> false, // bool
'checked'		=> false, // bool
'required'		=> false, // bool
'selected'		=> false, // bool


// Field args

'label'	=> '',
'value'	=> '',



