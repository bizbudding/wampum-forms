<?php

function sus_get_password_form() {
	$output = '';

	$output .= '<h4>Heading</h4>';

	$output .= '<form id="sus_password_form" class="sus_form" action="" method="POST">';
		$output .= '<p class="sus-password">';
			$output .= '<label for="password"><?php _e('Enter Password'); ?></label>';
			$output .= '<input name="sus_password" id="password" class="required" type="password"/>';
		$output .= '</p>';
		$output .= '<p class="sus-password-confirm">';
			$output .= '<label for="password_form"><?php _e('Confirm Password'); ?></label>';
			$output .= '<input name="sus_password_confirm" id="password_form" class="required" type="password"/>';
		$output .= '</p>';
		$output .= '<p>';
			$output .= '<input type="hidden" name="sus_register_nonce" value="<?php echo wp_create_nonce('sus-register-nonce'); ?>"/>';
			$output .= '<input type="submit" value="<?php _e('Save New Password'); ?>"/>';
		$output .= '</p>';
	$output .= '</form>';
}
