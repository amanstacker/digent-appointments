<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings_Fields {

	private static function get_option_key( $args = [] ) {
		if ( ! empty( $args['tab'] ) ) {
			return 'dgap_' . sanitize_key( $args['tab'] ) . '_settings';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( wp_unslash( $_GET['tab'] ?? 'general' ) );

		return "dgap_{$active_tab}_settings";
	}

	private static function get_value( $id, $args = [] ) {
		$option_key = self::get_option_key( $args );
		$options    = get_option( $option_key, [] );

		if ( isset( $options[ $id ] ) && $options[ $id ] !== '' ) {
			return $options[ $id ];
		}

		return $args['default'] ?? '';
	}

	private static function get_field_name( $id, $args = [] ) {
		$option_key = self::get_option_key( $args );

		return $option_key . '[' . $id . ']';
	}

	private static function render_description( $args ) {
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	public static function select( $args ) {
		$value      = self::get_value( $args['id'], $args );
		$field_name = self::get_field_name( $args['id'], $args );
		?>
		<select name="<?php echo esc_attr( $field_name ); ?>">
			<?php foreach ( $args['options'] as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( (string) $value, (string) $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
		self::render_description( $args );
	}

	public static function number( $args ) {
		$value      = self::get_value( $args['id'], $args );
		$field_name = self::get_field_name( $args['id'], $args );
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $args['min'] ?? 0 ); ?>"
			step="<?php echo esc_attr( $args['step'] ?? 1 ); ?>"
		/>
		<?php
		self::render_description( $args );
	}

	public static function toggle( $args ) {
		$value      = self::get_value( $args['id'], $args );
		$field_name = self::get_field_name( $args['id'], $args );
		?>
		<label class="dgap-switch">
			<input
				type="hidden"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="0"
			/>
			<input
				type="checkbox"
				name="<?php echo esc_attr( $field_name ); ?>"
				value="1"
				<?php checked( (int) $value, 1 ); ?>
			/>
			<span class="dgap-slider"></span>
		</label>
		<?php
		self::render_description( $args );
	}

	public static function text( $args ) {
		$value            = self::get_value( $args['id'], $args );
		$field_name       = self::get_field_name( $args['id'], $args );
		$type             = $args['type'] ?? 'text';
		$placeholder      = $args['placeholder'] ?? '';
		$autocomplete     = $args['autocomplete'] ?? '';
		$disable_autofill = ! empty( $args['disable_autofill'] );
		?>
		<input
			type="<?php echo esc_attr( $type ); ?>"
			name="<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
			<?php if ( '' !== $autocomplete ) : ?>
				autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
			<?php endif; ?>
			<?php if ( $disable_autofill ) : ?>
				data-lpignore="true"
				data-1p-ignore="true"
				readonly
				onfocus="this.removeAttribute('readonly');"
			<?php endif; ?>
		/>
		<?php
		self::render_description( $args );
	}

	public static function password( $args ) {
		$args['type'] = 'password';
		if ( empty( $args['autocomplete'] ) ) {
			$args['autocomplete'] = 'new-password';
		}
		self::text( $args );
	}

	public static function textarea( $args ) {
		$value       = self::get_value( $args['id'], $args );
		$field_name  = self::get_field_name( $args['id'], $args );
		$placeholder = $args['placeholder'] ?? '';
		?>
		<textarea
			name="<?php echo esc_attr( $field_name ); ?>"
			rows="5"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
		self::render_description( $args );
	}
}
