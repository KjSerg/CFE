<?php

namespace CFE;

// Клас перейменовано для більшої специфічності та уникнення конфліктів.
class FormRenderer {

	/**
	 * Лічильник для унікальних ID форм на одній сторінці.
	 * Використовуємо статичну властивість замість глобальної змінної.
	 * @var int
	 */
	private static int $form_instance_counter = 0;

	/**
	 * Прапорець, щоб гарантувати, що preloader рендериться лише один раз на сторінці.
	 * @var bool
	 */
	private static bool $preloader_rendered = false;

	/**
	 * Головний метод для рендерингу форми.
	 *
	 * @param int|string $id ID поста контактної форми.
	 *
	 * @return string Готовий HTML-код форми.
	 */
	public static function render( $id ): string {
		// 1. Не підключаємо файли для завантаження медіа тут.
		// Це має відбуватися в AJAX-обробнику, який обробляє POST-запит.

		$contact_form_rows = carbon_get_post_meta( $id, 'contact_form_rows' );

		// Якщо даних для форми немає, повертаємо порожній рядок.
		if ( empty( $contact_form_rows ) ) {
			return '';
		}

		self::$form_instance_counter ++;

		ob_start();

		// Рендеримо preloader лише один раз на сторінці.
		self::render_preloader_once();

		// 2. Збираємо атрибути для тегу <form> в окремому методі.
		$form_attributes = self::build_form_attributes( $id );
		?>
        <form <?php echo $form_attributes; ?>>
			<?php
			// 3. Рендеримо приховані поля в окремому методі.
			self::render_hidden_fields( $id );

			// 4. Рендеримо поля форми, винесено в окремий метод для чистоти.
			self::render_form_rows( $contact_form_rows );
			?>
        </form>
		<?php

		return ob_get_clean();
	}

	/**
	 * Рендерить preloader, якщо він ще не був відрендерений.
	 */
	private static function render_preloader_once(): void {
		if ( self::$preloader_rendered ) {
			return;
		}
		?>
        <div class="cfe-preloader">
            <img src="<?php echo esc_url( CFE__ASSETS_URL . '/img/loading.gif' ); ?>" alt="Loading...">
        </div>
		<?php
		self::$preloader_rendered = true;
	}

	/**
	 * Генерує рядок з атрибутами для тегу <form>.
	 *
	 * @param int|string $id ID форми.
	 *
	 * @return string
	 */
	private static function build_form_attributes( $id ): string {
		$scripts_off = carbon_get_theme_option( 'scripts_off' );

		// 5. Збираємо класи в масив - це набагато чистіше.
		$css_classes = [
			'custom-form-js',
			'form',
			'custom-form-render',
			carbon_get_post_meta( $id, 'contact_form_css_class' ),
		];

		if ( ! $scripts_off ) {
			$css_classes[] = 'form-js';
		}

		$attributes = [
			'id'      => sprintf( 'custom-form-%s-%d', $id, self::$form_instance_counter ),
			'class'   => implode( ' ', array_filter( $css_classes ) ), // array_filter видаляє порожні класи.
			'action'  => esc_url( admin_url( 'admin-ajax.php' ) ),
			'method'  => 'post',
			'enctype' => 'multipart/form-data',
			'novalidate', // Додаємо як атрибут без значення.
		];

		$attributes_str = '';
		foreach ( $attributes as $key => $value ) {
			if ( is_int( $key ) ) {
				$attributes_str .= $value . ' ';
			} else {
				$attributes_str .= sprintf( '%s="%s" ', $key, esc_attr( $value ) );
			}
		}

		return trim( $attributes_str );
	}

	/**
	 * Рендерить приховані поля (action, form_id, nonce).
	 *
	 * @param int|string $id ID форми.
	 */
	private static function render_hidden_fields( $id ): void {
		?>
        <input type="hidden" name="action" value="send_custom_form">
        <input type="hidden" name="form_id" value="<?php echo esc_attr( $id ); ?>">
		<?php
		wp_nonce_field( 'send_custom_form' . $id, 'true_nonce' );
	}

	/**
	 * Обробляє цикли та рендерить ряди і колонки з полями.
	 *
	 * @param array $rows Масив рядів з Carbon Fields.
	 */
	private static function render_form_rows( array $rows ): void {
		foreach ( $rows as $row_index => $row ) {
			if ( empty( $row['columns'] ) ) {
				continue;
			}

			foreach ( $row['columns'] as $column_index => $column ) {
				if ( empty( $column['field'] ) ) {
					continue;
				}

				foreach ( $column['field'] as $field_index => $field_data ) {
					// 6. Спрощена, але надійна генерація унікального індексу.
					$unique_field_id = sprintf( '%d_%d_%d_%d', self::$form_instance_counter, $row_index, $column_index, $field_index );

					$field = new Field( $field_data );
					$field->set_title( $column['column_title'] ?? '' );
					$field->set_index( $unique_field_id );
					$field->set_column_width_cls( $column['column_width'] ?? '' ); // Додано значення за замовчуванням
					$field->render();
				}
			}
		}
	}
}