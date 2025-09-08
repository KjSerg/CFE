<?php

namespace CFE;

class Field {
	/**
	 * @var int|mixed
	 */
	private mixed $index;
	/**
	 * @var mixed|string
	 */
	private mixed $column_width_cls;
	/**
	 * @var mixed|string
	 */
	private mixed $column_title;
	private mixed $field;
	/**
	 * @var array|string[]
	 */
	private array $exclusion;

	public function __construct( $field ) {
		$this->field     = $field;
		$this->exclusion = array( 'html', 'button' );
	}

	public function set_index( $index = 0 ): void {
		$this->index = $index;
	}

	public function set_column_width_cls( $cls = '' ): void {
		$this->column_width_cls = $cls;
	}

	public function set_title( $title = '' ): void {
		$this->column_title = $title;
	}

	private function render_field_footer(): void {
		$field_type = $this->field['_type'];
		if ( in_array( $field_type, $this->exclusion ) ) {
			return;
		}
		echo '</label>';
	}

	private function render_field_head(): void {
		$field_type = $this->field['_type'];
		if ( in_array( $field_type, $this->exclusion ) ) {
			return;
		}
		$cls = esc_attr( $this->column_width_cls );
		echo ' <label class="form-label ' . $cls . '">';
		if ( $this->column_title ) {
			echo "<span>" . esc_html( $this->column_title ) . "</span>";
		}
	}


	private function the_text_field( $field, $field_ID ): void {
		$field_custom_regular_expression = $field['field_custom_regular_expression'] ?? '';
		if ( $field['type'] == 'email' ) {
			$field_custom_regular_expression = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])";
		}
		?>
        <input title="<?php echo esc_attr( $field['field_placeholder'] ) ?? ''; ?>"
               class="input_st <?php echo esc_attr( $field['field_css_class'] ) ?? ''; ?>"
               id="<?php echo esc_attr( $field_ID ) ?>"
               type="<?php echo esc_attr( $field['type'] ) ?? 'text'; ?>"
               name="<?php echo esc_attr( $field['field_name'] ) ?? ''; ?>"
               placeholder="<?php echo esc_attr( $field['field_placeholder'] ) ?? ''; ?>"
			<?php if ( $field['field_required'] ) {
				echo 'required="required"';
			} ?>
			<?php if ( $field_custom_regular_expression ) {
				echo 'data-reg="' . $field_custom_regular_expression . '"';
			} ?>
        >
		<?php
	}

	private function the_textarea( $field, $field_ID ): void {
		?>
        <textarea title="<?php echo esc_attr( $field['field_placeholder'] ) ?? ''; ?>"
                  class="input_st <?php echo esc_attr( $field['field_css_class'] ) ?? ''; ?>"
                  name="<?php echo esc_attr( $field['field_name'] ) ?? ''; ?>"
                  id="<?php echo esc_attr( $field_ID ) ?>"
                  placeholder="<?php echo esc_attr( $field['field_placeholder'] ) ?? ''; ?>"
		<?php if ( $field['field_required'] ) {
			echo 'required="required"';
		} ?>
    ></textarea>
		<?php
	}

	private function the_select( $field, $field_ID ): void {
		$name              = $field['field_name'] ?? '';
		$field_placeholder = $field['field_placeholder'] ?? '';
		$value_type        = $field['value_type'] ?? '';
		$field_placeholder = esc_attr( $field_placeholder );
		$value_type        = esc_attr( $value_type );
		$name              = str_replace( ' ', '_', $name );
		$name              = str_replace( '-', '_', $name );
		$name              = esc_attr( $name );
		$multiple          = $field['multiple'];
		if ( $multiple ) {
			$name .= '[]';
		}
		?>
        <select title="" class="select_st <?php echo esc_attr( $field['field_css_class'] ?? '' ); ?>"
                id="<?php echo esc_attr( $field_ID ) ?>"
			<?php if ( $field['field_required'] ) {
				echo 'required="required"';
			} ?>
			<?php if ( $multiple ) {
				echo 'multiple';
			} ?>
                name="<?php echo $name ?>"
        >
			<?php if ( $field_placeholder ): ?>
                <option disabled
					<?php if ( ! $multiple ) {
						echo 'selected';
					} ?>
                >
					<?php echo $field_placeholder ?>
                </option>
			<?php endif; ?>
			<?php if ( $value_type == 'default' ): if ( $values = $field['values'] ): foreach ( $values as $value ): ?>
                <option><?php echo $value['option_value']; ?></option>
			<?php endforeach; endif; endif; ?>
			<?php if ( $value_type == 'association' ): if ( $values = $field['field_association'] ): foreach ( $values as $value ):
				$_id = $value['id'];
				$_title = get_the_title( $_id );
				?>
                <option value="<?php echo $_title . ' [' . $_id . ']'; ?>"><?php echo $_title; ?></option>
			<?php endforeach; endif; endif; ?>
        </select>
		<?php
	}

	private function the_checkbox_radio( $field, $field_ID ): void {
		$type              = $field['type'] ?? 'checkbox';
		$field_name        = $field['field_name'];
		$field_name_hide   = $field['field_name_hide'];
		$field_required    = $field['field_required'];
		$value_type        = $field['value_type'];
		$wrapper_css_class = $field['wrapper_css_class'];
		$name              = str_replace( ' ', '_', $field_name );
		$name              = str_replace( '-', '_', $name );
		$name              = esc_attr( $name );
		$field_name        = esc_html( $field_name );
		$encodedSVG        = \rawurlencode( \str_replace( [ "\r", "\n" ], ' ', \cfe_check_svg() ) );
		?>
        <div class="checkbox-group <?php echo $wrapper_css_class; ?>" id="<?php esc_attr( $field_ID ); ?>">
			<?php if ( ! $field_name_hide ): ?>
                <div class="checkbox-group__title ">
					<?php echo $field_name; ?>
                </div>
			<?php endif; ?>
            <div class="checked-group form-label">
				<?php if ( $value_type == 'default' ): if ( $values = $field['values'] ): foreach ( $values as $value ): ?>
                    <div class="form-consent">
                        <label class="form-consent-box">
                            <input name="<?php echo $name; ?>[]"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo strip_tags( $value['option_value'] ); ?>"
                                   type="<?php echo $type ?>">
                            <span><img src="data:image/svg+xml;utf8,<?php echo $encodedSVG ?>" alt=""></span>
                        </label>
                        <div class="form-consent-text">
							<?php echo $value['option_value']; ?>
                        </div>
                    </div>
				<?php endforeach; endif; endif; ?>
				<?php if ( $value_type == 'association' ): if ( $values = $field['field_association'] ): foreach ( $values as $value ):
					$_id = $value['id'];
					$_title = get_the_title( $_id );
					?>
                    <div class="form-consent">
                        <label class="form-consent-box">
                            <input name="<?php echo $name; ?>[]"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo $_title . ' [' . $_id . ']'; ?>" type="<?php echo $type ?>">
                            <span><img src="data:image/svg+xml;utf8,<?php echo $encodedSVG ?>" alt=""></span>
                        </label>
                        <div class="form-consent-text">
							<?php echo $_title; ?>
                        </div>
                    </div>
				<?php endforeach; endif; endif; ?>
            </div>
        </div>
		<?php
	}

	private function the_file_field( $field, $field_ID ): void {
		$multiple = $field['multiple'];
		?>
        <div class="form-group">
            <label class="up_file">
                <input class="upfile_hide file-js <?php echo esc_attr( $field['field_css_class'] ); ?>"
                       name="upfile[]"
                       id="<?php echo esc_attr( $field_ID ); ?>"
					<?php if ( $multiple ) {
						echo 'multiple';
					} ?>
					<?php if ( $field['field_required'] ) {
						echo 'required="required"';
					} ?>
                       accept="<?php echo $field['file_types']; ?>"
                       type="file">
                <span class="up_file_text">
                <?php echo esc_html( $field['field_placeholder'] ); ?>
            </span>
            </label>
            <div class="result-upload"></div>
        </div>
		<?php
	}

	private function the_form_button( $field, $column_width_cls = '' ): void {
		?>
        <button class="button <?php echo esc_attr( $field['button_css_class'] . ' ' . $column_width_cls ); ?>"
                type="<?php echo $field['button_type']; ?>">
			<?php echo esc_html( $field['button_text'] ); ?>
        </button>
		<?php
	}

	private function the_html( $field, $column_width_cls = '' ): void {
		$html = $field['html'] ?? '';
		if ( $html ) {
			$html = "<div class='form-html-element $column_width_cls'>" . $html . "</div>";
		}
		echo $html;
	}

	public function render(): void {
		$field      = $this->field;
		$field_type = $field['_type'];
		$field_ID   = 'field_' . $this->field['_type'] . '_' . $this->index;
		$this->render_field_head();
		switch ( $field_type ) {
			case 'text':
				$this->the_text_field( $field, $field_ID );
				break;
			case 'textarea':
				$this->the_textarea( $field, $field_ID );
				break;
			case 'select':
				$this->the_select( $field, $field_ID );
				break;
			case 'checkbox_radio':
				$this->the_checkbox_radio( $field, $field_ID );
				break;
			case 'file':
				$this->the_file_field( $field, $field_ID );
				break;
			case 'button':
				$this->the_form_button( $field, $field_ID );
				break;
			case 'html':
				$this->the_html( $field, $field_ID );
				break;
		}
		$this->render_field_footer();
	}
}

