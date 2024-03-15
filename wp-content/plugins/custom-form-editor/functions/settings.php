<?php
add_filter( 'wp_mail_content_type', 'cfe_content_type' );

function cfe_content_type( $content_type ) {
	return 'text/html';
}

add_filter( 'wp_mail_charset', 'cfe_mail_charset' );

function cfe_mail_charset( $content_type ) {
	return 'utf-8';
}


add_action( 'admin_menu', 'add_cfe_menu_bubble' );
function add_cfe_menu_bubble() {
	global $menu;
	$count1 = wp_count_posts( 'cfe_results' )->pending;
	if ( $count1 ) {
		foreach ( $menu as $key => $value ) {
			if ( $menu[ $key ][2] == 'edit.php?post_type=cfe_results' ) {
				$menu[ $key ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $count1 . '</span></span>';
				break;
			}
		}
	}
}