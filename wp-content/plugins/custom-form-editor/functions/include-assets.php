<?php
/**
 * Константа для шляху до папки assets на сервері для версіонування файлів.
 */
define( 'CFE__ASSETS_PATH', get_stylesheet_directory() . '/assets' );

/**
 * Правильне підключення скриптів та стилів для фронтенду.
 */
function cfe_enqueue_theme_assets(): void {
	// Отримуємо всі налаштування один раз на початку
	$options = [
		'style_off'     => carbon_get_theme_option( 'style_off' ),
		'scripts_off'   => carbon_get_theme_option( 'scripts_off' ),
		'selectric_off' => carbon_get_theme_option( 'selectric_off' ),
		'fancybox_off'  => carbon_get_theme_option( 'fancybox_off' ),
	];

	// --- Підключення стилів ---

	// Loader підключається завжди
	wp_enqueue_style( 'cfe-loader', CFE__ASSETS_URL . '/css/loader.css', [], filemtime( CFE__ASSETS_PATH . '/css/loader.css' ) );

	// Підключаємо основний файл стилів, якщо не вимкнено
	if ( ! $options['style_off'] ) {
		wp_enqueue_style( 'cfe-main', CFE__ASSETS_URL . '/css/old_cfe.css', [], filemtime( CFE__ASSETS_PATH . '/css/old_cfe.css' ) );
	}

	// Підключаємо додаткові стилі, якщо основні скрипти не вимкнені
	if ( ! $options['scripts_off'] ) {
		if ( ! $options['fancybox_off'] && ! wp_style_is( 'fancybox', 'enqueued' ) ) {
			wp_enqueue_style( 'fancybox', CFE__ASSETS_URL . '/css/jquery.fancybox.min.css', [], '3.5.7' );
		}
		if ( ! $options['selectric_off'] && ! wp_style_is( 'selectric', 'enqueued' ) ) {
			wp_enqueue_style( 'selectric', CFE__ASSETS_URL . '/css/selectric.css', [], '1.13.0' );
		}
	}

	// --- Підключення скриптів ---

	// Підключаємо скрипти, тільки якщо вони не вимкнені
	if ( ! $options['scripts_off'] ) {
		if ( ! $options['selectric_off'] && ! wp_script_is( 'selectric', 'enqueued' ) ) {
			wp_enqueue_script( 'selectric', CFE__ASSETS_URL . '/js/jquery.selectric.min.js', [ 'jquery' ], '1.13.0', true );
		}
		if ( ! $options['fancybox_off'] && ! wp_script_is( 'fancybox', 'enqueued' ) ) {
			// Fancybox залежить від jQuery, це варто вказувати явно.
			wp_enqueue_script( 'fancybox', CFE__ASSETS_URL . '/js/jquery.fancybox.min.js', [ 'jquery' ], '3.5.7', true );
		}

		// Підключаємо основний скрипт
		wp_enqueue_script( 'cfe-scripts', CFE__ASSETS_URL . '/js/cfe.js', [ 'jquery' ], filemtime( CFE__ASSETS_PATH . '/js/cfe.js' ), true );

		// ПРАВИЛЬНО локалізуємо скрипт: прив'язуємо дані до вже підключеного скрипта 'cfe-scripts'
		wp_localize_script(
			'cfe-scripts', // Прив'язка до нашого головного скрипта
			'CFE_AJAX',    // Назва JS об'єкта (краще використовувати префікс)
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}
}
add_action( 'wp_enqueue_scripts', 'cfe_enqueue_theme_assets' );


/**
 * Правильне підключення скриптів та стилів для адмін-панелі.
 */
function cfe_enqueue_admin_assets(): void {
	// Використовуємо get_current_screen() для надійного визначення сторінки
	$screen = get_current_screen();

	// Підключаємо адмін-стилі завжди
	wp_enqueue_style( 'custom-admin-css', CFE__ASSETS_URL . '/css/admin.css', [], filemtime( CFE__ASSETS_PATH . '/css/admin.css' ) );

	// Підключаємо адмін-скрипт тільки на сторінках редагування CPT 'contact_form'
	if ( $screen && $screen->post_type === 'contact_form' && $screen->base === 'post' ) {
		wp_enqueue_script( 'custom-admin-scripts', CFE__ASSETS_URL . '/js/admin.js', [ 'jquery' ], filemtime( CFE__ASSETS_PATH . '/js/admin.js' ), true );
	}
}
add_action( 'admin_enqueue_scripts', 'cfe_enqueue_admin_assets' );