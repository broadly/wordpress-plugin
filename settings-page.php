<div class="wrap">
	<h1><?php _e( 'Broadly Settings', 'broadly' ); ?></h1>
	
	<div>
		<form action='options.php' method='POST'>
		<?php
			settings_fields( 'broadly_options' );
			do_settings_sections( 'broadly' );
			
			submit_button();
		?>
		</form>
	</div>
	
	<?php if ( ! empty( $broadly_options['broadly_account_id'] ) ): ?>
	<iframe src="http://embed.broadly.com/<?php echo esc_html( $broadly_options['broadly_account_id'] ); ?>" width="100%" height="700px"></iframe>
	<?php endif; ?>
	
</div>