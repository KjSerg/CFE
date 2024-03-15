<?php

function custom_shortcode_form_init( $atts ) {
	$id        = $atts['id'] ?? '';

	if ( $id && get_post( $id ) && get_post_type( $id ) == 'contact_form' ) {
		$id = (int) $id;
		return get_form_html( $id );
	}

	return '';
}

add_shortcode( 'custom-form', 'custom_shortcode_form_init' );