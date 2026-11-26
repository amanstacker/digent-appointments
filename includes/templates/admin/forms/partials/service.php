<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_selection_mode = $form['settings']['selection_mode'] ?? 'location_service_staff';
$dgap_services       = ( 'service_only' === $dgap_selection_mode )
	? DGAP_Booking_Repo::get_all_scheduled_services()
	: [];
?>
<select id="dgap-service">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Service --', 'digent-appointments' ); ?></option>
	<?php if ( ! empty( $dgap_services ) && is_array( $dgap_services ) ) : ?>
		<?php foreach ( $dgap_services as $dgap_srv ) : ?>
			<option value="<?php echo esc_attr( $dgap_srv['id'] ); ?>">
				<?php echo esc_html( $dgap_srv['name'] . ' - $' . $dgap_srv['price'] ); ?>
			</option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>