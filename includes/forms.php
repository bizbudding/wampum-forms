<?php

add_shortcode( 'wampum-login-form', 'wampum_get_login_form' );
add_shortcode( 'wampum-password-form', 'wampum_get_password_form' );
add_shortcode( 'wampum-membership-form', 'wampum_get_membership_form' );

/**
 * Get a login form
 *
 * @since   1.0.0
 *
 * @param   array  $args  Args to configure the form
 *
 * @return  string  The login form HTML
 */
function wampum_get_login_form( $args ) {

	// Bail if already logged in
	if ( is_user_logged_in() ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-zxcvbn');
	wp_enqueue_script('wampum-user-forms');

	$args = shortcode_atts( array(
		'title'			 => __( 'Login', 'wampum' ),
		'title_wrap'	 => 'h3',
		'button'		 => __( 'Log In', 'wampum' ),
		'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'remember'       => true,
		'value_username' => '',
		'value_remember' => true,
	), $args, 'wampum-login-form' );

	ob_start();
	?>
	<div class="wampum-form">
		<?php
		echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
		?>
		<form id="wampum_user_login_form" class="wampum-user-login-form" name="wampum_user_login_form" method="post">

			<p class="wampum-field login">
				<label for="wampum_user_login"><?php _e( 'Username/Email', 'wampum' ); ?><span class="required">*</span></label>
				<input type="text" name="wampum_user_login" id="wampum_user_login" class="input" value="<?php echo $args['value_username']; ?>" required>
			</p>

			<p class="wampum-field password">
				<label for="wampum_user_pass"><?php _e( 'Password', 'wampum' ); ?><span class="required">*</span></label>
				<input type="password" name="wampum_user_pass" id="wampum_user_pass" class="input" value="" required>
			</p>

			<?php if ( filter_var( $args['remember'], FILTER_VALIDATE_BOOLEAN ) ) { ?>
				<p class="wampum-field remember">
					<label><input name="rememberme" type="checkbox" id="wampum_rememberme" value="forever" checked="checked"> <?php _e( 'Remember Me', 'wampum' ); ?></label>
				</p>
			<?php } ?>

			<p class="wampum-field wampum-submit login-submit">
				<button class="wampum_submit button" type="submit" form="wampum_user_login_form"><?php echo $args['button']; ?></button>
				<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
			</p>

		</form>
	</div>
	<?php
	return ob_get_clean();

}

/**
 * Get a password form
 *
 * @since   1.0.0
 *
 * @param   array  $args  Args to configure the form
 *
 * @return  string  The login form HTML
 */
function wampum_get_password_form( $args ) {

	// Bail if not logged in
	if ( ! is_user_logged_in() ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-zxcvbn');
	wp_enqueue_script('wampum-user-forms');

	$args = shortcode_atts( array(
		'title'			=> __( 'Set A New Password', 'wampum' ),
		'title_wrap'	=> 'h3',
		'button'		=> __( 'Save Password', 'wampum' ),
		'redirect'		=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
	), $args, 'wampum-password-form' );

	ob_start();
	?>
	<div class="wampum-form">
		<?php
		echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
		?>
		<form id="wampum_user_password_form" class="wampum-user-password-form" name="wampum_user_password_form" method="post">

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
				<button class="wampum_submit button" type="submit" form="wampum_user_password_form"><?php echo $args['button']; ?></button>
				<input type="hidden" name="wampum_user_id" id="wampum_user_id" value="<?php echo get_current_user_id(); ?>">
				<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
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
 * @param   array  $args  Args to configure the form
 *
 * @return  string  The login form HTML
 */
function wampum_get_membership_form( $args ) {

	if ( ! function_exists( 'wc_memberships' ) ) {
		return;
	}

	$args = shortcode_atts( array(
		'plan_id'			=> null, // required
		'title'				=> null,
		'title_wrap'		=> 'h3',
		'button'			=> __( 'Submit', 'wampum' ),
		'redirect'			=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'first_name'		=> true,
		'last_name'			=> false,
		'username'			=> false,
		'password'			=> false,
		'member_message'	=> '',
		'ss_baseuri'		=> '', // 'https://app-3QMU9AFX44.marketingautomation.services/webforms/receivePostback/MzawMDE2MjCwAAA/'
		'ss_endpoint'		=> '', // 'b19a2e43-3904-4b80-b587-353767f56849'
	), $args, 'wampum-membership-form' );

	// Bail if no plan ID
	if ( ! $args['plan_id'] ) {
		return;
	}

	// CSS
	wp_enqueue_style('wampum-user-forms');
	// JS
	wp_enqueue_script('wampum-zxcvbn');
	wp_enqueue_script('wampum-user-forms');


	$first_name = $last_name = $email = $disabled = '';

	if ( is_user_logged_in() ) {
		$current_user	= wp_get_current_user();
		$first_name		= $current_user->first_name;
		$last_name		= $current_user->last_name;
		$email			= $current_user->user_email;
		$disabled		= ' disabled';
	}

	// Keep fields filled out if something crazy happens and page refreshes
	if ( isset($_POST['wampum_membership_first_name']) && ! empty($_POST['wampum_membership_first_name']) ) {
		$email = sanitize_text_field($_POST['wampum_membership_first_name']);
	}
	if ( isset($_POST['wampum_membership_last_name']) && ! empty($_POST['wampum_membership_last_name']) ) {
		$email = sanitize_text_field($_POST['wampum_membership_last_name']);
	}
	if ( isset($_POST['wampum_membership_email']) && ! empty($_POST['wampum_membership_email']) ) {
		$email = sanitize_text_field($_POST['wampum_membership_email']);
	}
	if ( isset($_POST['wampum_membership_username']) && ! empty($_POST['wampum_membership_username']) ) {
		$email = sanitize_text_field($_POST['wampum_membership_username']);
	}

	ob_start();
	?>
	<div class="wampum-form">
		<?php
		if ( is_user_logged_in() && wc_memberships_is_user_member( get_current_user_id(), $args['plan_id'] ) ) {
			echo $args['member_message'] ? wpautop($args['member_message']) : '';
		} else {
			// Maybe display a title
			echo $args['title'] ? sprintf( '<%s>%s</%s>', $args['title_wrap'], $args['title'], $args['title_wrap'] ) : '';
			?>
			<form id="wampum_user_membership_form" class="wampum-user-membership-form" name="wampum_user_membership_form" method="post">

				<p class="wampum-field wampum-say-what">
					<label for="wampum_membership_name">Say What?</label>
					<input type="text" name="wampum_say_what" id="wampum_say_what" value="">
				</p>

				<?php if ( filter_var( $args['first_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-first-name">
						<label for="wampum_membership_first_name"><?php _e( 'First Name', 'wampum' ); ?></label>
						<input type="text" name="wampum_membership_first_name" id="wampum_membership_first_name" class="input" value="<?php echo $first_name; ?>">
					</p>

				<?php } ?>

				<?php if ( filter_var( $args['last_name'], FILTER_VALIDATE_BOOLEAN ) ) { ?>

					<p class="wampum-field membership-name membership-last-name">
						<label for="wampum_membership_last_name"><?php _e( 'Last Name', 'wampum' ); ?></label>
						<input type="text" name="wampum_membership_last_name" id="wampum_membership_last_name" class="input" value="<?php echo $last_name; ?>">
					</p>

				<?php } ?>

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
					<button class="wampum_submit button" type="submit" form="wampum_user_membership_form"><?php echo $args['button']; ?></button>
					<input type="hidden" name="wampum_plan_id" id="wampum_plan_id" value="<?php echo $args['plan_id']; ?>">
					<input type="hidden" name="wampum_redirect" class="wampum_redirect" value="<?php echo $args['redirect']; ?>">
					<?php
					// SharpSpring baseURI
					if ( $args['ss_baseuri'] ) {
						echo '<input type="hidden" name="wampum_ss_baseuri" id="wampum_ss_baseuri" value="' . sanitize_text_field($args['ss_baseuri']) . '">';
					}
					// SharpSpring endpoint
					if ( $args['ss_endpoint'] ) {
						echo '<input type="hidden" name="wampum_ss_endpoint" id="wampum_ss_endpoint" value="' . sanitize_text_field($args['ss_endpoint']) . '">';
					}
					?>
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
