<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action( 'carbon_fields_register_fields', 'crb_cfe_attach_theme_options' );
function crb_cfe_attach_theme_options() {
	$labels = array(
		'plural_name'   => 'items',
		'singular_name' => 'item',
	);
	Container::make( 'theme_options', "Associations settings" )
	         ->set_page_parent( 'edit.php?post_type=contact_form' )
	         ->add_fields( array(
		         Field::make( "complex", "association_post_types" )
		              ->setup_labels( $labels )
		              ->add_fields( array(
			              Field::make( "text", "custom_post_type" )->set_attribute( 'pattern', '^[a-z]+$' )
			                   ->set_help_text( 'The word is in Latin without spaces.' )
			                   ->set_required( true ),
		              ) ),
		         Field::make( "complex", "association_taxonomies" )
		              ->setup_labels( $labels )
		              ->add_fields( array(
			              Field::make( "text", "custom_taxonomy" )
			                   ->set_help_text( 'The word is in Latin without spaces.' )->set_required( true )->set_attribute( 'pattern', '^[a-z]+$' ),
		              ) ),
	         ) );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_contact_form' );
function crb_attach_in_contact_form() {
	$labels            = array(
		'plural_name'   => 'items',
		'singular_name' => 'item',
	);
	$label_rows        = array(
		'plural_name'   => 'rows',
		'singular_name' => 'row',
	);
	$label_columns     = array(
		'plural_name'   => 'columns',
		'singular_name' => 'column',
	);
	$label_fields      = array(
		'plural_name'   => 'fields',
		'singular_name' => 'field',
	);
	$association_items = get_association_items();
	Container::make( 'post_meta', 'Form body' )
	         ->show_on_post_type( 'contact_form' )
	         ->add_fields(
		         array(
			         Field::make( "complex", "contact_form_rows", "Rows" )
			              ->setup_labels( $label_rows )
			              ->set_layout( 'tabbed-vertical' )
			              ->set_required( true )
			              ->add_fields(
				              array(
					              Field::make( "complex", "columns", 'Columns' )
					                   ->setup_labels( $label_columns )
					                   ->set_max( 4 )
					                   ->set_layout( 'tabbed-horizontal' )
					                   ->set_required( true )
					                   ->add_fields(
						                   array(
							                   Field::make( 'select', 'column_width', __( 'Column width' ) )
							                        ->set_required( true )
							                        ->set_options( array(
								                        'full'    => '100%',
								                        'half'    => '50%',
								                        'third'   => '33%',
								                        'quarter' => '25%',
							                        ) ),
							                   Field::make( "complex", "field", 'Field' )
							                        ->setup_labels( $label_fields )->set_max( 1 )
							                        ->set_required( true )
							                        ->add_fields( 'text', 'Text field',
								                        array(
									                        Field::make( "checkbox", "field_required" ),
									                        Field::make( 'select', 'type', __( 'Type' ) )->set_required( true )
									                             ->set_options( array(
										                             'text'  => 'text',
										                             'email' => 'email',
										                             'tel'   => 'phone',
									                             ) ),
									                        Field::make( "text", "field_name" )->set_required( true ),
									                        Field::make( "text", "field_placeholder" )->set_required( true ),
									                        Field::make( "text", "field_css_class" ),
									                        Field::make( "text", "field_custom_regular_expression" )
									                             ->set_conditional_logic( array(
										                             'relation' => 'AND',
										                             array(
											                             'field'   => 'type',
											                             'value'   => 'text',
											                             'compare' => '=',
										                             )
									                             ) )
								                        )
							                        )
							                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
							                        ->add_fields( 'select', 'Select',
								                        array(
									                        Field::make( "checkbox", "field_required" )->set_width( 50 ),
									                        Field::make( "checkbox", "multiple" )->set_width( 50 ),
									                        Field::make( "text", "field_name" )->set_required( true ),
									                        Field::make( "text", "field_placeholder" )->set_required( true ),
									                        Field::make( 'select', 'value_type', __( 'Value type' ) )
									                             ->set_required( true )->set_width( 50 )
									                             ->set_options( array(
										                             'default'     => 'default',
										                             'association' => 'Association',
									                             ) ),
									                        Field::make( "complex", "values", 'Values' )
									                             ->setup_labels( $labels )
									                             ->set_conditional_logic( array(
										                             'relation' => 'AND',
										                             array(
											                             'field'   => 'value_type',
											                             'value'   => 'default',
											                             'compare' => '=',
										                             )
									                             ) )
									                             ->set_required( true )
									                             ->add_fields( array(
										                             Field::make( "text", "option_value" )->set_required( true ),
									                             ) ),
									                        Field::make( 'association', 'field_association', __( 'Association' ) )->set_required( true )
									                             ->set_types( $association_items )
									                             ->set_conditional_logic( array(
										                             'relation' => 'AND',
										                             array(
											                             'field'   => 'value_type',
											                             'value'   => 'association',
											                             'compare' => '=',
										                             )
									                             ) ),
									                        Field::make( "text", "field_css_class" ),
								                        )
							                        )
							                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
							                        ->add_fields( 'checkbox_radio', 'Radio/Checkbox',
								                        array(
									                        Field::make( "checkbox", "field_required" ),
									                        Field::make( "text", "field_name" )->set_required( true )->set_width( 50 ),
									                        Field::make( "checkbox", "field_name_hide" )->set_width( 50 ),
									                        Field::make( 'select', 'type', __( 'Type' ) )
									                             ->set_required( true )->set_width( 50 )
									                             ->set_options( array(
										                             'radio'    => 'Radiobutton',
										                             'checkbox' => 'Checkbox',
									                             ) ),
									                        Field::make( 'select', 'value_type', __( 'Value type' ) )
									                             ->set_required( true )->set_width( 50 )
									                             ->set_options( array(
										                             'default'     => 'default',
										                             'association' => 'Association',
									                             ) ),
									                        Field::make( "complex", "values", 'Values' )
									                             ->setup_labels( $labels )
									                             ->set_conditional_logic( array(
										                             'relation' => 'AND',
										                             array(
											                             'field'   => 'value_type',
											                             'value'   => 'default',
											                             'compare' => '=',
										                             )
									                             ) )
									                             ->set_required( true )
									                             ->add_fields( array(
										                             Field::make( "text", "option_value" )->set_required( true ),
									                             ) ),
									                        Field::make( 'association', 'field_association', __( 'Association' ) )->set_required( true )
									                             ->set_types( $association_items )
									                             ->set_conditional_logic( array(
										                             'relation' => 'AND',
										                             array(
											                             'field'   => 'value_type',
											                             'value'   => 'association',
											                             'compare' => '=',
										                             )
									                             ) ),
									                        Field::make( "text", "wrapper_css_class" ),
								                        )
							                        )
							                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
							                        ->add_fields( 'textarea', 'Textarea',
								                        array(
									                        Field::make( "checkbox", "field_required" ),
									                        Field::make( "text", "field_name" )->set_required( true ),
									                        Field::make( "text", "field_placeholder" )->set_required( true ),
									                        Field::make( "text", "field_css_class" ),
								                        )
							                        )
							                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
							                        ->add_fields( 'file', 'File field',
								                        array(
									                        Field::make( "checkbox", "field_required" ),
									                        Field::make( "checkbox", "multiple" ),
									                        Field::make( "text", "field_placeholder" )->set_required( true ),
									                        Field::make( "text", "field_css_class" ),
									                        Field::make( "text", "file_types" )->set_help_text( get_file_types_string() )
								                        )
							                        )
							                        ->add_fields( 'button', 'Button',
								                        array(
									                        Field::make( "text", "button_text" )->set_required( true ),
									                        Field::make( "select", "button_type" )
									                             ->set_options( array(
										                             'submit' => 'submit',
										                             'button' => 'button',
										                             'reset'  => 'reset',
									                             ) )
									                             ->set_required( true ),
									                        Field::make( "text", "button_css_class" ),
								                        )
							                        )
						                   )
					                   )
					                   ->set_header_template( 'Column <%- $_index + 1 %>' )
				              )
			              )
			              ->set_header_template( '
			                        Row <%- $_index + 1 %>
			                    ' )
		         )
	         );
	Container::make( 'post_meta', 'Answer' )
	         ->show_on_post_type( 'contact_form' )
	         ->add_fields(
		         array(
			         Field::make( "text", "contact_form_answer", "Answer" )->set_required( true )
		         )
	         );
	Container::make( 'post_meta', 'Receivers' )
	         ->show_on_post_type( 'contact_form' )
	         ->add_fields(
		         array(
			         Field::make( "text", "contact_form_subject", "Subject" ),
			         Field::make( "complex", "contact_form_emails", "Emails" )
			              ->add_fields(
				              array(
					              Field::make( "text", "email", "Email" )->set_required( true )->set_attribute( 'type', 'email' )
				              )
			              )
		         )
	         );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_cfe_results' );
function crb_attach_in_cfe_results() {
	$labels = array(
		'plural_name'   => 'items',
		'singular_name' => 'item',
	);
	Container::make( 'post_meta', 'Result' )
	         ->show_on_post_type( 'cfe_results' )
	         ->add_fields(
		         array(
			         Field::make( "text", "form_id", "Form ID" ),
			         Field::make( "complex", "cfe_results", "Result" )->setup_labels( $labels )
			              ->add_fields(
				              array(
					              Field::make( "text", "field_name", "Field name" )->set_width( 50 ),
					              Field::make( "text", "field_value", "Field value" )->set_width( 50 ),
				              ) ),
			         Field::make( "complex", "cfe_result_files", "Files" )->setup_labels( $labels )
			              ->add_fields(
				              array(
					              Field::make( "text", "file_url", "File url" )
				              ) )
		         )
	         );
}

add_action( 'after_setup_theme', 'crb_cfe_load' );
function crb_cfe_load() {
	if ( ! class_exists( 'Carbon_Fields\Carbon_Fields' ) ) {
		require_once(CFE__PLUGIN_DIR . 'vendor/autoload.php' );
		\Carbon_Fields\Carbon_Fields::boot();
	}
}