<?php
function register_my_cpts_forms() {

	/**
	 * Post Type: Forms.
	 */

	$labels = [
		"name" => esc_html__( "Forms", CFE__PLUGIN_NAME ),
		"singular_name" => esc_html__( "Form", CFE__PLUGIN_NAME ),
		"menu_name" => esc_html__( "Forms", CFE__PLUGIN_NAME ),
		"all_items" => esc_html__( "All forms", CFE__PLUGIN_NAME ),
		"add_new" => esc_html__( "Add", CFE__PLUGIN_NAME ),
		"add_new_item" => esc_html__( "Add new", CFE__PLUGIN_NAME ),
		"edit_item" => esc_html__( "Edit", CFE__PLUGIN_NAME ),
		"new_item" => esc_html__( "New", CFE__PLUGIN_NAME ),
		"view_item" => esc_html__( "_", CFE__PLUGIN_NAME ),
		"view_items" => esc_html__( "_", CFE__PLUGIN_NAME ),
		"search_items" => esc_html__( "Search", CFE__PLUGIN_NAME ),
		"not_found" => esc_html__( "Not found", CFE__PLUGIN_NAME ),
		"not_found_in_trash" => esc_html__( "Not found in trash", CFE__PLUGIN_NAME ),
	];

	$args = [
		"label" => esc_html__( "Forms", CFE__PLUGIN_NAME ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "contact_form", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-forms",
		"supports" => [ "title" ],
		"show_in_graphql" => false,
	];

	register_post_type( "contact_form", $args );

	$labels_form_list = [
		"name" => esc_html__( "Results", CFE__PLUGIN_NAME ),
		"singular_name" => esc_html__( "Result", CFE__PLUGIN_NAME ),
		"menu_name" => esc_html__( "Results", CFE__PLUGIN_NAME ),
		"all_items" => esc_html__( "All results", CFE__PLUGIN_NAME ),
		"add_new" => esc_html__( "Add", CFE__PLUGIN_NAME ),
		"add_new_item" => esc_html__( "Add new", CFE__PLUGIN_NAME ),
		"edit_item" => esc_html__( "Edit", CFE__PLUGIN_NAME ),
		"new_item" => esc_html__( "New", CFE__PLUGIN_NAME ),
		"view_item" => esc_html__( "_", CFE__PLUGIN_NAME ),
		"view_items" => esc_html__( "_", CFE__PLUGIN_NAME ),
		"search_items" => esc_html__( "Search", CFE__PLUGIN_NAME ),
		"not_found" => esc_html__( "Not found", CFE__PLUGIN_NAME ),
		"not_found_in_trash" => esc_html__( "Not found in trash", CFE__PLUGIN_NAME ),
	];

	$args_form_list = [
		"label" => esc_html__( "Results", CFE__PLUGIN_NAME ),
		"labels" => $labels_form_list,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => false,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "cfe_results", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-database",
		"supports" => [ "title" ],
		"show_in_graphql" => false,
	];

	register_post_type( "cfe_results", $args_form_list );
}

add_action( 'init', 'register_my_cpts_forms' );
