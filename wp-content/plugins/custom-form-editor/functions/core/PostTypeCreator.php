<?php

class PostTypeCreator {
	private static ?self $instance = null;

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_my_cpts_forms' ] );
	}

	public function register_my_cpts_forms(): void {

		/**
		 * Post Type: Forms.
		 */

		$labels = [
			"name"               => esc_html__( "Forms", 'custom-form-editor' ),
			"singular_name"      => esc_html__( "Form", 'custom-form-editor' ),
			"menu_name"          => esc_html__( "Forms", 'custom-form-editor' ),
			"all_items"          => esc_html__( "All forms", 'custom-form-editor' ),
			"add_new"            => esc_html__( "Add", 'custom-form-editor' ),
			"add_new_item"       => esc_html__( "Add new", 'custom-form-editor' ),
			"edit_item"          => esc_html__( "Edit", 'custom-form-editor' ),
			"new_item"           => esc_html__( "New", 'custom-form-editor' ),
			"view_item"          => esc_html__( "_", 'custom-form-editor' ),
			"view_items"         => esc_html__( "_", 'custom-form-editor' ),
			"search_items"       => esc_html__( "Search", 'custom-form-editor' ),
			"not_found"          => esc_html__( "Not found", 'custom-form-editor' ),
			"not_found_in_trash" => esc_html__( "Not found in trash", 'custom-form-editor' ),
		];

		$args = [
			"label"                 => esc_html__( "Forms", 'custom-form-editor' ),
			"labels"                => $labels,
			"description"           => "",
			"public"                => true,
			"publicly_queryable"    => false,
			"show_ui"               => true,
			"show_in_rest"          => true,
			"rest_base"             => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace"        => "wp/v2",
			"has_archive"           => false,
			"show_in_menu"          => true,
			"show_in_nav_menus"     => false,
			"delete_with_user"      => false,
			"exclude_from_search"   => false,
			"capability_type"       => "post",
			"map_meta_cap"          => true,
			"hierarchical"          => false,
			"can_export"            => true,
			"rewrite"               => [ "slug" => "contact_form", "with_front" => true ],
			"query_var"             => true,
			"menu_icon"             => "dashicons-forms",
			"supports"              => [ "title" ],
			"show_in_graphql"       => false,
		];

		register_post_type( "contact_form", $args );

		$labels_form_list = [
			"name"               => esc_html__( "Results", 'custom-form-editor' ),
			"singular_name"      => esc_html__( "Result", 'custom-form-editor' ),
			"menu_name"          => esc_html__( "Results", 'custom-form-editor' ),
			"all_items"          => esc_html__( "All results", 'custom-form-editor' ),
			"add_new"            => esc_html__( "Add", 'custom-form-editor' ),
			"add_new_item"       => esc_html__( "Add new", 'custom-form-editor' ),
			"edit_item"          => esc_html__( "Edit", 'custom-form-editor' ),
			"new_item"           => esc_html__( "New", 'custom-form-editor' ),
			"view_item"          => esc_html__( "_", 'custom-form-editor' ),
			"view_items"         => esc_html__( "_", 'custom-form-editor' ),
			"search_items"       => esc_html__( "Search", 'custom-form-editor' ),
			"not_found"          => esc_html__( "Not found", 'custom-form-editor' ),
			"not_found_in_trash" => esc_html__( "Not found in trash", 'custom-form-editor' ),
		];

		$args_form_list = [
			"label"                 => esc_html__( "Results", 'custom-form-editor' ),
			"labels"                => $labels_form_list,
			"description"           => "",
			"public"                => true,
			"publicly_queryable"    => false,
			"show_ui"               => true,
			"show_in_rest"          => true,
			"rest_base"             => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"rest_namespace"        => "wp/v2",
			"has_archive"           => false,
			"show_in_menu"          => true,
			"show_in_nav_menus"     => false,
			"delete_with_user"      => false,
			"exclude_from_search"   => false,
			"capability_type"       => "post",
			"map_meta_cap"          => true,
			"hierarchical"          => false,
			"can_export"            => true,
			"rewrite"               => [ "slug" => "cfe_results", "with_front" => true ],
			"query_var"             => true,
			"menu_icon"             => "dashicons-database",
			"supports"              => [ "title" ],
			"show_in_graphql"       => false,
		];

		register_post_type( "cfe_results", $args_form_list );
	}
}

PostTypeCreator::get_instance();