<?php
/*
Plugin Name: Custom WebP Converter
Description: Converts uploaded images to webp format and does not create copies, i.e. saves server memory. The "Custom WebP Converter" plugin requires that the GD library with WebP support is enabled on your server.
Version: 1.0
Author: Каланджій Сергій
Author URI: https://github.com/KjSerg/
Plugin URI: https://github.com/KjSerg/CFE/tree/main/wp-content/plugins/custom-webp-converter
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.5
Text Domain: custom-webp-converter
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Перевірка мінімальної версії PHP
$cwc_min_php_version = '8.0';
if ( version_compare( PHP_VERSION, $cwc_min_php_version, '<' ) ) {
	add_action( 'admin_notices', function () use ( $cwc_min_php_version ) {
		$message = sprintf(
			esc_html__( 'Plugin "Custom WebP Converter" requires PHP version %1$s or newer. Your current version is %2$s. The plugin has been deactivated.', 'custom-webp-converter' ),
			$cwc_min_php_version,
			PHP_VERSION
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	return;
}

// Перевірка мінімальної версії WordPress
global $wp_version;
$cwc_min_wp_version = '6.0';
if ( version_compare( $wp_version, $cwc_min_wp_version, '<' ) ) {
	add_action( 'admin_notices', function () use ( $cwc_min_wp_version, $wp_version ) {
		$message = sprintf(
			esc_html__( 'Plugin "Custom WebP Converter" requires WordPress version %1$s or newer. Your current version is %2$s. The plugin has been deactivated.', 'custom-webp-converter' ),
			$cwc_min_wp_version,
			$wp_version
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	return;
}

// <-- НОВА ПЕРЕВІРКА -->
// Перевірка наявності бібліотеки GD з підтримкою WebP
if ( ! function_exists( 'imagewebp' ) ) {
	add_action( 'admin_notices', function () {
		$message = sprintf(
			esc_html__( 'Plugin "Custom WebP Converter" requires the GD library with WebP support to be enabled on your server. The function %1$s was not found. Please contact your hosting provider. The plugin has been deactivated.', 'custom-webp-converter' ),
			'<code>imagewebp()</code>'
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	return;
}
// <-- КІНЕЦЬ НОВОЇ ПЕРЕВІРКИ -->


define( 'CWC__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CWC__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CWC__SITE_URL', site_url() );
define( 'CWC__PLUGIN_NAME', 'custom-webp-converter' );
define( 'CWC_MAX_PIXEL_THRESHOLD', 35000000 );
// Підключення основного файлу ініціалізації
require_once( CWC__PLUGIN_DIR . 'Initializer.php' );
require_once( CWC__PLUGIN_DIR . 'enqueue_scripts.php' );
