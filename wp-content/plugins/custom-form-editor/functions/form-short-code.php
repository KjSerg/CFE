<?php

add_action( 'admin_notices', function () {
	$post_id = $_GET['post'] ?? ( $_POST['post_ID'] ?? '' );
	if ( ! $post_id ) {
		return;
	}
	$post_type = get_post_type( $post_id );
	if ( $post_type === 'contact_form' ) {
		echo '<div id="' . CFE__PLUGIN_NAME . '-short-code-notice" class="notice" style="">Short code: [custom-form id="' . $post_id . '"]</div>';
	}

} );