<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Bookings_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'           => '<input type="checkbox" />',
			'booking'      => __( 'Booking', 'digent-appointments' ),
			'customer'     => __( 'Customer', 'digent-appointments' ),
			'service'      => __( 'Service', 'digent-appointments' ),
			'date_time'    => __( 'Date & Time', 'digent-appointments' ),
			'price'        => __( 'Price', 'digent-appointments' ),
			'status'       => __( 'Status', 'digent-appointments' ),
			'actions'      => __( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			$item['id']
		);
	}

	public function column_booking( $item ) {
		return sprintf(
			'<strong>#%d</strong><br><span class="dgap-muted">%s</span>',
			$item['id'],
			esc_html( $item['location_name'] ?? '' )
		);
	}

	public function column_customer( $item ) {
		return esc_html( $item['customer_name'] ?? '' );
	}

	public function column_service( $item ) {
		return esc_html( $item['service_name'] ?? '' );
	}

	public function column_date_time( $item ) {
		return sprintf(
			'%s<br><span class="dgap-muted">%s - %s</span>',
			esc_html( $item['booking_date'] ),
			esc_html( $item['start_time'] ),
			esc_html( $item['end_time'] )
		);
	}

	public function column_price( $item ) {
		return $item['price'];
	}

	public function column_status( $item ) {

	$map = [
		'pending'   => 'warning',
		'confirmed' => 'active',
		'cancelled' => 'inactive',
		'completed' => 'success',
	];

	$type = $map[ $item['status'] ] ?? 'default';

	ob_start();
	?>
	<div class="dgap-status-wrap" data-id="<?php echo esc_attr( $item['id'] ); ?>">

		<span class="dgap-badge dgap-badge-<?php echo esc_attr( $type ); ?>" data-status="<?php echo esc_attr( $item['status'] ); ?>">
			<?php echo esc_html( ucfirst( $item['status'] ) ); ?>
		</span>

		<button type="button" class="dgap-status-menu">
			<span class="dashicons dashicons-ellipsis"></span>
		</button>

		<div class="dgap-status-popover" style="display:none;">
			<strong><?php esc_html_e( 'Change Status', 'digent-appointments' ); ?></strong>

			<select class="dgap-status-select">
				<option value="pending"><?php echo esc_html__( 'Pending', 'digent-appointments' ); ?></option>
				<option value="confirmed"><?php echo esc_html__( 'Confirmed', 'digent-appointments'); ?></option>
				<option value="cancelled"><?php echo esc_html__( 'Cancelled', 'digent-appointments'); ?></option>
				<option value="completed"><?php echo esc_html__( 'Completed', 'digent-appointments'); ?></option>
			</select>
					<hr>
			<label class="dgap-notify">
				<input type="checkbox" class="dgap-status-notify">
				<?php esc_html_e( 'Notify customer', 'digent-appointments' ); ?>
			</label>

			<button class="button button-small button-primary dgap-status-update">
				<?php esc_html_e( 'Update', 'digent-appointments' ); ?>
			</button>
		</div>

	</div>
	<?php
	return ob_get_clean();
}


	public function column_actions( $item ) {
		return sprintf(
			'<button class="button button-small dgap-edit" data-entity="booking" data-id="%1$d">%2$s</button>
			 <button class="button button-small button-secondary dgap-delete" data-entity="booking" data-id="%1$d">%3$s</button>',
			$item['id'],
			__( 'Edit', 'digent-appointments' ),
			__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Booking_Repo::get_all(); // join based data
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
