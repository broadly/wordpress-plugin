<?php
/*
  Plugin Name: Broadly WordPress Plugin
  Plugin URI: http://broadly.com
  Version: 2.0.1
  Description: Easily integrate Broadly.com reviews into your WordPress site!
  Author: Tyler Longren
  Author URI: https://longrendev.io/
  Text Domain: broadly
  Domain Path: /languages
  License: MIT
 */


add_action('admin_menu', 'broadly_add_admin_menu');
add_action('admin_init', 'broadly_settings_init');


function broadly_add_admin_menu(  ) {

  add_menu_page('Broadly', 'Broadly', 'manage_options', 'broadly', 'broadly_options_page', plugins_url('img/logo.png', __FILE__ ));

}


function broadly_settings_init(  ) {

  register_setting('broadly_plugin_page', 'broadly_settings');

  add_settings_section(
    'broadly_broadly_plugin_page_section',
    null,
    'broadly_settings_section_callback',
    'broadly_plugin_page'
  );

  add_settings_field(
    'broadly_account_id',
    __('Broadly Account ID', 'broadly'),
    'broadly_account_id_render',
    'broadly_plugin_page',
    'broadly_broadly_plugin_page_section'
  );


}


function broadly_account_id_render(  ) {

  $options = get_option('broadly_settings');
  ?>
  <input type='text' name='broadly_settings[broadly_account_id]' value='<?php echo $options['broadly_account_id']; ?>'>
  <?php

}


function broadly_settings_section_callback(  ) {

  echo __('Enter your Broadly account ID below.', 'broadly');

}


function broadly_options_page(  ) {

  ?>
  <form action='options.php' method='post'>

    <h2>Broadly</h2>

    <?php
    settings_fields('broadly_plugin_page');
    do_settings_sections('broadly_plugin_page');
    submit_button();
    ?>

  </form>
  <h2>How to Use The Plugin</h2>

  <p>Simply add the <code>[broadly]</code> shortcode to the post or page that you want the reviews to be displayed on. Save the post or page and go visit the page. Your reviews should show similar to the image below.</p>
  <p>
    <?php echo '<img src="' . plugins_url('img/reviews-example-screenshot.png', __FILE__ ) . '" > '; ?>
  </p>
  <?php

}

function get_broadly($atts) {

  $broadly_options = get_option('broadly_settings');
  $account_id = $broadly_options['broadly_account_id'];

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

    if ( class_exists('WP_Http') ) {

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
  echo "Incorrect Broadly account ID or missing account ID. Were you sure to entere it?";
  return false;

}
add_shortcode('broadly', 'get_broadly');

?>