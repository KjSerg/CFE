<?php
function cfe_scripts() {
	if ( ! wp_script_is( 'fancybox', 'enqueued' ) ) {
		wp_enqueue_style( 'fancybox', 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css', array(), '1.0' );
	}
	if ( ! wp_script_is( 'selectric', 'enqueued' ) ) {
		wp_enqueue_style( 'selectric', CFE__ASSETS_URL . '/css/selectric.css', array(), '1.0' );
	}
	wp_enqueue_style( 'cfe-main', CFE__ASSETS_URL . '/css/cfe.css', array(), '1.0' );
	if ( ! wp_script_is( 'selectric', 'enqueued' ) ) {
		wp_enqueue_script( 'selectric', CFE__ASSETS_URL . '/js/jquery.selectric.min.js', array( 'jquery' ), '1.0', true );
	}
	if ( ! wp_script_is( 'fancybox', 'enqueued' ) ) {
		wp_enqueue_script( 'fancybox', 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js', array(), '1.0', true );
	}
	$google_recaptcha_site_key   = carbon_get_theme_option( 'google_recaptcha_site_key' );
	$google_recaptcha_secret_key = carbon_get_theme_option( 'google_recaptcha_secret_key' );
	if ( $google_recaptcha_site_key && $google_recaptcha_secret_key ) {
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . $google_recaptcha_site_key, array(), '', true );
	}

	wp_enqueue_script( 'cfe-scripts', CFE__ASSETS_URL . '/js/cfe.js', array(), '1.0', true );

	wp_localize_script( 'ajax-script', 'AJAX', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'cfe_scripts' );

function cfe_enqueue_admin_scripts() {

	wp_enqueue_style( 'custom-admin-css', CFE__ASSETS_URL . '/css/admin.css', array(), '1.0' );

	wp_enqueue_script( 'custom-admin-scripts', CFE__ASSETS_URL . '/js/admin.js', array( 'jquery' ), '1.0', true );

}

add_action( 'admin_enqueue_scripts', 'cfe_enqueue_admin_scripts' );