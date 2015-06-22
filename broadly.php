<?php
/*
  Plugin Name: Broadly WordPress Plugin
  Plugin URI: http://broadly.com
  Version: 1.1.0
  Description: Easily integrate Broadly.com reviews into your WordPress site!
  Author: Tyler Longren
  Author URI: https://longrendev.io/
  Text Domain: broadly
  Domain Path: /languages
  License: GPL2
 */

function get_broadly($atts) {

  $account_id = esc_attr(get_option('broadly_account_id'));

  $args = shortcode_atts( 
    array(
        'embed'   => 'reviews',
        'options'   => null
    ), 
    $atts
  );

  $embed = $args['embed'];

  $options = $args['options'];

  if ( !empty($account_id) && !is_admin() ) {

    $url_prefix = 'https://embed.broadly.com/';

    $url_options = $account_id . '/' . $embed . '?';

    if (isset($options)) {

      $url_options = $account_id . '/' . $embed . '?' . $options;

    }

    $url = $url_prefix.$url_options;

    $content = '<script type="text/javascript" src="//embed.broadly.com/include.js" defer data-url="/' . $url_options . '"></script>';

    if ( class_exists( 'WP_Http' ) ) {

      $args = array(

        'sslverify' => true

      );

      $resp = wp_remote_request( $url, $args );

      if ( 200 == $resp['response']['code'] ) {

        $content = $resp['body'];

      }

    }

    return $content;

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

  // create custom plugin settings menu
add_action('admin_menu', 'broadly_plugin_create_menu');

function my_cool_plugin_create_menu() {

  //create new top-level menu
  add_menu_page('Broadly Settings', 'Broadly Settings', 'administrator', __FILE__, 'broadly_plugin_settings_page' , plugins_url('/img/logo.png', __FILE__) );

  //call register settings function
  add_action( 'admin_init', 'register_broadly_plugin_settings' );
}


function register_broadly_plugin_settings() {
  //register our settings
  register_setting( 'broadly-plugin-settings-group', 'broadly_account_id' );
}

function my_cool_plugin_settings_page() {
?>
<div class="wrap">
<h2>Broadly Settings</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'broadly-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'broadly-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Account ID</th>
        <td><input type="text" name="broadly_account_id" value="<?php echo esc_attr( get_option('broadly_account_id') ); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
<h2>How to Use The Plugin</h2><p>Simply add the <code>[broadly]</code> shortcode to the post or page that you want the reviews to be displayed on. Save the post or page and go visit the page. Your reviews should show similar to the image below.</p>
      <p><img src="<?php plugins_url('/img/logo.png', __FILE__); ?>" border="0" /></p>
</div>
<?php
// Hook to plugins_loaded
add_action( 'plugins_loaded', 'broadly_init' );
