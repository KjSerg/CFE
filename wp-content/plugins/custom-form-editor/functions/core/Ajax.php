<?php

namespace CFE;
class Ajax {
	private static ?self $instance = null;

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_nopriv_send_custom_form', [ $this, 'send_custom_form' ] );
		add_action( 'wp_ajax_send_custom_form', [ $this, 'send_custom_form' ] );
	}

	public static function send_error( string $message ): void {
		self::send_response( [
			'type' => 'error',
			'msg'  => $message
		] );
	}

	public static function send_response( array $response ): void {
		echo json_encode( $response );
		wp_die();
	}

	public static function cfe_handle_attachment( $file_handler, $post_id = 0, $set_thu = false ): \WP_Error|int {

		if ( $_FILES[ $file_handler ]['error'] !== UPLOAD_ERR_OK ) {
			__return_false();
		}

		require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
		require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

		return media_handle_upload( $file_handler, $post_id );
	}

	public function send_custom_form() {
		$res        = array( 'msg' => '' );
		$store      = array();
		$URL        = get_bloginfo( 'url' );
		$nonce_test = isset( $_POST['true_nonce'] ) && wp_verify_nonce( $_POST['true_nonce'], 'send_custom_form' . $_POST['form_id'] );
		if ( ! $nonce_test ) {
			self::send_error( 'Invalid nonce' );
		}
		$user_ip  = $_SERVER['REMOTE_ADDR'];
		$attempts = get_transient( "reg_attempts_$user_ip" ) ?: 1;
		$attempts = intval( $attempts );
		if ( $attempts && $attempts > 3 ) {
			self::send_error( 'Too many attempts, please wait a few minutes.' );
		}
		$attempts = $attempts + 1;
		set_transient( "reg_attempts_$user_ip", $attempts, 300 );
		if ( $post = $_POST ) {
			$google_recaptcha_site_key   = carbon_get_theme_option( 'google_recaptcha_site_key' );
			$google_recaptcha_secret_key = carbon_get_theme_option( 'google_recaptcha_secret_key' );
			$token                       = filter_input( INPUT_POST, 'g-recaptcha-response' ) ?: filter_input( INPUT_POST, 'token' );
			$form_id                     = filter_input( INPUT_POST, 'form_id', FILTER_SANITIZE_NUMBER_INT );
			$title                       = 'New application ';
			if ( $google_recaptcha_site_key && $google_recaptcha_secret_key ) {
				if ( ! $token ) {
					self::send_error( 'Recaptcha Error' );
				}
				if ( ! google_recaptcha_token_test( $token ) ) {
					self::send_error( 'Token Recaptcha Error' );
				}
			}
			if ( ! $form_id ) {
				self::send_error( 'Form ID Error' );
			}
			if ( ! get_post( $form_id ) ) {
				self::send_error( 'Form ID Error' );
			}
			$contact_form_answer = carbon_get_post_meta( $form_id, 'contact_form_answer' );
			$res['msg']          = $contact_form_answer;
			$title               .= get_the_title( $form_id ) . ' [FormID:' . $form_id . ']';
			foreach ( $post as $key => $value ) {
				$exclusion = array( 'action', 'form_id', 'true_nonce', '', 'g-recaptcha-response' );
				if ( $key == '_wp_http_referer' ) {
					$_url    = $URL . $value;
					$post_id = url_to_postid( $_url );
					if ( $post_id ) {
						$_type   = get_post_type( $post_id );
						$_title  = get_the_title( $post_id );
						$store[] = array(
							'field_name'  => $_type . " [ID: $post_id]",
							'field_value' => $_title,
						);
					}
				}
				if ( ! in_array( $key, $exclusion ) ) {
					if ( $value && $value != '' ) {
						$key     = str_replace( '_', ' ', $key );
						$value   = is_array( $value ) ? implode( ', ', $value ) : $value;
						$store[] = array(
							'field_name'  => $key,
							'field_value' => $value,
						);
					}

				}
			}
			$post_data = array(
				'post_type'   => 'cfe_results',
				'post_title'  => $title,
				'post_status' => 'pending',
			);
			$_id       = wp_insert_post( $post_data, true );
			$post      = get_post( $_id );
			if ( is_wp_error( $_id ) ) {
				self::send_error( $_id->get_error_message() );
			}
			if ( ! $post ) {
				self::send_error( 'Error' );
			}
			$store[] = array(
				'field_name'  => 'User IP',
				'field_value' => $user_ip,
			);
			carbon_set_post_meta( $_id, 'cfe_results', $store );
			carbon_set_post_meta( $_id, 'form_id', $_id );
			$files         = $_FILES["upfile"];
			$arr           = array();
			$res['$files'] = $files;
			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ] ) {
					$file   = array(
						'name'     => $files['name'][ $key ],
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ]
					);
					$_FILES = array( "file" => $file );
					foreach ( $_FILES as $file => $array ) {
						$arr[] = array(
							'file_url' => wp_get_attachment_url( $this->cfe_handle_attachment( $file ) )
						);
					}
					carbon_set_post_meta( $_id, 'cfe_result_files', $arr );
				}
			}
			$contact_form_subject  = carbon_get_post_meta( $form_id, 'contact_form_subject' );
			$contact_form_emails   = carbon_get_post_meta( $form_id, 'contact_form_emails' ) ?: array();
			$res['emails_results'] = cfe_send_message( get_mail_html( $_id ), $contact_form_emails, $contact_form_subject );
			if ( $bot_token = carbon_get_theme_option( 'cfe_telegram_api_key' ) ) {
				$chats_id             = \CFE\Helpers\Telegram::get_all_admin_telegram_chat_ids();
				$res['telegram_data'] = [];
				if ( $contact_form_telegram_chats = carbon_get_post_meta( $form_id, 'contact_form_telegram_chats' ) ) {
					foreach ( $contact_form_telegram_chats as $item ) {
						$chat_id = $item['chat_id'];
						if ( ! in_array( $chat_id, $chats_id ) ) {
							$chats_id[] = $chat_id;
						}
					}
				}
				if ( ! empty( $chats_id ) ) {
					$telegram_msg = '';
					foreach ( $store as $item ) {
						$n            = $item['field_name'];
						$value        = $item['field_value'];
						$telegram_msg .= "$n: $value\n";
					}
					if ( $arr ) {
						foreach ( $arr as $f_index => $item ) {
							$num          = $f_index + 1;
							$file_url     = $item['file_url'];
							$telegram_msg .= "File $num: $file_url\n";
						}
					}
					foreach ( $chats_id as $chat_id ) {
						$res['telegram_data'][] = \CFE\Helpers\Telegram::send_message( $telegram_msg, $chat_id );
					}
				}
			}
			self::send_response( $res );
		}
	}
}

Ajax::get_instance();