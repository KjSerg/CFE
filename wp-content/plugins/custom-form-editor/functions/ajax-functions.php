<?php
add_action( 'wp_ajax_nopriv_send_custom_form', 'send_custom_form' );
add_action( 'wp_ajax_send_custom_form', 'send_custom_form' );
function send_custom_form() {
	$res   = array( 'msg' => '' );
	$store = array();
	if ( $post = $_POST ) {
		$form_id = $post['form_id'] ?? '';
		$title   = 'New application ';
		if ( $form_id && get_post( $form_id ) ) {
			$contact_form_answer = carbon_get_post_meta( $form_id, 'contact_form_answer' );
			$res['msg']          = $contact_form_answer;
			$title               .= get_the_title( $form_id ) . ' [FormID:' . $form_id . ']';
		} else {
			$form_id = 0;
		}
		foreach ( $post as $key => $value ) {
			if ( $key != 'action' && $key != 'form_id' ) {
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
		if ( ! is_wp_error( $_id ) && $post ) {
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
							'file_url' => wp_get_attachment_url( my_handle_attachment( $file ) )
						);
					}
					carbon_set_post_meta( $_id, 'cfe_result_files', $arr );
				}
			}
			if ( $form_id ) {
				$contact_form_subject = carbon_get_post_meta( $form_id, 'contact_form_subject' );
				$contact_form_emails  = carbon_get_post_meta( $form_id, 'contact_form_emails' ) ?: array();
				send_message( get_mail_html( $_id ), $contact_form_emails, $contact_form_subject );
			}
		} else {
			if ( is_wp_error( $_id ) ) {
				$res['msg'] = $_id->get_error_message();
			} else {
				$res['msg'] = 'Error';
			}
			$res['type'] = 'error';
		}
	}
	echo json_encode( $res );
	die();
}


function my_handle_attachment( $file_handler, $post_id = 0, $set_thu = false ) {

	if ( $_FILES[ $file_handler ]['error'] !== UPLOAD_ERR_OK ) {
		__return_false();
	}

	require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

	return media_handle_upload( $file_handler, $post_id );
}
