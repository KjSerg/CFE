<?php
global $shortcode_counter;
global $preloader;
$shortcode_counter = 0;
$preloader         = false;
function get_form_html( $id ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	global $shortcode_counter;
	global $preloader;
	$shortcode_counter ++;
	$ajax_url = site_url() . '/wp-admin/admin-ajax.php';
	ob_start();
	$contact_form_rows = carbon_get_post_meta( $id, 'contact_form_rows' );
	if ( ! $preloader ) {
		$preloader = CFE__ASSETS_URL . '/img/loading.gif';
		?>
        <div class="cfe-preloader">
            <img src="<?php echo $preloader; ?>" alt="loading.gif">
        </div>
		<?php
	}
	if ( $contact_form_rows ) {
		?>
        <form action="<?php echo $ajax_url; ?>" method="post" novalidate=""
              id="custom-form-<?php echo $id ?>-<?php echo $shortcode_counter ?>"
              class="custom-form-js custom-form-render" enctype="multipart/form-data">
            <input type="hidden" name="action" value="send_custom_form">
            <input type="hidden" name="form_id" value="<?php echo $id ?>">
			<?php
			foreach ( $contact_form_rows as $row_index => $row ):
				if ( $columns = $row['columns'] ):
					$field_index = $shortcode_counter . '_' . $row_index;
					?>
                    <div class="form-horizontal">
						<?php foreach ( $columns as $column_index => $column ):
							$column_width_cls = $column['column_width'];
							$field_index .= $column_index;
							$fields = $column['field'];
							if ( $fields ):
								?>
                                <div class="form-group <?php echo $column_width_cls; ?>">
									<?php foreach ( $fields as $_index => $field ): $field_index .= $_index;
										the_field( $field, $field_index );
									endforeach; ?>
                                </div>
							<?php endif; ?>
						<?php endforeach; ?>
                    </div>
				<?php
				endif;
			endforeach;
			?>
        </form>
		<?php
	}
	return ob_get_clean();
}