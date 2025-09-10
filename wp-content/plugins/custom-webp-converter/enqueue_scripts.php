<?php
function CWC_enqueue_media_scripts( $hook_suffix ): void {

	wp_enqueue_script(
		'cwc-media-upload-listener',
		CWC__PLUGIN_URL . 'assets/js/admin-script.js',
		array( 'jquery', 'media-models' ),
		'1.0.0',
		true
	);
	wp_localize_script('cwc-media-upload-listener', 'cwc_params', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('cwc-ajax-nonce'),
		'action'   => 'cwc_get_last_error'
	]);
}

add_action( 'admin_enqueue_scripts', 'CWC_enqueue_media_scripts' );