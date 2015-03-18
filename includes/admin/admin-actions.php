<?php
/**
 * Admin Actions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display links next to title in settings panel
 *
 * @since 1.0.0
 * @return array
*/
function wpum_add_links_to_settings_title() {
	echo '<a href="http://support.wp-user-manager.com" class="add-new-h2" target="_blank">'.__('Documentation').'</a>';
	echo '<a href="http://wp-user-manager.com/addons" class="add-new-h2" target="_blank">'.__('Add Ons').'</a>';
}
add_action( 'wpum_next_to_settings_title', 'wpum_add_links_to_settings_title' );

/**
 * Function to display content of the "registration_status" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_registration_status() {

	$output = null;

	if( get_option( 'users_can_register' ) ) {
		$output = '<div class="wpum-admin-message">'.sprintf( __( '<strong>Enabled.</strong> <br/> <small>Registrations can be disabled in <a href="%s" target="_blank">Settings -> General</a>.</small>', 'wpum' ), admin_url( 'options-general.php#users_can_register' ) ).'</div>';
	} else {
		$output = '<div class="wpum-admin-message">'.sprintf( __( 'Registrations are disabled. Enable the "Membership" option in <a href="%s" target="_blank">Settings -> General</a>.', 'wpum' ), admin_url( 'options-general.php#users_can_register' ) ).'</div>';
	}

	echo $output;

}
add_action( 'wpum_registration_status', 'wpum_option_registration_status' );

/**
 * Check on which pages to enable the shortcodes editor.
 *
 * @access public
 * @since  1.0.0
 * @return void
*/
function wpum_shortcodes_add_mce_button() {
	// check user permissions
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	// check if WYSIWYG is enabled
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'wpum_shortcodes_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'wpum_shortcodes_register_mce_button' );
	}
}
add_action( 'admin_head', 'wpum_shortcodes_add_mce_button' );

/**
 * Load tinymce plugin
 *
 * @access public
 * @since  1.0.0
 * @return $plugin_array
*/
function wpum_shortcodes_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['wpum_shortcodes_mce_button'] = apply_filters( 'wpum_shortcodes_tinymce_js_file_url', WPUM_PLUGIN_URL . '/includes/admin/tinymce/js/wpum_shortcodes_tinymce.js' );
	return $plugin_array;
}

/**
 * Load tinymce button
 *
 * @access public
 * @since  1.0.0
 * @return $buttons
*/
function wpum_shortcodes_register_mce_button( $buttons ) {
	array_push( $buttons, 'wpum_shortcodes_mce_button' );
	return $buttons;
}

/**
 * Function to display content of the "registration_role" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_registration_role() {

	$role = get_option( 'default_role' );

	$output = '<span class="wpum-role-option">'.$role.'.</span>';
	$output .= '<br/><small>'.sprintf( __('The default user role for registrations can be changed in <a href="%s">Settings -> General</a>'), admin_url( 'options-general.php#default_role' ) ).'</small>';

	echo $output;

}
add_action( 'wpum_registration_role', 'wpum_option_registration_role' );

/**
 * Processes all WPUM actions sent via POST and GET by looking for the 'wpum-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function wpum_process_actions() {
	if ( isset( $_POST['wpum-action'] ) ) {
		do_action( 'wpum_' . $_POST['wpum-action'], $_POST );
	}

	if ( isset( $_GET['wpum-action'] ) ) {
		do_action( 'wpum_' . $_GET['wpum-action'], $_GET );
	}
}
add_action( 'admin_init', 'wpum_process_actions' );

/**
 * Function to display content of the "restore_emails" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_restore_emails() {

	$output = '<a id="wpum-restore-emails" href="'.add_query_arg( array('tool' => 'restore-email') , admin_url( 'users.php?page=wpum-settings&tab=tools' ) ).'" class="button">'.__('Restore default emails').'</a>';
	$output .= '<br/><p class="description">' . __('Click the button to restore the default emails content and subject.') . '</p>';
	$output .= wp_nonce_field( "wpum_nonce_login_form", "wpum_backend_security" );

	echo $output;

}
add_action( 'wpum_restore_emails', 'wpum_option_restore_emails' );

/**
 * Function to display content of the "restore_default_fields" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_option_restore_default_fields() {

	$output = '<a id="wpum-restore-default-fields" href="'.add_query_arg( array('tool' => 'restore-default-fields') , admin_url( 'users.php?page=wpum-settings&tab=tools' ) ).'" class="button">'.__('Restore default fields settings').'</a>';
	$output .= '<br/><p class="description">' . __('Click the button to restore the default fields settings.') . '</p>';
	$output .= wp_nonce_field( "wpum_nonce_default_fields_restore", "wpum_backend_fields_restore" );

	echo $output;

}
add_action( 'wpum_restore_default_fields', 'wpum_option_restore_default_fields' );


/**
 * Function to display content of the "wpum_profile_permalink" option.
 *
 * @since 1.0.0
 * @return array
*/
function wpum_profile_permalink() {

	$output = '<p>'. sprintf(__('Current profile permalink structure: %s%s'), wpum_get_profile_page_url(), get_option( 'wpum_permalink', 'user_id' ) ) . '</p>';
	$output .= '<p class="description">' . sprintf( __('You can change the profiles permalink structure into your <a href="%s">permalink settings page</a>.'), admin_url( 'options-permalink.php' ) ) . '</p>';

	// Display error if something is wrong
	if( !wpum_get_core_page_id( 'profile' ) )
		$output = '<p style="color:red;"><strong>'. __('Your users profile page is not configured.') .'</strong>'. ' ' . sprintf( __('<a href="%s">Setup your profile page here.</a>'), admin_url( 'users.php?page=wpum-settings&tab=general' ) ) .'</p>';

	echo $output;

}
add_action( 'wpum_profile_permalinks', 'wpum_profile_permalink' );