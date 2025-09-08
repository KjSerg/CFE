<?php


class CFESettings {

	public static function init_short_code(): void {
		add_shortcode( 'custom-form', function ( $atts ) {
			$id = $atts['id'] ?? 0;
			if ( ! $id ) {
				return '';
			}
			$id = (int) $id;
			if ( ! get_post( $id ) ) {
				return '';
			}
			if ( get_post_type( $id ) !== 'contact_form' ) {
				return '';
			}

			return \CFE\FormRenderer::render( $id );
		} );
	}

	public static function add_copied_shortcode(): void {
		add_action( 'admin_notices', function () {
			$post_id = $_GET['post'] ?? ( $_POST['post_ID'] ?? '' );
			if ( ! $post_id ) {
				return;
			}
			$post_type = get_post_type( $post_id );
			if ( $post_type !== 'contact_form' ) {
				return;
			}
			$code = "[custom-form id='$post_id']";
			echo '<div id="' . CFE__PLUGIN_NAME . '-short-code-notice" class="notice cfe-short-code-info copy-on-click" data-value="' . $code . '" style="">Short code: ' . $code . '</div>';

		} );
	}

	public static function wp_mail_charset(): void {
		add_filter( 'wp_mail_charset', function () {
			return 'UTF-8';
		} );
	}

	public static function wp_mail_content_type(): void {
		add_filter( 'wp_mail_content_type', function () {
			return 'text/html';
		} );
	}

	public static function add_cfe_menu_bubble(): void {
		add_action( 'admin_menu', function () {
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
		} );
	}
}

CFESettings::wp_mail_charset();
CFESettings::wp_mail_content_type();
CFESettings::add_cfe_menu_bubble();
CFESettings::add_copied_shortcode();
CFESettings::init_short_code();