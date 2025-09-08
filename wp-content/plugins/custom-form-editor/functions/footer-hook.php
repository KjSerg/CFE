<?php
add_action( 'wp_footer', 'cfe_footer_custom_code_improved' );

function cfe_footer_custom_code_improved(): void {
	$data                      = [
		'admin_ajax' => admin_url( 'admin-ajax.php' ),
	];
	$google_recaptcha_site_key = carbon_get_theme_option( 'google_recaptcha_site_key' );

	if ( ! empty( $google_recaptcha_site_key ) ) {
		$data['google_recaptcha_site_key'] = $google_recaptcha_site_key;
		$data['google_recaptcha_script']   = 'https://www.google.com/recaptcha/api.js';
	}
	?>
    <script>
        var cfe_data = <?php echo wp_json_encode( $data ); ?>;
    </script>
	<?php
}