<?php

function get_telegram_help_text_cfe() {
	$cfe_telegram_api_key = carbon_get_theme_option( 'cfe_telegram_api_key' ) ?: 'YOUR_BOT_TOKEN';

	return 'How to find chat_id: Write something to your bot in Telegram. Open this URL in your browser:
<a target="_blank" href="https://api.telegram.org/bot' . $cfe_telegram_api_key . '/getUpdates">https://api.telegram.org/bot' . $cfe_telegram_api_key . '/getUpdates</a>
In the answer you will see chat.id.';
}

function crb_cfe_complex_field_header_template(): bool|string {
	ob_start();
	?>
    <%- $_index + 1 %>. <%- field_name ? field_name : "" %>
	<?php
	return ob_get_clean();
}

function crb_cfe_complex_rows_header_template(): bool|string {
	ob_start();
	echo esc_html__( 'row', 'custom-form-editor' )
	?>
    <%- $_index + 1 %>.
	<?php
	return ob_get_clean();
}

function crb_cfe_complex_column_header_template(): bool|string {
	ob_start();
	echo esc_html__( 'column', 'custom-form-editor' )
	?>
    <%- $_index + 1 %>.
	<?php
	return ob_get_clean();
}

function get_association_items() {
	$arr = get_transient( 'cfe_get_association_items' );
	if ( $arr === false ) {
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
		set_transient( 'cfe_get_association_items', $arr, 600 );
	}

	return $arr;
}

function get_file_types_string(): string {
	return "
     Image files: .jpg, .jpeg, .png, .gif, .bmp <br>
     Audio files: .mp3, .wav, .ogg <br>
     Video files: .mp4, .webm, .avi, .mov <br>
     Document files: .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx <br>
     Compressed files: .zip, .rar <br>
    ";
}

function cfe_send_message( $m, $emails = array(), $form_subject = 'Повідомлення із сайту' ): array {
	$res          = [];
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

			$res[ $email ] = wp_mail( $email, $form_subject, $message, $headers );
		}
	}

	return $res;

}

function cfe_adopt( $text ): string {
	return '=?UTF-8?B?' . base64_encode( $text ) . '?=';
}

function get_mail_html( $_id ): string {
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

function google_recaptcha_token_test( $token ): bool {
	$google_recaptcha_secret_key = carbon_get_theme_option( 'google_recaptcha_secret_key' );
	$secret_key                  = $google_recaptcha_secret_key;
	$remote_ip                   = $_SERVER['REMOTE_ADDR'];
	$url                         = 'https://www.google.com/recaptcha/api/siteverify';
	$data                        = array(
		'secret'   => $secret_key,
		'response' => $token,
		'remoteip' => $remote_ip
	);
	$options                     = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query( $data )
		)
	);
	$context                     = stream_context_create( $options );
	$response                    = file_get_contents( $url, false, $context );
	$result                      = json_decode( $response, true );

	if ( $result['success'] ) {
		return true;
	}

	return false;
}

function cfe_generate_random_string( $length = 10 ): string {
	$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen( $characters );
	$randomString     = '';

	for ( $i = 0; $i < $length; $i ++ ) {
		$randomString .= $characters[ random_int( 0, $charactersLength - 1 ) ];
	}

	return $randomString;
}

function cfe_check_svg(): string {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
    <mask id="mask0_705_12968" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
        <rect width="16" height="16" fill="#D9D9D9"/>
    </mask>
    <g mask="url(#mask0_705_12968)">
        <path d="M6.36664 10.1L12.0166 4.45C12.15 4.31667 12.3055 4.25 12.4833 4.25C12.6611 4.25 12.8166 4.31667 12.95 4.45C13.0833 4.58333 13.15 4.74167 13.15 4.925C13.15 5.10833 13.0833 5.26667 12.95 5.4L6.83331 11.5333C6.69998 11.6667 6.54442 11.7333 6.36664 11.7333C6.18886 11.7333 6.03331 11.6667 5.89998 11.5333L3.03331 8.66667C2.89998 8.53333 2.83609 8.375 2.84164 8.19167C2.8472 8.00833 2.91664 7.85 3.04998 7.71667C3.18331 7.58333 3.34164 7.51667 3.52498 7.51667C3.70831 7.51667 3.86664 7.58333 3.99998 7.71667L6.36664 10.1Z" fill="#146C8F"/>
    </g>
</svg>';
}