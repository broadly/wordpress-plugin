<?php
/*
Plugin Name: Broadly for WordPress
Description: Dynamic integration of your Broadly reviews within your existing WordPress website. 
Plugin URL: http://broadly.com
Author: Broadly
Author URI: http://broadly.com/
Version: 2.0
License: GPLv2 or later
*/

if ( ! class_exists( 'Broadly_Plugin' ) ) {
	
	/**
	 * Main Broadly Plugin class
	 * 
	 * Responsible for the frontend review management and backend  
	 * settings for the plugin.
	 * 
	 * @author nofearinc
	 *
	 */
	class Broadly_Plugin {
	
		function __construct() {
			// Creating the admin menu
			add_action( 'admin_menu', array( $this, 'broadly_menu' ) );
			
			// Register the Settings fields
			add_action( 'admin_init', array( $this, 'broadly_settings_init' ) );
			
			// Replace the Broadly scripts with the prefetched HTML
			add_filter( 'the_content', array( $this, 'replace_js' ) );
		}

		/**
		 * Register a menu page for Broadly under Settings
		 */
		public function broadly_menu() {
			add_options_page( __('Broadly', 'broadly' ), 
					__( 'Broadly Setup', 'broadly' ),
					'manage_options', 'broadly', array( $this, 'broadly_menu_cb' ) );
		}
		
		/**
		 * Settings class initialization
		 */
		public function broadly_settings_init() {
			include_once 'settings.class.php';
		}
		
		/**
		 * Menu page callback - render the UI form for the admin
		 */
		public function broadly_menu_cb() {
			$broadly_options = get_option( 'broadly_options', array() );
			
			include_once 'settings-page.php';
		}

		/**
		 * Replace the JS snippet with the prefetched reviews
		 * 
		 * @param string $content the existing page content
		 * @return string $content the updated page content if a script is found
		 */
		public function replace_js( $content ) {
			// Look for embedly scripts
			$matches_count = preg_match_all( '/<script.*embed\.broadly\.com\/include.js.*data-url="\/([^"]*)[^>]*>(.*?)<\/script>/', $content, $matches );

			// Proceed further only if a match is found - false will handle both 0 and false
			if ( false != $matches_count ) {
				
				// Iterate through all of the matches if more scripts are injected
				for ( $current_match = 0; $current_match < $matches_count; $current_match++ ) {
					
					// Fetch the entire script and the data-url match
					$script_match = $matches[0][$current_match];
					$dataurl_match = $matches[1][$current_match];
					
					// Append the data-url and build the reviews URL
					$broadly_reviews_url = 'http://embed.broadly.com/' . $dataurl_match;
		
					$args = array();
					/**
					 * Hook the arguments for the remote call.
					 * 
					 * If needed, we can disable SSL or update the other HTTP arguments.
					 */
					$args = apply_filters( 'broadly_ssl_args', $args );
		
					$response = wp_remote_get( $broadly_reviews_url, $args );
					
					// Verify for errors - not being sent for reporting yet
					$error = null;
					if ( is_wp_error( $response ) ) {
						$error = __('Error Found ( ' . $response->get_error_message() . ' )', 'broadly' );
					} else {
						if ( ! empty( $response["body"] ) ) {
							$review = $response["body"];
						} else {
							$error = __( 'No body tag in the response', 'broadly' );
						}
					}
					
					// If errors occured, don't replace the script tags
					if ( ! is_null( $error ) ) {
						continue;
					}

					// Replace the script tag with the HTML reviews
					$content = str_replace( $script_match, $review, $content );
				}
			}
			
			return $content;
		}
	}
	
	// Initialize the plugin body
	$broadly = new Broadly_Plugin();
}
