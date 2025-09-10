<?php

namespace CWC;

class Initializer {

	private string $transient_key = 'cwc_last_errors';

	public function __construct() {
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'convert_to_webp_on_upload' ], 10, 2 );
		add_action( 'wp_ajax_cwc_get_last_error', [ $this, 'cwc_get_last_error' ] );
	}

	public function cwc_get_last_error(): void {
		check_ajax_referer( 'cwc-ajax-nonce', 'nonce' );
		$last_errors = get_transient( $this->transient_key ) ?: [];
		$last_errors = array_map( 'trim', $last_errors );
		$last_errors = array_filter( $last_errors, fn( $item ) => $item !== '' );
		if ( $last_errors ) {
			delete_transient( $this->transient_key );
			wp_send_json_success( [ 'message' => implode( "; ", $last_errors ) ] );
		} else {
			wp_send_json_error( [ 'message' => 'No specific error message found.' ] );
		}
	}

	private function set_error( $error = '', $attachment_id = 0 ): void {
		if ( $error === '' ) {
			return;
		}
		if ( $attachment_id ) {
			$file_path = get_attached_file( $attachment_id );
			$file_name = basename( $file_path );
			$error     .= " File: $file_name";
		}
		$last_errors   = get_transient( $this->transient_key ) ?: [];
		$last_errors[] = $error;
		set_transient( $this->transient_key, $last_errors, DAY_IN_SECONDS );
	}

	public function convert_to_webp_on_upload( $metadata, $attachment_id ) {

		error_log( "--- CWC: Початок обробки для Attachment ID: {$attachment_id} ---" );

		$original_relative_path = $metadata['file'];
		$upload_dir             = wp_get_upload_dir();
		$original_filepath      = path_join( $upload_dir['basedir'], $original_relative_path );

		$file_extension = strtolower( pathinfo( $original_filepath, PATHINFO_EXTENSION ) );
		if ( ! in_array( $file_extension, [ 'jpg', 'jpeg', 'png' ] ) ) {
			error_log( "CWC: Файл не є JPG/PNG. Зупинка." );

			return $metadata;
		}

		if ( ! function_exists( 'imagewebp' ) ) {
			error_log( "CWC: Функція imagewebp() не існує. Зупинка." );
			$this->set_error( 'The server does not support conversion.', $attachment_id );

			return $metadata;
		}


		if ( ! isset( $metadata['width'], $metadata['height'] ) ) {
			error_log( "CWC: Помилка - відсутні метадані 'width' або 'height'. Зупинка." );
			$this->set_error( 'The metadata error.', $attachment_id );

			return $metadata;
		}

		$pixel_count = $metadata['width'] * $metadata['height'];
		error_log( "CWC: Розміри зображення {$metadata['width']}x{$metadata['height']}. Загальна кількість пікселів: {$pixel_count}" );

		if ( $pixel_count > CWC_MAX_PIXEL_THRESHOLD ) {
			error_log( "CWC: Зображення занадто велике ({$pixel_count} > " . CWC_MAX_PIXEL_THRESHOLD . "). Конвертація скасована." );
			$this->set_error( 'Image is too large. Conversion canceled, original uploaded.', $attachment_id );

			return $metadata;
		}

		error_log( "CWC: Оригінальний відносний шлях: {$original_relative_path}" );


		// --- КОНВЕРТАЦІЯ ---
		error_log( "CWC: Початок конвертації основного зображення..." );
		$webp_filepath      = str_ireplace( ".{$file_extension}", '.webp', $original_filepath );
		$conversion_success = $this->cwc_convert_image_to_webp( $original_filepath, $webp_filepath );

		if ( ! $conversion_success ) {
			error_log( "CWC: ПОМИЛКА конвертації основного зображення. Запускаємо відкат." );
			$this->set_error( 'CONVERSION ERROR ', $attachment_id );
		} else {
			error_log( "CWC: Основне зображення успішно сконвертовано в {$webp_filepath}" );
		}

		$files_to_delete = [ $original_filepath ];

		if ( $conversion_success && isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => &$size_data ) {
				error_log( "CWC: Конвертація мініатюри '{$size}'..." );
				$thumb_filepath    = path_join( dirname( $original_filepath ), $size_data['file'] );
				$files_to_delete[] = $thumb_filepath;

				$thumb_ext           = strtolower( pathinfo( $thumb_filepath, PATHINFO_EXTENSION ) );
				$webp_thumb_filepath = str_ireplace( ".{$thumb_ext}", '.webp', $thumb_filepath );

				if ( $this->cwc_convert_image_to_webp( $thumb_filepath, $webp_thumb_filepath ) ) {
					$size_data['file']      = basename( $webp_thumb_filepath );
					$size_data['mime-type'] = 'image/webp';
					error_log( "CWC: Мініатюра '{$size}' успішно сконвертована." );
				} else {
					error_log( "CWC: ПОМИЛКА конвертації мініатюри '{$size}'. Запускаємо відкат." );
					$conversion_success = false;
					break;
				}
			}
			unset( $size_data );
		}

		// --- ОНОВЛЕННЯ ТА ВИДАЛЕННЯ ---
		if ( $conversion_success && file_exists( $webp_filepath ) ) {
			error_log( "CWC: Усі конвертації успішні. Оновлення бази даних та видалення оригіналів." );
			$new_relative_path = str_ireplace( ".{$file_extension}", '.webp', $original_relative_path );

			update_post_meta( $attachment_id, '_wp_attached_file', $new_relative_path );
			error_log( "CWC: Оновлено _wp_attached_file на: {$new_relative_path}" );

			$metadata['file']      = $new_relative_path;
			$metadata['mime-type'] = 'image/webp';

			foreach ( $files_to_delete as $file ) {
				if ( file_exists( $file ) ) {
					@unlink( $file );
					error_log( "CWC: Видалено оригінальний файл: {$file}" );
				}
			}

			wp_update_post( [ 'ID' => $attachment_id, 'post_mime_type' => 'image/webp' ] );
			error_log( "CWC: Оновлено post_mime_type для ID {$attachment_id}." );

		} else {
			error_log( "CWC: Сталася помилка. Видалення створених WebP файлів і відкат." );
			$pattern            = path_join( dirname( $original_filepath ), pathinfo( $original_filepath, PATHINFO_FILENAME ) . '*.webp' );
			$created_webp_files = glob( $pattern );
			foreach ( $created_webp_files as $webp_file ) {
				@unlink( $webp_file );
				error_log( "CWC: Видалено тимчасовий WebP файл: {$webp_file}" );
			}
			error_log( "CWC: Повернення оригінальних метаданих." );

			return get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
		}

		error_log( "--- CWC: Обробка для Attachment ID {$attachment_id} успішно завершена. ---" );

		return $metadata;
	}

	/**
	 * Допоміжна функція для конвертації одного зображення у WebP
	 *
	 * @param string $source_path - Шлях до вихідного файлу (JPG/PNG)
	 * @param string $dest_path - Шлях для збереження WebP файлу
	 * @param int $quality - Якість WebP (від 0 до 100)
	 *
	 * @return bool - true у разі успіху, false у разі помилки
	 */

	public static function cwc_convert_image_to_webp( $source_path, $dest_path, $quality = 82 ): bool {
		if ( ! file_exists( $source_path ) ) {
			error_log( "CWC_CONVERT: Вихідний файл не знайдено: {$source_path}" );

			return false;
		}

		@ini_set( 'memory_limit', '6144M' ); // Явно встановимо ваш ліміт
		@set_time_limit( 300 ); // Спробуємо встановити час виконання на 5 хвилин

		// ... (решта коду функції cwc_convert_image_to_webp без змін) ...
		$file_extension = strtolower( pathinfo( $source_path, PATHINFO_EXTENSION ) );
		$image          = null;

		if ( $file_extension === 'jpg' || $file_extension === 'jpeg' ) {
			$image = @imagecreatefromjpeg( $source_path );
		} elseif ( $file_extension === 'png' ) {
			$image = @imagecreatefrompng( $source_path );
			if ( $image ) {
				imagepalettetotruecolor( $image );
				imagealphablending( $image, true );
				imagesavealpha( $image, true );
			}
		}

		if ( ! $image ) {
			error_log( "CWC_CONVERT: Не вдалося створити зображення з файлу (imagecreatefrom...): {$source_path}" );

			return false;
		}

		$success = imagewebp( $image, $dest_path, $quality );
		imagedestroy( $image );

		if ( ! $success ) {
			error_log( "CWC_CONVERT: Функція imagewebp() повернула false для: {$dest_path}" );
		}

		return $success;
	}

}

new Initializer();