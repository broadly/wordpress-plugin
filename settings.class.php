<?php
/**
 * The Broadly Settings class
 * 
 * Handling the Account ID database handling
 * 
 * @author nofearinc
 *
 */
class Broadly_Settings {
	
	/**
	 * Register the Setting, and the required section and settings field.
	 */
	public function __construct() {
		register_setting( 'broadly_options', 'broadly_options' );
		
		add_settings_section(
				'broadly_admin_section',
				null,
				array( $this, 'broadly_settings_section_cb' ),
				'broadly'
		);
		
		add_settings_field(
				'broadly_account_id',
				__( 'Broadly Account ID', 'broadly' ),
				array( $this, 'broadly_account_id_cb' ),
				'broadly',
				'broadly_admin_section'
		);
	}
	
	/**
	 * Settings message for the section heading
	 */
	public function broadly_settings_section_cb() {
		_e( 'Please enter your Account ID here. Based on your Account ID we will display a table with the available'
				.' script snippets that you could use across the site.', 'broadly' );
	}
	
	/**
	 * Broadly Account ID field management
	 */
	public function broadly_account_id_cb() {
		$broadly_options = get_option( 'broadly_options', array() );
		
		$account_id = '';
		
		// Make sure it's properly escaped
		if ( is_array( $broadly_options ) 
				&& ! empty( $broadly_options['broadly_account_id'] ) ) {
			$account_id = esc_html( $broadly_options['broadly_account_id'] );
		}
		
		echo "<input type='text' name='broadly_options[broadly_account_id]' value='$account_id'>";
	}
}

/**
 * Initialize the Settings class
 */
$broadly_settings = new Broadly_Settings();