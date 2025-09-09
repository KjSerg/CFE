<?php

namespace CWC;
class Initializer {
	public function __construct() {
		/**
		 * Хук, який спрацьовує після завантаження та обробки зображення,
		 * але до збереження його метаданих у базу даних.
		 * Ми модифікуємо ці метадані "на льоту".
		 */
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'convert_to_webp_on_upload' ], 10, 2 );
	}

	public function convert_to_webp_on_upload( $metadata, $attachment_id ) {

		// 1. Отримуємо шлях до завантаженого файлу
		$upload_dir        = wp_get_upload_dir();
		$original_filepath = path_join( $upload_dir['basedir'], $metadata['file'] );

		// 2. Перевіряємо розширення файлу. Працюємо тільки з JPG та PNG.
		$file_extension = strtolower( pathinfo( $original_filepath, PATHINFO_EXTENSION ) );

		if ( ! in_array( $file_extension, [ 'jpg', 'jpeg', 'png' ] ) ) {
			return $metadata; // Якщо це не JPG/PNG, нічого не робимо
		}

		// 3. Перевіряємо, чи сервер підтримує WebP (потрібна бібліотека GD або Imagick)
		if ( ! function_exists( 'imagewebp' ) ) {
			// Можна додати логування помилки, якщо потрібно
			error_log( 'WebP conversion failed: GD library is not available.' );

			return $metadata;
		}

		// Список файлів, які потрібно буде видалити після успішної конвертації
		$files_to_delete    = [ $original_filepath ];
		$conversion_success = true;

		// --- КОНВЕРТАЦІЯ ОСНОВНОГО ЗОБРАЖЕННЯ ---

		// Створюємо новий шлях для WebP файлу
		$webp_filepath = str_ireplace( [ '.jpg', '.jpeg', '.png' ], '.webp', $original_filepath );

		// Конвертуємо основне зображення
		if ( ! $this->convert_image_to_webp( $original_filepath, $webp_filepath ) ) {
			$conversion_success = false;
		}

		// --- КОНВЕРТАЦІЯ МІНІАТЮР ---

		if ( $conversion_success && isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => &$size_data ) {
				// Отримуємо повний шлях до мініатюри
				$thumb_filepath = path_join( dirname( $original_filepath ), $size_data['file'] );

				// Додаємо оригінальну мініатюру до списку на видалення
				$files_to_delete[] = $thumb_filepath;

				// Створюємо новий шлях для WebP мініатюри
				$webp_thumb_filepath = str_ireplace( [ '.jpg', '.jpeg', '.png' ], '.webp', $thumb_filepath );

				// Конвертуємо мініатюру
				if ( $this->convert_image_to_webp( $thumb_filepath, $webp_thumb_filepath ) ) {
					// Оновлюємо метадані для цієї мініатюри
					$size_data['file']      = basename( $webp_thumb_filepath );
					$size_data['mime-type'] = 'image/webp';
				} else {
					// Якщо хоч одна мініатюра не сконвертувалась, відміняємо всю операцію
					$conversion_success = false;
					break; // Виходимо з циклу
				}
			}
			unset( $size_data ); // Важливо для уникнення проблем з посиланнями
		}

		// --- ОНОВЛЕННЯ МЕТАДАНИХ ТА ВИДАЛЕННЯ ОРИГІНАЛІВ ---

		if ( $conversion_success && file_exists( $webp_filepath ) ) {
			// Оновлюємо метадані для основного файлу
			$new_relative_path = str_ireplace(".{$file_extension}", '.webp', $original_filepath);

			// *** КЛЮЧОВИЙ МОМЕНТ: Оновлюємо шлях до основного файлу в мета-даних ***
			// Це поле використовується WordPress для побудови URL-адрес
			update_post_meta($attachment_id, '_wp_attached_file', $new_relative_path);

			// Оновлюємо метадані, які будуть збережені в '_wp_attachment_metadata'
			$metadata['file'] = $new_relative_path;
			$metadata['mime-type'] = 'image/webp';

			// Оновлюємо розмір файлу в метаданих
			$metadata['filesize'] = filesize( $webp_filepath );

			// Видаляємо всі оригінальні файли (основний + мініатюри)
			foreach ( $files_to_delete as $file ) {
				if ( file_exists( $file ) ) {
					@unlink( $file );
				}
			}

			// Оновлюємо тип файлу безпосередньо в базі даних (важливо для правильного відображення в медіатеці)
			wp_update_post( array(
				'ID'             => $attachment_id,
				'post_mime_type' => 'image/webp'
			) );

		} else {
			// Якщо сталася помилка, видаляємо всі створені WebP файли, щоб не залишати сміття
			$created_webp_files = glob( dirname( $original_filepath ) . '/*.webp' );
			foreach ( $created_webp_files as $webp_file ) {
				@unlink( $webp_file );
			}

			// Повертаємо оригінальні метадані, ніби нічого й не було
			return $metadata;
		}

		return $metadata; // Повертаємо змінені метадані
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

	public static function convert_image_to_webp( $source_path, $dest_path, $quality = 80 ): bool {
		if ( ! file_exists( $source_path ) ) {
			return false;
		}

		$file_extension = strtolower( pathinfo( $source_path, PATHINFO_EXTENSION ) );
		$image          = null;

		// Завантажуємо зображення залежно від його типу
		if ( $file_extension === 'jpg' || $file_extension === 'jpeg' ) {
			$image = @imagecreatefromjpeg( $source_path );
		} elseif ( $file_extension === 'png' ) {
			$image = @imagecreatefrompng( $source_path );
			if ( $image ) {
				// Зберігаємо прозорість для PNG
				imagepalettetotruecolor( $image );
				imagealphablending( $image, true );
				imagesavealpha( $image, true );
			}
		}

		if ( ! $image ) {
			return false;
		}

		// Конвертуємо та зберігаємо у форматі WebP
		$success = imagewebp( $image, $dest_path, $quality );

		// Звільняємо пам'ять
		imagedestroy( $image );

		return $success;
	}

}

new Initializer();