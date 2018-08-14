<?php
/*
Plugin Name: Broadly for WordPress
Description: Dynamic integration of your Broadly reviews within your existing WordPress website.
Plugin URL: http://broadly.com
Author: Broadly
Author URI: http://broadly.com/
Version: 3.0.0
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

		public static $version = '3.0.0';

		function __construct() {
			// Creating the admin menu
			add_action( 'admin_menu', array( $this, 'broadly_menu' ) );

			// Register the Settings fields
			add_action( 'admin_init', array( $this, 'broadly_settings_init' ) );

			// Replace the Broadly scripts with the prefetched HTML
			add_filter( 'the_content', array( $this, 'replace_js' ) );

      // Inject webchat script in the header.
			add_action('wp_head', array( $this, 'add_webchat' ) );
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

					// Append the data-url and build the embed URL
					$broadly_embed_url = 'https://embed.broadly.com/' . $dataurl_match;

					$args = array();
					/**
					 * Hook the arguments for the remote call.
					 *
					 * If needed, we can disable SSL or update the other HTTP arguments.
					 */
					$args = apply_filters( 'broadly_ssl_args', $args );

					add_filter( 'http_request_args', array( $this, 'filter_broadly_headers' ), 10, 2 );
					$response = wp_remote_get( $broadly_embed_url, $args );
					remove_filter( 'http_request_args', array( $this, 'filter_broadly_headers' ), 10 );

					// Verify for errors - not being sent for reporting yet
					$error = null;
					if ( is_wp_error( $response ) ) {
						$error = __('Error Found ( ' . $response->get_error_message() . ' )', 'broadly' );
					} else {
						if ( ! empty( $response["body"] ) ) {
							$embed_content = $response["body"];
						} else {
							$error = __( 'No body tag in the response', 'broadly' );
						}
					}

					// If errors occured, don't replace the script tags
					if ( ! is_null( $error ) ) {
						continue;
					}

					// Wrapper tabs
					$embed_name = $this->parse_embed_from_datauri( $dataurl_match );
					$opening_comment = sprintf( '<!-- Start of Broadly "%s" content - Broadly for WordPress %s -->', $embed_name, self::$version );
					$closing_comment = sprintf( '<!-- End of Broadly "%s" content -->', $embed_name );

					// Replace the script tag with the HTML reviews
					$content = str_replace( $script_match, $opening_comment . $embed_content . $closing_comment, $content );
				}
			}

			return $content;
		}

		/**
		 * Filter Broadly Headers
		 *
		 * @param $request_args original request arguments
		 * @param $url request URL
		 */
		public function filter_broadly_headers( $request_args, $url ) {
			$user_agent = sprintf( '%s; Broadly/%s', $request_args['user-agent'], self::$version );

			// Update the User-Agent header
			$headers = $request_args['headers'];
			$headers['User-Agent'] = $user_agent;

			// Set the Referer header if possible
			$current_page = get_the_permalink();
			if ( false !== $current_page ) {
				$headers['Referer'] = $current_page;
			}

			// Update the original headers
			$request_args['headers'] = $headers;

			return $request_args;
		}

    private function get_account_id() {
      $broadly_account_id = null;
      $broadly_options    = get_option( 'broadly_options', array() );
      if ( is_array( $broadly_options ) 
        && ! empty( $broadly_options['broadly_account_id'] ) ) {
        
        $broadly_account_id = $broadly_options['broadly_account_id']; 
      }

      return $broadly_account_id;
    }
    
		public function add_webchat() {
      $broadly_account_id = $this->get_account_id();

			if ( $broadly_account_id != null) {
				$script  = '<script>'; 
				$script .= '  window.broadlyChat = {';
				$script .= '    id: "'.$broadly_account_id.'"';
				$script .= '  };';
				$script .= '</script>';
				$script .= '<script src="https://chat.broadly.com/javascript/chat.js" async defer></script>';
				echo $script;
			}
		}

		/**
		 * Parse the type of embed from the data_uri
		 *
		 * @param $data_uri argument passed to Broadly including the embed name
		 */
		private function parse_embed_from_datauri( $data_uri ) {
			// Split the data uri in attempt to read the type
			$components = explode( '/', $data_uri );

			$embed_name = 'reviews';

			if ( isset( $components[1] ) ) {
				$embed_name = $components[1];
			}

			return $embed_name;
		}
	}

	// Initialize the plugin body
	$broadly = new Broadly_Plugin();
}
