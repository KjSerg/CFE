<?php
/*
Plugin Name: Custom Form Editor
Description: Візуальний редактор форм на основі carbonfields
Version: 1.1
Author: Каланджій Сергій
Author URI: https://web-mosaica.art/
Plugin URI: https://github.com/KjSerg/contacts-form-editor
*/

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


