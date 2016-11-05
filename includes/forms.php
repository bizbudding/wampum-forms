<?php

add_shortcode( 'wampum_membership_form', 'wampum_get_membership_form' );
add_shortcode( 'wampum_login_form', 'wampum_get_login_form' );
add_shortcode( 'wampum_password_form', 'wampum_get_password_form' );


function wampum_get_membership_form( $args ) {

	$args = shortcode_atts( array(
		'button'		=> __( 'Submit', 'wampum' ),
		'plan_id'		=> false,
		'redirect'		=> false,
		'title'			=> false,
		'title_wrap'	=> 'h2',
	), $args, 'wampum_membership_form' );

	// Bail if no plan ID
	// if ( $args['plan_id'] ) {
	// 	return;
	// }

	ob_start();
	?>
	<div class="wampum-form">
		<form id="wampum_user_membership_form" name="wampum_user_membership_form" method="post">

			<?php
			// Maybe display a title
			echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
			?>

			<p class="membership-name">
				<label for="wampum_membership_name"><?php _e( 'Name', 'wampum' ); ?></label>
				<input type="text" name="wampum_membership_name" id="wampum_membership_name" class="input" value="" required>
			</p>

			<p class="membership-email">
				<label for="wampum_membership_email"><?php _e( 'Email', 'wampum' ); ?></label>
				<input type="email" name="wampum_membership_email" id="wampum_membership_email" class="input" value="" required>
			</p>

			<p class="membership-submit">
				<input type="submit" name="wampum_submit" id="wampum_submit" class="button" value="<?php echo $args['button']; ?>">
				<input type="hidden" name="redirect_to" value="<?php echo $args['redirect']; ?>">
			</p>

		</form>
	</div>
	<?php
	return ob_get_clean();
}


function wampum_get_login_form( $args ) {

	// Bail if already logged in
	if ( is_user_logged_in() ) {
		return;
	}

	$args = shortcode_atts( array(
		'remember'       => true,
		'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'form_id'        => 'wampum_user_login_form',
		'id_username'    => 'wampum_user_login',
		'id_password'    => 'wampum_user_pass',
		'id_remember'    => 'wampum_rememberme',
		'id_submit'      => 'wampum_submit',
		'label_username' => __( 'Username', 'wampum' ),
		'label_password' => __( 'Password', 'wampum' ),
		'label_remember' => __( 'Remember Me', 'wampum' ),
		'label_log_in'   => __( 'Log In', 'wampum' ),
		'value_username' => '',
		'value_remember' => true,
	), $args, 'wampum_membership_form' );

	// Force return of wp_login_form() function
	$args['echo'] = false;

	return sprintf( '<div class="wampum-form">%s</div>', wp_login_form($args) );
}


function wampum_get_password_form( $args ) {

	$args = shortcode_atts( array(
		'button' 	=> __( 'Submit', 'wampum' ),
		'plan_id' 	=> false,
		'redirect' 	=> false,
	), $args, 'wampum_membership_form' );

	// Bail if no plan ID
	if ( $args['plan_id'] ) {
		return;
	}

	ob_start();
	?>
	<div class="wampum-form">
		<form id="wampum_user_password_form" name="wampum_user_password_form" method="post">

			<p class="password">
				<label for="wampum_user_password"><?php _e( 'Password', 'wampum' ); ?></label>
				<input type="password" name="log" id="wampum_user_password" class="input" value="" required>
			</p>

			<p class="password-confirm">
				<label for="wampum_user_password_confirm"><?php _e( 'Confirm Password', 'wampum' ); ?></label>
				<input type="password" name="wampum_user_password_confirm" id="wampum_user_password_confirm" class="input" value="" required>
			</p>

			<p>
				<meter max="4" id="password-strength-meter">
					<span></span>
					<span id="password-strength-text"></span>
				</meter>
			</p>

			<p class="password-submit">
				<input type="submit" name="wampum_submit" id="wampum_submit" class="button" value="<?php _e( 'Save Password', 'wampum' ); ?>">
				<input type="hidden" name="wampum_user_id" id="wampum_user_id" value="<?php echo get_current_user_id(); ?>">
				<input type="hidden" name="redirect_to" value="<?php echo home_url( remove_query_arg('user') ); ?>">
			</p>

		</form>
	</div>
	<?php
	return ob_get_clean();
}
