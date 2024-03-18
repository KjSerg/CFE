<?php
function the_field( $field, $field_index ) {
	$field_type = $field['_type'];
	$field_ID   = 'field_' . $field['_type'] . '_' . $field_index;
	if ( $field_type == 'text' ) {
		the_text_field( $field, $field_ID );
	} elseif ( $field_type == 'textarea' ) {
		the_textarea( $field, $field_ID );
	} elseif ( $field_type == 'select' ) {
		the_select( $field, $field_ID );
	} elseif ( $field_type == 'checkbox_radio' ) {
		the_checkbox_radio( $field, $field_ID );
	} elseif ( $field_type == 'file' ) {
		the_file_field( $field, $field_ID );
	} elseif ( $field_type == 'button' ) {
		the_form_button( $field );
	} elseif ( $field_type == 'html' ) {
		the_html( $field );
	}
}

function the_html( $field ) {
	echo $field['html'] ?? '';
}

function the_text_field( $field, $field_ID ) {
	$field_custom_regular_expression = $field['field_custom_regular_expression'] ?? '';
	if ( $field['type'] == 'email' ) {
		$field_custom_regular_expression = "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])";
	}
	?>
    <input class="input_st <?php echo $field['field_css_class'] ?? ''; ?>"
           id="<?php echo $field_ID ?>"
           type="<?php echo $field['type'] ?? 'text'; ?>"
           name="<?php echo $field['field_name'] ?? ''; ?>"
           placeholder="<?php echo $field['field_placeholder'] ?? ''; ?>"
		<?php if ( $field['field_required'] ) {
			echo 'required="required"';
		} ?>
		<?php if ( $field_custom_regular_expression ) {
			echo 'data-reg="' . $field_custom_regular_expression . '"';
		} ?>
    >
	<?php
}

function the_textarea( $field, $field_ID ) {
	?>
    <textarea class="input_st <?php echo $field['field_css_class'] ?? ''; ?>"
              name="<?php echo $field['field_name'] ?? ''; ?>"
              id="<?php echo $field_ID ?>"
              placeholder="<?php echo $field['field_placeholder'] ?? ''; ?>"
		<?php if ( $field['field_required'] ) {
			echo 'required="required"';
		} ?>
    ></textarea>
	<?php
}

function the_select( $field, $field_ID ) {
	$name              = $field['field_name'];
	$field_placeholder = $field['field_placeholder'];
	$value_type        = $field['value_type'];
	$name              = str_replace( ' ', '_', $name );
	$name              = str_replace( '-', '_', $name );
	$multiple          = $field['multiple'];
	if ( $multiple ) {
		$name .= '[]';
	}
	?>
    <select class="select_st <?php echo $field['field_css_class'] ?? ''; ?>"
            id="<?php echo $field_ID ?>"
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

function the_checkbox_radio( $field, $field_ID ) {
	$type              = $field['type'] ?? 'checkbox';
	$field_name        = $field['field_name'];
	$field_name_hide   = $field['field_name_hide'];
	$field_required    = $field['field_required'];
	$value_type        = $field['value_type'];
	$wrapper_css_class = $field['wrapper_css_class'];
	$name              = str_replace( ' ', '_', $field_name );
	$name              = str_replace( '-', '_', $name );
	?>
    <div class="checkbox-wrapper form-group <?php echo $wrapper_css_class; ?>">
		<?php if ( ! $field_name_hide ): ?>
            <div class="checkbox-wrapper__title form-group__title">
				<?php echo $field_name; ?>
            </div>
		<?php endif; ?>
        <div class="checked-group ">
			<?php if ( $type == 'checkbox' ): ?>
				<?php if ( $value_type == 'default' ): if ( $values = $field['values'] ): foreach ( $values as $value ): ?>
                    <div class="checked-item">
                        <label class="check-item">
                            <input class="check_st " name="<?php echo $name; ?>[]"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo $value['option_value']; ?>" type="checkbox"
                            >
                            <span> </span>
                            <i class="check-item__text"><?php echo $value['option_value']; ?></i>
                        </label>
                    </div>
				<?php endforeach; endif; endif; ?>
				<?php if ( $value_type == 'association' ): if ( $values = $field['field_association'] ): foreach ( $values as $value ):
					$_id = $value['id'];
					$_title = get_the_title( $_id );
					?>
                    <div class="checked-item">
                        <label class="check-item">
                            <input class="check_st " name="<?php echo $name; ?>[]"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo $_title . ' [' . $_id . ']'; ?>" type="checkbox"
                            >
                            <span> </span>
                            <i class="check-item__text"><?php echo $_title; ?></i>
                        </label>
                    </div>
				<?php endforeach; endif; endif; ?>

			<?php else: ?>

				<?php if ( $value_type == 'default' ): if ( $values = $field['values'] ): foreach ( $values as $value ): ?>
                    <div class="select-product__item-continue">
                        <label class="switch_st">
                            <input class="" name="<?php echo $name; ?>"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo $value['option_value']; ?>" type="radio"
                            >
                            <span></span>
                        </label>
                        <span><?php echo $value['option_value']; ?></span>
                    </div>
				<?php endforeach; endif; endif; ?>
				<?php if ( $value_type == 'association' ): if ( $values = $field['field_association'] ): foreach ( $values as $value ):
					$_id = $value['id'];
					$_title = get_the_title( $_id );
					?>
                    <div class="select-product__item-continue">
                        <label class="switch_st">
                            <input class="" name="<?php echo $name; ?>"
								<?php if ( $field_required ) {
									echo 'data-required';
								} ?>
                                   value="<?php echo $_title . ' [' . $_id . ']'; ?>" type="radio"
                            >
                            <span></span>
                        </label>
                        <span><?php echo $_title; ?></span>
                    </div>
				<?php endforeach; endif; endif; ?>


			<?php endif; ?>
        </div>
    </div>
	<?php
}

function the_file_field( $field, $field_ID ) {
	$multiple = $field['multiple'];
	?>
    <div class="form-group">
        <label class="up_file">
            <input class="upfile_hide file-js <?php echo $field['field_css_class']; ?>"
                   name="upfile[]"
                   id="<?php echo $field_ID; ?>"
				<?php if ( $multiple ) {
					echo 'multiple';
				} ?>
				<?php if ( $field['field_required'] ) {
					echo 'required="required"';
				} ?>
                   accept="<?php echo $field['file_types']; ?>"
                   type="file">
            <span class="up_file_text">
                <?php echo $field['field_placeholder']; ?>
            </span>
        </label>
        <div class="result-upload">

        </div>
    </div>
	<?php
}

function the_form_button( $field ) {
	?>
    <button class="btn_st <?php echo $field['button_css_class']; ?>" type="<?php echo $field['button_type']; ?>">
        <span><?php echo $field['button_text']; ?></span>
    </button>
	<?php
}