<?php


use Carbon_Fields\Container;
use Carbon_Fields\Field;

class CarbonFieldsInitializer {

	private array $labels;
	private array $label_rows;
	private array $label_columns;
	private array $label_fields;

	private function __construct() {
		$this->initialize_labels();
		$this->initialize_actions();
	}

	private static ?self $instance = null;

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	private function initialize_actions(): void {
		add_action( 'after_setup_theme', [ $this, 'load_carbon_fields' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'crb_cfe_attach_options' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'attach_fields_to_user' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'crb_attach_in_contact_form' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'crb_attach_in_cfe_results' ] );
	}

	public function crb_attach_in_cfe_results(): void {
		$labels = $this->labels;
		Container::make( 'post_meta', esc_html__( 'Result', 'custom-form-editor' ) )
		         ->where( 'post_type', '=', 'cfe_results' )
		         ->add_fields(
			         array(
				         Field::make( "text", "form_id", esc_html__( 'Form ID', 'custom-form-editor' ) ),
				         Field::make( "complex", "cfe_results", esc_html__( 'Result', 'custom-form-editor' ) )->setup_labels( $labels )
				              ->add_fields(
					              array(
						              Field::make( "text", "field_name", esc_html__( 'Field name', 'custom-form-editor' ) )->set_width( 50 ),
						              Field::make( "text", "field_value", esc_html__( 'Field value', 'custom-form-editor' ) )->set_width( 50 ),
					              ) ),
				         Field::make( "complex", "cfe_result_files", esc_html__( 'Files', 'custom-form-editor' ) )->setup_labels( $labels )
				              ->add_fields(
					              array(
						              Field::make( "text", "file_url", esc_html__( 'File url', 'custom-form-editor' ) )
					              ) )
			         )
		         );
	}

	public function crb_attach_in_contact_form(): void {
		$association_items         = get_association_items();
		$google_recaptcha_site_key = carbon_get_theme_option( 'google_recaptcha_site_key' ) ?: 'enter Google Recaptcha Site Key';
		Container::make( 'post_meta', 'Recaptcha' )
		         ->where( 'post_type', '=', 'contact_form' )
		         ->add_fields( array(
			         Field::make( "separator", "recaptcha_help", ' Select "Custom HTML" field, add HTML:' )
			              ->set_help_text( htmlspecialchars( '<div class="g-recaptcha" data-sitekey="' . $google_recaptcha_site_key . '"></div>', ENT_QUOTES ) ),
		         ) );
		Container::make( 'post_meta', esc_html__( 'Form body', 'custom-form-editor' ) )
		         ->where( 'post_type', '=', 'contact_form' )
		         ->add_tab( esc_html__( 'Form body', 'custom-form-editor' ),
			         array(

				         Field::make( "complex", "contact_form_rows", esc_html__( 'Rows', 'custom-form-editor' ) )
				              ->setup_labels( $this->label_rows )
				              ->set_layout( 'tabbed-vertical' )
				              ->set_required( true )
				              ->add_fields(
					              array(
						              Field::make( "complex", "columns", esc_html__( 'Columns', 'custom-form-editor' ) )
						                   ->setup_labels( $this->label_columns )
						                   ->set_max( 4 )
						                   ->set_layout( 'tabbed-horizontal' )
						                   ->set_required( true )
						                   ->add_fields(
							                   array(
								                   Field::make( 'text', 'column_title', __( 'Column title', 'custom-form-editor' ) ),
								                   Field::make( 'select', 'column_width', __( 'Column width', 'custom-form-editor' ) )
								                        ->set_required( true )
								                        ->set_options( array(
									                        'full'           => '100%',
									                        'half'           => '50%',
									                        'third'          => '33%',
									                        'quarter'        => '25%',
									                        'three-quarters' => '75%',
									                        'two-thirds'     => '66%',
								                        ) ),
								                   Field::make( "complex", "field", esc_html__( 'Field', 'custom-form-editor' ) )
								                        ->setup_labels( $this->label_fields )->set_max( 1 )
								                        ->set_required( true )
								                        ->add_fields( 'text', esc_html__( 'Text field', 'custom-form-editor' ),
									                        array(
										                        Field::make( "checkbox", "field_required", esc_html__( 'field required', 'custom-form-editor' ) ),
										                        Field::make( 'select', 'type', __( 'Type', 'custom-form-editor' ) )->set_required( true )
										                             ->set_options( array(
											                             'text'  => 'text',
											                             'email' => 'email',
											                             'tel'   => 'phone',
										                             ) ),
										                        Field::make( "text", "field_name", esc_html__( 'field name', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_placeholder", esc_html__( 'field placeholder', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_css_class", esc_html__( 'field css class', 'custom-form-editor' ) ),
										                        Field::make( "text", "field_custom_regular_expression", esc_html__( 'field custom regular expression', 'custom-form-editor' ) )
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
								                        ->add_fields( 'select', esc_html__( 'Select', 'custom-form-editor' ),
									                        array(
										                        Field::make( "checkbox", "field_required", esc_html__( 'field required', 'custom-form-editor' ) )->set_width( 50 ),
										                        Field::make( "checkbox", "multiple", esc_html__( 'multiple', 'custom-form-editor' ) )->set_width( 50 ),
										                        Field::make( "text", "field_name", esc_html__( 'field name', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_placeholder", esc_html__( 'field placeholder', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( 'select', 'value_type', __( 'Value type', 'custom-form-editor' ) )
										                             ->set_required( true )->set_width( 50 )
										                             ->set_options( array(
											                             'default'     => 'default',
											                             'association' => 'Association',
										                             ) ),
										                        Field::make( "complex", "values", esc_html__( 'Values', 'custom-form-editor' ) )
										                             ->setup_labels( $this->labels )
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
											                             Field::make( "text", "option_value", esc_html__( 'option value', 'custom-form-editor' ) )->set_required( true ),
										                             ) ),
										                        Field::make( 'association', 'field_association', __( 'Association', 'custom-form-editor' ) )->set_required( true )
										                             ->set_types( $association_items )
										                             ->set_conditional_logic( array(
											                             'relation' => 'AND',
											                             array(
												                             'field'   => 'value_type',
												                             'value'   => 'association',
												                             'compare' => '=',
											                             )
										                             ) ),
										                        Field::make( "text", "field_css_class", esc_html__( 'field css class', 'custom-form-editor' ) ),
									                        )
								                        )
								                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
								                        ->add_fields( 'checkbox_radio', 'Radio/Checkbox',
									                        array(
										                        Field::make( "checkbox", "field_required", esc_html__( 'field required', 'custom-form-editor' ) ),
										                        Field::make( "text", "field_name", esc_html__( 'field name', 'custom-form-editor' ) )->set_required( true )->set_width( 50 ),
										                        Field::make( "checkbox", "field_name_hide", esc_html__( 'field name hide', 'custom-form-editor' ) )->set_width( 50 ),
										                        Field::make( 'select', 'type', __( 'Type', 'custom-form-editor' ) )
										                             ->set_required( true )->set_width( 50 )
										                             ->set_options( array(
											                             'radio'    => 'Radiobutton',
											                             'checkbox' => 'Checkbox',
										                             ) ),
										                        Field::make( 'select', 'value_type', __( 'Value type', 'custom-form-editor' ) )
										                             ->set_required( true )->set_width( 50 )
										                             ->set_options( array(
											                             'default'     => 'default',
											                             'association' => 'Association',
										                             ) ),
										                        Field::make( "complex", "values", esc_html__( 'Values', 'custom-form-editor' ) )
										                             ->setup_labels( $this->labels )
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
											                             Field::make( "text", "option_value", esc_html__( 'option value', 'custom-form-editor' ) )->set_required( true ),
										                             ) ),
										                        Field::make( 'association', 'field_association', __( 'Association', 'custom-form-editor' ) )->set_required( true )
										                             ->set_types( $association_items )
										                             ->set_conditional_logic( array(
											                             'relation' => 'AND',
											                             array(
												                             'field'   => 'value_type',
												                             'value'   => 'association',
												                             'compare' => '=',
											                             )
										                             ) ),
										                        Field::make( "text", "wrapper_css_class", esc_html__( 'wrapper css class', 'custom-form-editor' ) ),
									                        )
								                        )
								                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
								                        ->add_fields( 'textarea', esc_html__( 'Textarea', 'custom-form-editor' ),
									                        array(
										                        Field::make( "checkbox", "field_required", esc_html__( 'field required', 'custom-form-editor' ) ),
										                        Field::make( "text", "field_name", esc_html__( 'field name', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_placeholder", esc_html__( 'field placeholder', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_css_class", esc_html__( 'field css class', 'custom-form-editor' ) ),
									                        )
								                        )
								                        ->set_header_template( 'crb_cfe_complex_field_header_template' )
								                        ->add_fields( 'file', esc_html__( 'File field', 'custom-form-editor' ),
									                        array(
										                        Field::make( "checkbox", "field_required", esc_html__( 'field required', 'custom-form-editor' ) ),
										                        Field::make( "checkbox", "multiple", esc_html__( 'multiple', 'custom-form-editor' ) ),
										                        Field::make( "text", "field_placeholder", esc_html__( 'field placeholder', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "text", "field_css_class", esc_html__( 'field css class', 'custom-form-editor' ) ),
										                        Field::make( "text", "file_types", esc_html__( 'file types', 'custom-form-editor' ) )->set_help_text( get_file_types_string() )
									                        )
								                        )
								                        ->add_fields( 'button', esc_html__( 'Button', 'custom-form-editor' ),
									                        array(
										                        Field::make( "text", "button_text", esc_html__( 'Button text', 'custom-form-editor' ) )->set_required( true ),
										                        Field::make( "select", "button_type", esc_html__( 'Button type', 'custom-form-editor' ) )
										                             ->set_options( array(
											                             'submit' => 'submit',
											                             'button' => 'button',
											                             'reset'  => 'reset',
										                             ) )
										                             ->set_required( true ),
										                        Field::make( "text", "button_css_class", esc_html__( 'button css class', 'custom-form-editor' ) ),
									                        )
								                        )
								                        ->add_fields( 'html', esc_html__( 'Custom HTML', 'custom-form-editor' ),
									                        array(
										                        Field::make( "textarea", "html" ),
									                        )
								                        )
							                   )
						                   )
						                   ->set_header_template( 'crb_cfe_complex_column_header_template' )
					              )
				              )
				              ->set_header_template( 'crb_cfe_complex_rows_header_template' )
			         )
		         )
		         ->add_tab( esc_html__( 'Settings', 'custom-form-editor' ),
			         array(
				         Field::make( "text", "contact_form_css_class", esc_html__( 'contact form css class', 'custom-form-editor' ), ),
			         )
		         );
		Container::make( 'post_meta', esc_html__( 'Answer', 'custom-form-editor' ) )
		         ->where( 'post_type', '=', 'contact_form' )
		         ->add_fields(
			         array(
				         Field::make( "text", "contact_form_answer", esc_html__( 'Answer', 'custom-form-editor' ) )->set_required( true )
			         )
		         );
		Container::make( 'post_meta', esc_html__( 'Receivers', 'custom-form-editor' ) )
		         ->where( 'post_type', '=', 'contact_form' )
		         ->add_fields(
			         array(
				         Field::make( "text", "contact_form_subject", esc_html__( 'Subject', 'custom-form-editor' ) ),
				         Field::make( "complex", "contact_form_emails", "Emails" )
				              ->add_fields(
					              array(
						              Field::make( "text", "email", "Email" )->set_required( true )->set_attribute( 'type', 'email' )
					              )
				              ),
				         Field::make( "complex", "contact_form_telegram_chats", "Telegram chats ids" )
				              ->add_fields(
					              array(
						              Field::make( "text", "chat_id" )->set_required( true )
					              )
				              )->set_help_text( get_telegram_help_text_cfe() )
			         )
		         );
	}

	public function attach_fields_to_user(): void {
		Container::make( 'user_meta', 'Telegram' )
		         ->add_fields( array(
			         Field::make( 'text', 'telegram_chat_id' ),
		         ) );
	}

	public function crb_cfe_attach_options(): void {
		Container::make( 'theme_options', esc_html__( "Associations settings", 'custom-form-editor' ) )
		         ->set_page_parent( 'edit.php?post_type=contact_form' )
		         ->add_fields( array(
			         Field::make( "complex", "association_post_types", esc_html__( "Associations settings", 'custom-form-editor' ) )
			              ->setup_labels( $this->labels )
			              ->add_fields( array(
				              Field::make( "text", "custom_post_type", esc_html__( "Custom post type", 'custom-form-editor' ) )
				                   ->set_attribute( 'pattern', '^[a-z]+$' )
				                   ->set_help_text( 'The word is in Latin without spaces.' )
				                   ->set_required( true ),
			              ) ),
			         Field::make( "complex", "association_taxonomies", esc_html__( "Association taxonomies", 'custom-form-editor' ) )
			              ->setup_labels( $this->labels )
			              ->add_fields( array(
				              Field::make( "text", "custom_taxonomy", esc_html__( "Custom taxonomy", 'custom-form-editor' ) )
				                   ->set_help_text( 'The word is in Latin without spaces.' )->set_required( true )->set_attribute( 'pattern', '^[a-z]+$' ),
			              ) ),
		         ) );
		Container::make( 'theme_options', "Google recaptcha_v2" )
		         ->set_page_parent( 'edit.php?post_type=contact_form' )
		         ->add_fields( array(
			         Field::make( "text", "google_recaptcha_site_key" ),
			         Field::make( "text", "google_recaptcha_secret_key" ),
		         ) );
		Container::make( 'theme_options', "Assets settings" )
		         ->set_page_parent( 'edit.php?post_type=contact_form' )
		         ->add_fields( array(
			         Field::make( "checkbox", "style_off", esc_html__( "Disable default styles?", 'custom-form-editor' ) ),
			         Field::make( "checkbox", "scripts_off", esc_html__( "Disable default JS?", 'custom-form-editor' ) ),
			         Field::make( "checkbox", "selectric_off", esc_html__( "Disable selectric.js?", 'custom-form-editor' ) )
			              ->set_help_text( 'If your theme uses this library, connect it to avoid conflicts.' ),
			         Field::make( "checkbox", "fancybox_off", esc_html__( "Disable fancybox.js?", 'custom-form-editor' ) )
			              ->set_help_text( 'If your theme uses this library, connect it to avoid conflicts.' ),
		         ) );
		Container::make( 'theme_options', "Telegram settings" )
		         ->set_page_parent( 'edit.php?post_type=contact_form' )
		         ->add_fields( array(
			         Field::make( "text", "cfe_telegram_api_key" ),
			         Field::make( "text", "cfe_telegram_bot_name" ),
		         ) );
	}

	private function initialize_labels(): void {
		$this->labels = [
			'plural_name'   => __( 'items' ),
			'singular_name' => __( 'item' ),
		];

		$this->label_rows    = array(
			'plural_name'   => esc_html__( 'Rows', 'custom-form-editor' ),
			'singular_name' => esc_html__( 'row', 'custom-form-editor' ),
		);
		$this->label_columns = array(
			'plural_name'   => esc_html__( 'Columns', 'custom-form-editor' ),
			'singular_name' => esc_html__( 'column', 'custom-form-editor' )
		);
		$this->label_fields  = array(
			'plural_name'   => esc_html__( 'fields', 'custom-form-editor' ),
			'singular_name' => esc_html__( 'field', 'custom-form-editor' ),
		);
	}

	public function load_carbon_fields(): void {
		if ( ! class_exists( 'Carbon_Fields\Carbon_Fields' ) ) {
			require_once( CFE__PLUGIN_DIR . 'vendor/autoload.php' );
			\Carbon_Fields\Carbon_Fields::boot();
		}
	}
}

CarbonFieldsInitializer::get_instance();