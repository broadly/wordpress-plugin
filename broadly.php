<?php
/*
  Plugin Name: Broadly WordPress Plugin
  Plugin URI: http://broadly.com
  Version: 1.0.3
  Description: Easily integrate Broadly.com reviews into your WordPress site!
  Author: Tyler Longren
  Author URI: https://impavidmedia.com/contact/
  Text Domain: broadly
  Domain Path: /languages
  License: Commercial
 */

require_once 'assets/sunrise.php';

function get_broadly($atts) {

  $account_id = esc_attr(get_option('broadly_account_id'));

  extract(shortcode_atts(array(

      'embed' => 'reviews',

      'options' => null

   ), $atts));

  if ( !empty($account_id) && !is_admin() ) {

    $url_prefix = 'https://embed.broadly.com/';

    $url_options = $account_id . '/' . $embed . '?' . $options;

    $url = $url_prefix.$url_options;

    if ( class_exists( 'WP_Http' ) ) {

      $args = array(

        'sslverify' => true

      );

      $resp = wp_remote_request( $url, $args );

      if ( 200 == $resp['response']['code'] ) {

        $content = $resp['body'];

      }

      else {

        $content = "There was an error establishing a connection to the API, please try again in a few minutes.";

      }

    }
    
    else { // if no WP_Http class, fall back to js embed

      $js = '<script type="text/javascript" src="//embed.broadly.com/include.js" defer data-url="/' . $url_options . '"></script>';

    }

    if (isset($js)) {

      echo $js;

    }

    else {

      return $content; // return the HTML

    }

  }

  return false;

}
add_shortcode('broadly', 'get_broadly');


/**
 * Initialize plugin
 */
function broadly_init() {

	// Make plugin available for translation, change /languages/ to your .mo-files folder name
	load_plugin_textdomain( 'broadly', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Initialize Sunrise
	$admin = new Sunrise6( array(
			'file'       => __FILE__,
			'slug'       => 'broadly',
			'prefix'     => 'broadly_',
			'textdomain' => 'broadly',
			'css'        => '',
			'js'         => ''
		) );

  $plugin_dir = plugin_dir_url( __FILE__ );

	// Prepare array with options
	$options = array(

		// Open tab: Regular fields
		array(
			'type' => 'opentab',
			'name' => __( 'Settings', 'broadly' )
		),

		array(
			'id'      => 'account_id',
			'type'    => 'text',
			'default' => '',
			'name'    => __( 'Broadly Account ID', 'broadly' ),
			'desc'    => __( 'The ID provided by your account manager', 'broadly' )
		),

		// Close tab: Regular fields
		array(
			'type' => 'closetab'
		),
    // Open tab: Regular fields
    array(
      'type' => 'opentab',
      'name' => __( 'Usage', 'broadly' )
    ),
    array(
      'id'      => 'usage',
      'type'    => 'html',
      'content' => '<h2>How to Use The Plugin</h2><p>Simply add the <code>[broadly]</code> shortcode to the post or page that you want the reviews to be displayed on. Save the post or page and go visit the page. Your reviews should show similar to the image below.</p>
      <p><img src="' . $plugin_dir . 'reviews-example-screenshot.png" border="0" /></p>'
    ),
    // Close tab: Regular fields
    array(
      'type' => 'closetab'
    )
	);

	// Add top-level menu (like Dashboard -> Comments)
	$admin->add_menu( array(
			'page_title'  => __( 'Broadly Settings', 'broadly' ), // Settings page <title>
			'menu_title'  => __( 'Broadly Settings', 'broadly' ), // Menu title, will be shown in left dashboard menu
			'capability'  => 'manage_options', // Minimal user capability to access this page
			'slug'        => 'broadly-settings', // Unique page slug
			'position'    => '91.1', // Menu position from 80 to <infinity>, you can use decimals
			'options'     => $options // Array with options available on this page
		) );
}

// Hook to plugins_loaded
add_action( 'plugins_loaded', 'broadly_init' );
