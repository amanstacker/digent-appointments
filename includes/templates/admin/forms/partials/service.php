<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_selection_mode = $form['settings']['selection_mode'] ?? 'location_service_staff';
$dgap_services       = ( 'service_only' === $dgap_selection_mode )
	? DGAP_Booking_Repo::get_all_scheduled_services()
	: [];
$dgap_gen_opts       = get_option( 'dgap_general_settings', [] );
$dgap_show_price     = isset( $dgap_gen_opts['show_price'] ) ? ! empty( $dgap_gen_opts['show_price'] ) : empty( $dgap_gen_opts['hide_price'] );
$dgap_show_decimals  = isset( $dgap_gen_opts['show_decimals'] ) ? ! empty( $dgap_gen_opts['show_decimals'] ) : empty( $dgap_gen_opts['hide_decimals'] );
$dgap_currency_sym   = dgap_get_currency_symbol( $dgap_gen_opts['currency_code'] ?? 'USD' );
?>
<select id="dgap-service">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Service --', 'digent-appointments' ); ?></option>
	<?php if ( ! empty( $dgap_services ) && is_array( $dgap_services ) ) : ?>
		<?php foreach ( $dgap_services as $dgap_srv ) :
			$dgap_raw_price = (float) ( $dgap_srv['price'] ?? 0 );
			$dgap_decimals  = ( ! $dgap_show_decimals && floor( $dgap_raw_price ) === $dgap_raw_price ) ? 0 : 2;
			$dgap_price_str = $dgap_currency_sym . number_format( $dgap_raw_price, $dgap_decimals );
			$dgap_opt_label = $dgap_show_price ? ( $dgap_srv['name'] . ' - ' . $dgap_price_str ) : $dgap_srv['name'];
		?>
			<option value="<?php echo esc_attr( $dgap_srv['id'] ); ?>" data-price="<?php echo esc_attr( $dgap_price_str ); ?>">
				<?php echo esc_html( $dgap_opt_label ); ?>
			</option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>