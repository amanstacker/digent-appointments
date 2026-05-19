<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings_Fields {

	private static function get_option_key() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( $_GET['tab'] ?? 'general' );

		return "dgap_{$active_tab}_settings";
	}

	private static function get_value( $id ) {

		$option_key = self::get_option_key();
		$options    = get_option( $option_key, [] );

		return $options[ $id ] ?? '';
	}

	private static function get_field_name( $id ) {

		$option_key = self::get_option_key();

		return $option_key . '[' . $id . ']';
	}

	public static function select( $args ) {

		$value      = self::get_value( $args['id'] );
		$field_name = self::get_field_name( $args['id'] );
		?>
		<select name="<?php echo esc_attr( $field_name ); ?>">
			<?php foreach ( $args['options'] as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public static function number( $args ) {

		$value      = self::get_value( $args['id'] );
		$field_name = self::get_field_name( $args['id'] );
		?>
		<input
			type="number"
			name="<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $args['min'] ?? 0 ); ?>"
			step="<?php echo esc_attr( $args['step'] ?? 1 ); ?>"
		/>
		<?php
	}

	public static function toggle( $args ) {

		$value      = self::get_value( $args['id'] );
		$field_name = self::get_field_name( $args['id'] );
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
				<?php checked( $value, 1 ); ?>
			/>
			<span class="dgap-slider"></span>
		</label>
		<?php
	}

	public static function text( $args ) {

		$value      = self::get_value( $args['id'] );
		$field_name = self::get_field_name( $args['id'] );
		?>
		<input
			type="text"
			name="<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<?php
	}

	public static function textarea( $args ) {

		$value      = self::get_value( $args['id'] );
		$field_name = self::get_field_name( $args['id'] );
		?>
		<textarea
			name="<?php echo esc_attr( $field_name ); ?>"
			rows="5"
			class="large-text"
		><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}
}
