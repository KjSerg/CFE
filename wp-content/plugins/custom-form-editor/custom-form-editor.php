<?php
/*
Plugin Name: Custom Form Editor
Description: Візуальний редактор форм на основі carbonfields. Відкритий код для кращої кстомізації стилів і HTML.
Version: 1.1
Author: Каланджій Сергій
Author URI: https://web-mosaica.art/
Plugin URI: https://github.com/KjSerg/contacts-form-editor
Requires at least: 6.0
Requires PHP: 8.0
Tested up to: 6.5
Text Domain: custom-form-editor
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfe_min_php_version = '8.0';
$cfe_min_wp_version  = '6.0';
if ( version_compare( PHP_VERSION, $cfe_min_php_version, '<' ) ) {
	add_action( 'admin_notices', function () use ( $cfe_min_php_version ) {
		$message = sprintf(
			esc_html__( 'Plugin "Custom Form Editor" requires PHP version %1$s or newer. Your current version is %2$s. The plugin has been deactivated.', 'custom-form-editor' ),
			$cfe_min_php_version,
			PHP_VERSION
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	return;
}
global $wp_version;
if ( version_compare( $wp_version, $cfe_min_wp_version, '<' ) ) {
	add_action( 'admin_notices', function () use ( $cfe_min_wp_version, $wp_version ) {
		$message = sprintf(
			esc_html__( 'Plugin "Custom Form Editor" requires WordPress version %1$s or newer. Your current version is %2$s. The plugin has been deactivated.', 'custom-form-editor' ),
			$cfe_min_wp_version,
			$wp_version
		);
		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} );

	return;
}

define( 'CFE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFE__SITE_URL', site_url() );
define( 'CFE__ASSETS_URL', CFE__SITE_URL . '/wp-content/plugins/custom-form-editor/assets' );
define( 'CFE__PLUGIN_NAME', 'custom-form-editor' );

function load_custom_form_editor_textdomain(): void {
	load_plugin_textdomain( 'custom-form-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'load_custom_form_editor_textdomain' );


require_once( CFE__PLUGIN_DIR . 'functions/core/PostTypeCreator.php' );
require_once( CFE__PLUGIN_DIR . 'functions/core/CarbonFieldsInitializer.php' );
require_once( CFE__PLUGIN_DIR . 'functions/helpers.php' );
require_once( CFE__PLUGIN_DIR . 'functions/include-assets.php' );
require_once( CFE__PLUGIN_DIR . 'functions/core/Ajax.php' );
require_once( CFE__PLUGIN_DIR . 'functions/footer-hook.php' );
require_once( CFE__PLUGIN_DIR . 'views/Field.php' );
require_once( CFE__PLUGIN_DIR . 'views/Form.php' );
require_once( CFE__PLUGIN_DIR . 'functions/core/CFESettings.php' );
require_once( CFE__PLUGIN_DIR . 'functions/Telegram.php' );


