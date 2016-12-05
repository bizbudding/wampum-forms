<?php

add_shortcode( 'wampum-login-form', 'wampum_get_login_form' );
add_shortcode( 'wampum-password-form', 'wampum_get_password_form' );
add_shortcode( 'wampum-membership-form', 'wampum_get_membership_form' );


function wampum_get_login_form( $args ) {

	// Bail if already logged in
	if ( is_user_logged_in() ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-user-login');

	$args = shortcode_atts( array(
		'title'				=> false,
		'title_wrap'		=> 'h2',
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
	), $args, 'wampum_login_form' );

	// Force return of wp_login_form() function
	$args['echo'] = false;

	$classes = 'wampum-form';
	if ( filter_var( $args['inline'], FILTER_VALIDATE_BOOLEAN ) ) {
		$classes .= ' wampum-form-inline';
	}
	return sprintf( '<div class="%s">%s%s</div>',
		$classes,
		$args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '',
		wp_login_form($args)
	);
}


function wampum_get_password_form( $args ) {

	// Bail if not logged in
	if ( ! is_user_logged_in() ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-zxcvbn');
	wp_enqueue_script('wampum-user-password');

	$args = shortcode_atts( array(
		'title'			=> false,
		'title_wrap'	=> 'h2',
		'button'		=> __( 'Submit', 'wampum' ),
		'redirect'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'inline'		=> false,
	), $args, 'wampum_password_form' );

	$classes = 'wampum-form';
	if ( filter_var( $args['inline'], FILTER_VALIDATE_BOOLEAN ) ) {
		$classes .= ' wampum-form-inline';
	}

	ob_start();
	?>
	<div class="<?php echo $classes; ?>">
		<?php
		echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
		?>
		<form id="wampum_user_password_form" name="wampum_user_password_form" method="post">

			<p class="wampum-field password">
				<label for="wampum_user_password"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="log" id="wampum_user_password" class="input" value="" required>
			</p>

			<p class="wampum-field password-confirm">
				<label for="wampum_user_password_confirm"><?php _e( 'Confirm Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="wampum_user_password_confirm" id="wampum_user_password_confirm" class="input" value="" required>
			</p>

			<p class="wampum-field password-strength">
				<span class="password-strength-meter" data-strength="">
					<span class="password-strength-color">
						<span class="password-strength-text"></span>
					</span>
				</span>
			</p>

			<p class="wampum-field wampum-submit password-submit">
				<input type="submit" name="wampum_submit" id="wampum_submit" class="button" value="<?php _e( 'Save Password', 'wampum' ); ?>">
				<input type="hidden" name="wampum_user_id" id="wampum_user_id" value="<?php echo get_current_user_id(); ?>">
				<input type="hidden" name="redirect_to" value="<?php echo home_url( remove_query_arg('user') ); ?>">
			</p>

		</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get a form to add a user to a membership
 * Creates a new user if one doesn't exist
 *
 * @param  [type] $args [description]
 * @return [type]       [description]
 */
function wampum_get_membership_form( $args ) {

	if ( ! function_exists( 'wc_memberships' ) ) {
		return;
	}

	$args = shortcode_atts( array(
		'plan_id'			=> false, // required
		'redirect'			=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'title'				=> false,
		'title_wrap'		=> 'h2',
		'first_name'		=> true,
		'last_name'			=> false,
		'username'			=> false,
		'password'			=> false,
		'button'			=> __( 'Submit', 'wampum' ),
		'inline'			=> false,
		'member_message'	=> '',
	), $args, 'wampum_membership_form' );


	// Bail if no plan ID
	if ( ! $args['plan_id'] ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-user-membership');

	$first_name = $last_name = $email = $disabled = '';

	if ( is_user_logged_in() ) {
		$current_user	= wp_get_current_user();
		$first_name		= $current_user->first_name;
		$last_name		= $current_user->last_name;
		// $name		= trim( $first . ' ' . $last );
		$email			= $current_user->user_email;
		$disabled		= ' disabled';
	} else {
		// user is logged out, so after successful form submission require them to change their password
		$args['redirect'] = add_query_arg( 'user', 'password', $args['redirect'] );
	}


	$classes = 'wampum-form';
	if ( filter_var( $args['inline'], FILTER_VALIDATE_BOOLEAN ) ) {
		$classes .= ' wampum-form-inline';
	}

	ob_start();
	?>
	<div class="<?php echo $classes; ?>">
		<?php
		if ( is_user_logged_in() && wc_memberships_is_user_member( get_current_user_id(), $args['plan_id'] ) ) {
			$message = $args['member_message'] ? $args['member_message'] : '';
			echo wpautop($args['member_message']);
		} else {
			// Maybe display a title
			echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
			?>
			<form id="wampum_user_membership_form" class="wampum-user-membership-form" name="wampum_user_membership_form" method="post">

				<!-- Honeypot -->
				<p class="wampum-field wampum-say-what">
					<label for="wampum_membership_name">Say What?</label>
					<input type="text" name="wampum_say_what" id="wampum_say_what" value="">
				</p>

				<!-- First Name -->
				<?php if ( filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-first-name">
						<label for="wampum_membership_first_name"><?php _e( 'First Name', 'wampum' ); ?></label>
						<input type="text" name="wampum_membership_first_name" id="wampum_membership_first_name" class="input" value="<?php echo $first_name; ?>">
					</p>

				<?php } ?>

				<!-- Last Name -->
				<?php if ( filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-last-name">
						<label for="wampum_membership_last_name"><?php _e( 'Last Name', 'wampum' ); ?></label>
						<input type="text" name="wampum_membership_last_name" id="wampum_membership_last_name" class="input" value="<?php echo $last_name; ?>">
					</p>

				<?php } ?>

				<!-- Email -->
				<p class="wampum-field<?php echo $disabled; ?> membership-email">
					<label for="wampum_membership_email"><?php _e( 'Email', 'wampum' ); ?><span class="required">*</span></label>
					<input type="email" name="wampum_membership_email" id="wampum_membership_email" class="input" value="<?php echo $email; ?>" required<?php echo $disabled; ?>>
				</p>

			    <?php
			    if ( ! is_user_logged_in() ) {

				    if ( filter_var( $args['username'], FILTER_VALIDATE_BOOLEAN ) ) {
				    	?>
						<p class="wampum-field membership-username">
							<label for="wampum_membership_username"><?php _e( 'Username', 'wampum' ); ?><span class="required">*</span></label>
							<input type="text" name="wampum_membership_username" id="wampum_membership_username" class="input" value="" required>
						</p>
						<?php
					}

				    if ( filter_var( $args['password'], FILTER_VALIDATE_BOOLEAN ) ) {
				    	?>
						<p class="wampum-field membership-password">
							<label for="wampum_membership_password"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
							<input type="password" name="wampum_membership_password" id="wampum_membership_password" class="input" value="" required>
						</p>
						<?php
					}

				}
				?>

				<p class="wampum-field wampum-submit membership-submit">
					<input type="submit" name="wampum_submit" id="wampum_submit" class="button" value="<?php echo $args['button']; ?>">
					<input type="hidden" name="wampum_plan_id" id="wampum_plan_id" value="<?php echo $args['plan_id']; ?>">
					<input type="hidden" name="redirect_to" value="<?php echo $args['redirect']; ?>">
				</p>

			</form>
			<style media="screen" type="text/css">.wampum-say-what { display: none; visibility: hidden; }</style>
		<?php
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}
