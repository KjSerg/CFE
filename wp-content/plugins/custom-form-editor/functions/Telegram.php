<?php

namespace CFE\Helpers;

use Exception;
use WP_User_Query;

class Telegram {

	public function add_login_button(): void {
		add_action( 'admin_menu', function () {
			add_submenu_page(
				'edit.php?post_type=contact_form', // Батьківська сторінка
				'Telegram authorization',
				'Telegram authorization',
				'manage_options',
				'custom_telegram_page',
				[ $this, 'custom_telegram_page_html' ],
				1000
			);
		} );
		add_action( 'admin_init', [ $this, 'telegram_auth' ] );
	}

	/**
	 * @throws Exception
	 */
	public static function check_telegram_authorization( $auth_data ) {
		$bot_token  = carbon_get_theme_option( 'cfe_telegram_api_key' );
		$check_hash = $auth_data['hash'];
		unset( $auth_data['hash'] );
		unset( $auth_data['page'] );
		unset( $auth_data['post_type'] );
		$data_check_arr = [];
		foreach ( $auth_data as $key => $value ) {
			$data_check_arr[] = $key . '=' . $value;
		}
		sort( $data_check_arr );
		$data_check_string = implode( "\n", $data_check_arr );
		$secret_key        = hash( 'sha256', $bot_token, true );
		$hash              = hash_hmac( 'sha256', $data_check_string, $secret_key );
		if ( strcmp( $hash, $check_hash ) !== 0 ) {
			wp_die( 'Data is NOT from Telegram' );
		}
		if ( ( time() - $auth_data['auth_date'] ) > 86400 ) {
			wp_die( 'Data is outdated' );
		}

		return $auth_data;
	}

	public static function telegram_auth(): void {
		$page = filter_input( INPUT_GET, 'page' );
		if ( ! filter_input( INPUT_GET, 'hash' ) ) {
			return;
		}
		if ( 'custom_telegram_page' != $page ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			wp_die( 'Вы не авторизованы' );
		}

		if ( ! $bot_token = carbon_get_theme_option( 'cfe_telegram_api_key' ) ) {
			wp_die( 'telegram_bot_token пустой' );
		}

		try {
			$var         = variables();
			$set         = $var['setting_home'];
			$assets      = $var['assets'];
			$url         = $var['url'];
			$url_home    = $var['url_home'];
			$auth_data   = self::check_telegram_authorization( $_GET );
			$telegram_id = $auth_data['id'];
			$username    = $auth_data['username'];
			$first_name  = $auth_data['first_name'];
			$last_name   = $auth_data['last_name'];
			$photo_url   = $auth_data['photo_url'];
			$user        = self::get_user_by_telegram( $telegram_id );
			$_user_id    = get_current_user_id();
			if ( $user ) {
				$_user_id = $user->ID;
			}
			$user_id = get_current_user_id();
			$chat_id = sanitize_text_field( $telegram_id );
			carbon_set_user_meta( $_user_id, 'telegram_chat_id', $chat_id );
			header( 'Location:' . $url . 'wp-admin/edit.php?post_type=contact_form&page=custom_telegram_page' );
		} catch ( Exception $e ) {
			die ( $e->getMessage() );
		}


	}

	public static function get_all_admin_ids(): array {
		global $wpdb;

		$admin_ids = $wpdb->get_col(
			"SELECT u.ID
         FROM $wpdb->users u
         INNER JOIN $wpdb->usermeta um ON u.ID = um.user_id
         WHERE um.meta_key = '{$wpdb->prefix}capabilities'
         AND um.meta_value LIKE '%administrator%'"
		);

		return $admin_ids;
	}

	public static function get_all_admin_telegram_chat_ids(): array {
		if ( ! $admin_ids = self::get_all_admin_ids() ) {
			return [];
		}
		$res = [];
		foreach ( $admin_ids as $admin_id ) {
			if ( $telegram_chat_id = carbon_get_user_meta( $admin_id, 'telegram_chat_id' ) ) {
				$res[] = $telegram_chat_id;
			}
		}

		return $res;
	}

	public static function get_admin_telegram_chat_id( $_admin_id = false ) {
		if ( $_admin_id ) {
			return carbon_get_user_meta( $_admin_id, 'telegram_chat_id' ) ?: false;
		}
		if ( ! $admin_ids = self::get_all_admin_ids() ) {
			return false;
		}
		$telegram_chat_id = false;
		foreach ( $admin_ids as $admin_id ) {
			$telegram_chat_id = carbon_get_user_meta( $admin_id, 'telegram_chat_id' );
			break;
		}

		return $telegram_chat_id;
	}

	public static function get_user_by_telegram( $telegram_id ) {
		$res    = array();
		$params = array(
			'meta_query' => array(
				array(
					'key'     => 'telegram_id',
					'value'   => $telegram_id,
					'compare' => '='
				)
			)
		);
		$uq     = new WP_User_Query( $params );
		if ( ! empty( $uq->results ) ) {
			foreach ( $uq->results as $u ) {
				$res = $u;
			}
		}

		return empty( $res ) ? false : $res;
	}

	public static function custom_telegram_page_html(): void {
		if ( ! $telegram_bot_name = carbon_get_theme_option( 'cfe_telegram_bot_name' ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}

		$_user_id    = get_current_user_id();
		$telegram_id = carbon_get_user_meta( $_user_id, 'telegram_chat_id' );
		?>
        <div class="wrap">
            <h2>Login with telegram</h2>
            <hr>
            <h3>This will allow you to receive messages in the telegram bot</h3>
			<?php if ( $telegram_id ): ?>
                <h3>You are logged in via telegram. Your telegram ID: <?php echo $telegram_id; ?></h3>
			<?php endif; ?>
            <div id="telegram-login">
                <script async src="https://telegram.org/js/telegram-widget.js?22"
                        data-telegram-login="<?php echo $telegram_bot_name ?>"
                        data-size="large"
                        data-auth-url="<?php echo admin_url( 'edit.php?post_type=contact_form&page=custom_telegram_page' ); ?>"
                        data-request-access="write"></script>
            </div>
        </div>
		<?php if ( $telegram_id ): ?>
            <h2>You can send it to yourself</h2>
            <hr>
            <form method="post" action="">
                <label>Message:</label>
                <label>
                    <input type="text" name="telegram_message">
                </label>
                <input type="submit" name="send_to_telegram" value="submit">
            </form>
			<?php
			if ( isset( $_POST['send_to_telegram'] ) && ! empty( $_POST['telegram_message'] ) ) {
				$message = sanitize_text_field( $_POST['telegram_message'] );
				self::send_message( $message, $telegram_id );
			}
		endif;
	}

	public static function send_message( $message, $chat_id ): \WP_Error|bool|array {
		if ( ! $bot_token = carbon_get_theme_option( 'cfe_telegram_api_key' ) ) {
			return false;
		}
		if ( empty( $message ) ) {
			return false;
		}
		if ( ! $chat_id ) {
			return false;
		}
		$api_url = "https://api.telegram.org/bot$bot_token/sendMessage";
		$data    = [
			'chat_id'    => $chat_id,
			'text'       => $message,
			'parse_mode' => 'HTML'
		];

		$query    = http_build_query( $data );
		$response = wp_remote_get( "$api_url?$query" );
		error_log( json_encode( $response ) );
		if ( is_wp_error( $response ) ) {
			error_log( 'Telegram API error: ' . $response->get_error_message() );
		}

		return $response;
	}
}

$telegram = new Telegram();
$telegram->add_login_button();