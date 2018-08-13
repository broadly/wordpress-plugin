<?php
  $broadly_options         = get_option( 'broadly_options', array() );
  $broadly_account_enabled = false;
  $broadly_webchat_enabled = false;

  $broadly_account_enabled = ( is_array( $broadly_options ) && ! empty( $broadly_options['broadly_account_id'] ) );

  if ($broadly_account_enabled) {
    $broadly_account_id = $broadly_options['broadly_account_id'];
    $broadly_webchat_url = "https://chat.broadly.com/chat/".$broadly_account_id;
    $content = file_get_contents($broadly_webchat_url);
    if ( $content ) {
      $broadly_webchat_enabled = json_decode($content, true)['enabled'];
    }
  }
?>


<div class="wrap">
	<h1><?php _e( 'Broadly Settings', 'broadly' ); ?></h1>

  <p>
    The Broadly 3.0 plugin for Wordpress quickly connects you with your website's visitors, and shows off your best reviews from Google, Facebook, Tripadvisor, and Broadly. 
    <a href="https://help.broadly.com/article/131-broadly-wordpress-setup">Learn more</a>
  </p>
	
  <h2>
    <?php _e( 'Setup', 'broadly' ); ?>
  </h2>
  <p>
    To get started, enter your Business ID and click save. 
  </p>
  <form action='options.php' method='POST'>
  <?php
    settings_fields( 'broadly_options' );
    do_settings_fields( 'broadly', 'broadly_admin_section');
    submit_button('Save', null, 'small', false);
  ?>
  </form>

  <?php if ($broadly_account_enabled) { ?>  
  <h2>
    <?php _e( 'Web Chat', 'broadly' ); ?>
  </h2>
  <p>
    <?php if ( $broadly_webchat_enabled === true ) { ?>
      Broadly Web Chat provides the quick responses modern consumers expect, and the new leads you demand.
    <?php } else { ?>
      Broadly Web Chat is disabled.
    <?php } ?>
    <a href="https://help.broadly.com/article/131-broadly-wordpress-setup">Learn more</a>
  </p>

  <h2>
    <?php _e( 'Contact Form', 'broadly' ); ?>
  </h2>

  <p>
    Please update your contact form to use the following email address:
    <br /> 
    <code>consumer+<?php echo $broadly_options['broadly_account_id'] ?>@broadly.com</code>
  </p>

  <h2>
    <?php _e( 'Review Stream', 'broadly' ); ?>
  </h2>
  <p>
    The Broadly Review Stream displays your most recent 4-star and 5-star reviews from Google, Facebook, Tripadvisor, and more. Follow the instructions below and add it to any page that you want to show your reviews.
  </p>
	<?php if ( ! empty( $broadly_options['broadly_account_id'] ) ) { ?>
    <iframe src="http://embed.broadly.com/<?php echo esc_html( $broadly_options['broadly_account_id'] ); ?>" i width="100%" height="700px"> </iframe>
  <?php } ?>  

  <?php } ?>
  <p>
    Broadly does not and will not sell your customer information to third parties. 
    <a href="https://broadly.com/privacy/">Privacy Policy</a>
  </p>
	
</div>
