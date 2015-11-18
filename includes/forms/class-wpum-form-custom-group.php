<?php
/**
 * WP User Manager Form: Group fields.
 * Used to display profile fields to update from a specific group.
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Form_Custom_Group Class
 *
 * @since 1.2.0
 */
class WPUM_Form_Custom_Group extends WPUM_Form {

	/**
	 * The name of this group.
	 *
	 * @var string
	 */
  public static $form_name = 'custom-group';

	/**
	 * The id number of the group to retrieve fields from.
	 *
	 * @var string
	 */
	public static $group_id;

	/**
	 * Current user id.
	 *
	 * @var int
	 */
	public static $user_id;

	/**
	 * Init the form.
	 *
	 * @access public
	 * @since 1.2.0
	 * @return void
	 */
	public static function init() {

		self::$group_id = self::get_group_id();

		// Retrieve user id.
		self::$user_id = get_current_user_id();

		add_action( 'wp_loaded', array( __CLASS__, 'process' ), 20 );

	}

	/**
	 * Get the group id that we're processing right now.
	 * The id number is first retrived from the global variable, then stored into the form.
	 * On submission the form sends back the group id again.
	 *
	 * @return mixed
	 * @since 1.2.0
	 */
	public static function get_group_id() {

		global $wpum_fields_group_id;

		$group_id = false;

		if ( ! empty( $wpum_fields_group_id ) ) {
			$group_id = $wpum_fields_group_id;
		} else if( isset( $_POST['wpum_group_form_id'] ) && $_POST['wpum_group_form_id'] !== '' ) {
			$group_id = $_POST['wpum_group_form_id'];
		}

		return $group_id;

	}

	/**
	 * Define password update form fields
	 *
	 * @access public
	 * @since 1.2.0
	 * @return void
	 */
	public static function get_group_fields() {

		$fields = wpumcf_get_group_fields_for_form( self::$group_id );

		self::$fields = array(
			'custom-group' => $fields
		);

	}

	/**
	 * Process the submission.
	 *
	 * @access public
	 * @since 1.2.0
	 * @return void
	 */
	public static function process() {

		// Get fields
		self::get_group_fields();

		// Get posted values
		$values = self::get_posted_fields();

		if ( empty( $_POST['wpum_submit_form'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'custom-group' ) ) {
			return;
		}

		// Check values
		if( empty( $values ) || ! is_array( $values ) )
			return;

		// Validate required
		if ( is_wp_error( ( $return = self::validate_fields( $values, self::$form_name ) ) ) ) {
			self::add_error( $return->get_error_message() );
			return;
		}

		// Proceed to update the password
		$user_data = array(
			'ID' => get_current_user_id(),
		);

		$user_id = wp_update_user( $user_data );

		if ( is_wp_error( $user_id ) ) {

			self::add_error( $user_id->get_error_message() );

		} else {

			self::add_confirmation( __('Password successfully updated.', 'wpum') );

		}

	}

	/**
	 * Output the form.
	 *
	 * @access public
	 * @since 1.2.0
	 * @return void
	 */
	public static function output( $atts = array() ) {

		// Get fields.
		self::get_group_fields();

		if( isset( $_POST['submit_wpum_group_form'] ) ) {

			// Show errors from fields.
			self::show_errors();

			// Show confirmation messages.
			self::show_confirmations();

		}

		$args = array(
			'atts'         => $atts,
			'form'         => self::$form_name,
			'group_id'     => self::get_group_id(),
			'group_fields' => self::get_fields( 'custom-group' ),
		);

		get_wpum_template( 'forms/group-form.php', $args );

	}

}