<?php
function crb_cfe_complex_field_header_template() {
	ob_start();
	?>
    <%- $_index + 1 %>. <%- field_name ? field_name : "" %>
	<?php
	return ob_get_clean();
}

function crb_cfe_complex_rows_header_template() {
	ob_start();
	echo esc_html__( 'row', 'custom-form-editor' )
	?>
    <%- $_index + 1 %>.
	<?php
	return ob_get_clean();
}
function crb_cfe_complex_column_header_template() {
	ob_start();
	echo esc_html__( 'column', 'custom-form-editor' )
	?>
    <%- $_index + 1 %>.
	<?php
	return ob_get_clean();
}

function get_association_items() {
	$arr = array(
		array(
			'type'      => 'post',
			'post_type' => 'page',
		),
	);
	if ( $association_post_types = carbon_get_theme_option( 'association_post_types' ) ) {
		foreach ( $association_post_types as $type ) {
			$arr[] = array(
				'type'      => 'post',
				'post_type' => $type['custom_post_type'],
			);
		}
	}
	if ( $association_taxonomies = carbon_get_theme_option( 'association_taxonomies' ) ) {
		foreach ( $association_taxonomies as $taxonomy ) {
			$arr[] = array(
				'type'     => 'term',
				'taxonomy' => $taxonomy['custom_taxonomy'],
			);
		}
	}

	return $arr;
}

function get_file_types_string() {
	return "
     Image files: .jpg, .jpeg, .png, .gif, .bmp <br>
     Audio files: .mp3, .wav, .ogg <br>
     Video files: .mp4, .webm, .avi, .mov <br>
     Document files: .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx <br>
     Compressed files: .zip, .rar <br>
    ";
}

function cfe_send_message( $m, $emails = array(), $form_subject = 'Повідомлення із сайту' ) {
	$c            = true;
	$message      = $m;
	$project_name = get_bloginfo( 'name' );
	$emails       = $emails ?: array( get_bloginfo( 'admin_email' ) );
	if ( $emails ) {
		foreach ( $emails as $email ) {
			$headers = "MIME-Version:1.0" . PHP_EOL .
			           "Content-Type:text/html; charset=utf-8" . PHP_EOL .
			           'From:' . cfe_adopt( $project_name ) . ' <application@' . $_SERVER['HTTP_HOST'] . '>' . PHP_EOL .
			           'Reply-To: ' . $email . '' . PHP_EOL;
			wp_mail( $email, $form_subject, $message, $headers );
		}
	}

}

function cfe_adopt( $text ) {
	return '=?UTF-8?B?' . base64_encode( $text ) . '?=';
}

function get_mail_html( $_id ) {
	$c       = true;
	$message = '';
	if ( $_id && get_post( $_id ) ) {
		if ( $cfe_results = carbon_get_post_meta( $_id, 'cfe_results' ) ) {
			foreach ( $cfe_results as $result ) {
				$field_name  = $result['field_name'];
				$field_value = $result['field_value'];
				if ( $field_name && $field_value ) {
					$message .=
						( ( $c = ! $c ) ? ' <tr>' : ' <tr style="background-color: #f8f8f8;"> ' ) . "
                        <td style='padding: 10px; border: #e9e9e9 1px solid;' ><b> $field_name</b></td>
                        <td style='padding: 10px; border: #e9e9e9 1px solid;' > $field_value</td>
                        </tr>
                    ";
				}
			}
		}
		if ( $cfe_result_files = carbon_get_post_meta( $_id, 'cfe_result_files' ) ) {
			foreach ( $cfe_result_files as $index => $file ) {
				$index = $index + 1;
				$file  = $file['file_url'];
				if ( $file ) {
					$image_info = getimagesize( $file );
					if ( $image_info !== false ) {
						list( $width, $height, $type, $attr ) = getimagesize( $file );

						$message .=
							( ( $c = ! $c ) ? ' <tr>' : ' <tr style="background-color: #f8f8f8;"> ' ) . "
                            <td style='padding: 10px; border: #e9e9e9 1px solid;' ><b> Image $index</b></td>
                            <td style='padding: 10px; border: #e9e9e9 1px solid;' ><img src=\"$file\" $attr alt=\"Image $index\" /></td>
                            </tr>
                        ";
					} else {
						$message .=
							( ( $c = ! $c ) ? ' <tr>' : ' <tr style="background-color: #f8f8f8;"> ' ) . "
                            <td style='padding: 10px; border: #e9e9e9 1px solid;' ><b> File $index</b></td>
                            <td style='padding: 10px; border: #e9e9e9 1px solid;' > $file</td>
                            </tr>
                        ";
					}

				}
			}
		}

	}

	return "<table style='width: 100%;'>$message</table> ";;
}


function token_test( $token ) {
	$google_recaptcha_secret_key = carbon_get_theme_option( 'google_recaptcha_secret_key' );
	$secret_key                  = $google_recaptcha_secret_key;
	$remote_ip                   = $_SERVER['REMOTE_ADDR'];

	$url      = 'https://www.google.com/recaptcha/api/siteverify';
	$data     = array(
		'secret'   => $secret_key,
		'response' => $token,
		'remoteip' => $remote_ip
	);
	$options  = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query( $data )
		)
	);
	$context  = stream_context_create( $options );
	$response = file_get_contents( $url, false, $context );
	$result   = json_decode( $response, true );

	if ( $result['success'] ) {
		return true;
	}

	return false;
}
