<?php
add_action( 'wp_footer', 'cfe_footer_custom_code' );

function cfe_footer_custom_code() {
	$google_recaptcha_site_key   = carbon_get_theme_option( 'google_recaptcha_site_key' );
	$google_recaptcha_secret_key = carbon_get_theme_option( 'google_recaptcha_secret_key' );
	if ( $google_recaptcha_site_key && $google_recaptcha_secret_key ) {
		?>
        <script>
            var google_recaptcha_site_key = '<?php echo $google_recaptcha_site_key ?>';
        </script>
		<?php
	}

}