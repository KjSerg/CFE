<?php
/*
Plugin Name: Custom Form Editor
Description: Візуальний редактор форм на основі carbonfields
Version: 1.0
Author: Каланджій Сергій
Author URI: https://web-mosaica.art/
Plugin URI: https://github.com/KjSerg/contacts-form-editor
*/

define( 'CFE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CFE__SITE_URL', site_url() );
define( 'CFE__ASSETS_URL', CFE__SITE_URL . '/wp-content/plugins/custom-form-editor/assets' );
define( 'CFE__PLUGIN_NAME', 'custom-form-editor' );

function load_custom_form_editor_textdomain() {
	load_plugin_textdomain( 'custom-form-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'load_custom_form_editor_textdomain' );


require_once( CFE__PLUGIN_DIR . 'functions/form-post-type.php' );
require_once( CFE__PLUGIN_DIR . 'functions/form-short-code.php' );
require_once( CFE__PLUGIN_DIR . 'functions/carbonfields-init.php' );
require_once( CFE__PLUGIN_DIR . 'functions/helpers.php' );
require_once( CFE__PLUGIN_DIR . 'functions/include-assets.php' );
require_once( CFE__PLUGIN_DIR . 'functions/short-code-init.php' );
require_once( CFE__PLUGIN_DIR . 'functions/ajax-functions.php' );
require_once( CFE__PLUGIN_DIR . 'functions/settings.php' );
require_once( CFE__PLUGIN_DIR . 'views/init.php' );

add_action( 'admin_notices', function () {
	if ( !class_exists( 'Carbon_Fields\Carbon_Fields' ) ) {
		echo '<div id="' . CFE__PLUGIN_NAME . '-notice1" class="notice" style="">Carbon_Fields not active</div>';
	}
} );

