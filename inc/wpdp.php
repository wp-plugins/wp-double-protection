<?php
/**
 * This file contains the main fuctionality of the plugin.
 */

class WP_double_protection {
	/**
	 * Contains all the active hooks and filters for the plugin
	 *
	 * @param void
	 *
	 * @return void
	 */
	public function __construct() {
		register_activation_hook( WPDP_FILE, array( $this, 'wpdp_register_activation' ) );
		add_action( 'init', array( $this, 'wpdp_add_localization' ) );
		add_action( 'user_register', array( $this, 'wpdp_user_register' ) );
		add_action( 'login_form', array( $this, 'wpdp_add_second_password_field' ) );
		add_filter( 'shake_error_codes', array( $this, 'wpdp_shake_error_codes' ) );
		add_filter( 'wp_authenticate_user', array( $this, 'wpdp_authenticate_second_password' ), 10, 2 );
		add_action( 'show_user_profile', array( $this, 'wpdp_add_extra_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'wpdp_add_extra_profile_fields' ) );
		add_action( 'admin_print_scripts-profile.php', array( $this, 'wpdp_enqueue_scripts' ) );
		add_action( 'admin_print_scripts-user-edit.php', array( $this, 'wpdp_enqueue_scripts' ) );
		add_action( 'personal_options_update', array( $this, 'wpdp_save_extra_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'wpdp_save_extra_profile_fields' ) );
		add_action( 'password_reset', array( $this, 'wpdp_password_reset' ) );
	}

	/**
	 * Adds the localization.
	 *
	 * @param void
	 *
	 * @return void
	 */
	public function wpdp_add_localization() {
		// Localization
		load_plugin_textdomain( 'wpdp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Adds the wpdp flag to the user meta of the available users on activaton.
	 *
	 * @param void
	 *
	 * @return void
	 */
	public function wpdp_register_activation() {
		$wpdp_users    = get_users();
		$wpdp_users_id = wp_list_pluck( $wpdp_users, 'ID' );

		foreach ( $wpdp_users_id as $user_id ) {
			if ( 1 != get_user_meta( $user_id, 'wpdp_flag', true ) ) {
				update_user_meta( $user_id, 'wpdp_flag', 0 );
				update_user_meta( $user_id, 'wpdp_second_password', 0 );
			}
		}
	}

	/**
	 * Adds the wpdp flag to the user meta of the newly added user.
	 *
	 * @param int     $user_id the id of the newly added user
	 *
	 * @return void
	 */
	public function wpdp_user_register( $user_id ) {
		update_user_meta( $user_id, 'wpdp_flag', 0 );
		update_user_meta( $user_id, 'wpdp_second_password', 0 );
	}

	/**
	 * Adds the second password field to the login form
	 *
	 * @param void
	 *
	 * @return string $html The html for the second password field to the login form
	 */
	public function wpdp_add_second_password_field() {
		$html  = '';
		$html .= '<p><label for="second_pass">' . __( 'Second Password', 'wpdp' );
		$html .= '<input type="password" name="second_pass" id="second_pass" class="input" value="" size="20" /></label></p>';

		echo $html;
	}

	/**
	 * Adds the wpdp error codes to the array of shake error codes
	 *
	 * @param array   $shake_error_codes The array of default error codes for shake js
	 *
	 * @return array $shake_error_codes The array of modified error codes for shake js
	 */
	public function wpdp_shake_error_codes( $shake_error_codes ) {
		array_push( $shake_error_codes, 'invalid_second_password', 'empty_second_password' );

		return $shake_error_codes;
	}

	/**
	 * Adds the wpdp error codes to the array of shake error codes
	 *
	 * @param object  $user     WP_User
	 * @param string  $password the user password
	 *
	 * @return object $user Either WP_User|WP_Error
	 */
	public function wpdp_authenticate_second_password( $user, $password ) {
		if ( ! empty( $_POST['second_pass'] ) && isset( $_POST['second_pass'] ) ) {
			$wpdp_second_pass = get_user_meta( $user->ID, 'wpdp_second_password', true );
			$wpdp_flag        = get_user_meta( $user->ID, 'wpdp_flag', true );
			if ( $wpdp_second_pass && $wpdp_flag == 1 ) {
				if ( ! wp_check_password( $_POST['second_pass'], $wpdp_second_pass, $user->ID ) ) {
					$user = new WP_Error( 'invalid_second_password', __( '<strong>ERROR</strong>: Invalid second password.', 'wpdp' ) );

					return $user;
				} else {
					return $user;
				}
			}
			if ( $wpdp_flag == 0 ) {
				if ( $password != $_POST['second_pass'] ) {
					$user = new WP_Error( 'invalid_second_password', __( '<strong>ERROR</strong>: Invalid second password.', 'wpdp' ) );

					return $user;
				} else {
					return $user;
				}
			}
		} else {
			$user = new WP_Error( 'empty_second_password', __( '<strong>ERROR</strong>: The second password field is empty.', 'wpdp' ) );

			return $user;
		}
	}

	/**
	 * Adds extra second password fields in the profile page to change the password.
	 *
	 * @param void
	 *
	 * @return string The html of the second password fields
	 */
	public function wpdp_add_extra_profile_fields() {
		?>
		<table class="form-table">
			<tr id="second-password">
				<th><label for="secondpass1"><?php _e( 'New Second Password', 'wpdp' ); ?></label></th>
				<td>
					<input class="hidden" value=" " />
					<input type="password" name="secondpass1" id="secondpass1" class="regular-text" size="16" value="" autocomplete="off" /><br />
					<span class="description"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.', 'wpdp' ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="secondpass2"><?php _e( 'Repeat New Second Password', 'wpdp' ); ?></label></th>
				<td>
					<input name="secondpass2" type="password" id="secondpass2" class="regular-text" size="16" value="" autocomplete="off" /><br />
					<span class="description" for="secondpass2"><?php _e( 'Type your new password again.', 'wpdp' ); ?></span>
					<br />
					<div id="secondpass-strength-result"><?php _e( 'Strength indicator', 'wpdp' ); ?></div>
					<p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', 'wpdp' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Enqueues the style and css to the profile page
	 *
	 * @param void
	 *
	 * @return void
	 */
	public function wpdp_enqueue_scripts() {
		wp_enqueue_script( 'wpdp.js', WPDP_URI . '/inc/js/wpdp.js', array( 'jquery', 'password-strength-meter' ), false, 1 );
		wp_enqueue_style( 'wpdp.css', WPDP_URI . '/inc/css/wpdp.css' );
	}

	public function wpdp_save_extra_profile_fields( $user_id ) {
		if ( $_POST['secondpass1'] === $_POST['secondpass2'] )
			$user_second_pass = update_usermeta( $user_id, 'wpdp_second_password', wp_hash_password( trim( $_POST['secondpass1'] ) ) );

		if ( $user_second_pass )
			update_usermeta( $user_id, 'wpdp_flag', 1 );
	}

	public function wpdp_password_reset( $user ) {
		update_user_meta( $user->ID, 'wpdp_flag', 0 );
		update_user_meta( $user->ID, 'wpdp_second_password', 0 );
	}
}
