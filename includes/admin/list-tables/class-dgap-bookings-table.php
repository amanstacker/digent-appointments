<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Bookings_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'        => '<input type="checkbox" />',
			'booking'   => esc_html__( 'Booking', 'digent-appointments' ),
			'customer'  => esc_html__( 'Customer', 'digent-appointments' ),
			'service'   => esc_html__( 'Service & Staff', 'digent-appointments' ),
			'date_time' => esc_html__( 'Date & Time', 'digent-appointments' ),
			'price'     => esc_html__( 'Price', 'digent-appointments' ),
			'status'    => esc_html__( 'Status', 'digent-appointments' ),
			'actions'   => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			esc_attr( $item['id'] )
		);
	}

	public function column_booking( $item ) {
		return sprintf(
			'<strong class="dgap-booking-id">#%d</strong><br><strong class="dgap-booking-loc">%s</strong>',
			esc_html( $item['id'] ),
			esc_html( $item['location_name'] ?? '' )
		);
	}

	public function column_customer( $item ) {
		$name  = esc_html( trim( $item['customer_name'] ?? '' ) ?: __( 'Guest', 'digent-appointments' ) );
		$email = ! empty( $item['customer_email'] ) ? '<br><span class="dgap-muted">' . esc_html( $item['customer_email'] ) . '</span>' : '';
		return '<strong>' . $name . '</strong>' . $email;
	}

	public function column_service( $item ) {
		$service = esc_html( $item['service_name'] ?? '' );
		$staff   = ! empty( $item['staff_name'] ) ? '<br><span class="dgap-muted">' . esc_html( $item['staff_name'] ) . '</span>' : '';
		return '<strong>' . $service . '</strong>' . $staff;
	}

	public function column_date_time( $item ) {
		$date_str = ! empty( $item['booking_date'] ) ? date_i18n( 'M j, Y', strtotime( $item['booking_date'] ) ) : '';
		$start    = ! empty( $item['start_time'] ) ? date_i18n( 'g:i A', strtotime( $item['start_time'] ) ) : '';
		$end      = ! empty( $item['end_time'] ) ? date_i18n( 'g:i A', strtotime( $item['end_time'] ) ) : '';

		return sprintf(
			'<strong>%s</strong><br><span class="dgap-muted">%s – %s</span>',
			esc_html( $date_str ),
			esc_html( $start ),
			esc_html( $end )
		);
	}

	public function column_price( $item ) {
		return '<strong>$' . esc_html( number_format( (float) $item['price'], 2 ) ) . '</strong>';
	}

	public function column_status( $item ) {
		$map = [
			'pending'   => 'warning',
			'confirmed' => 'active',
			'cancelled' => 'inactive',
			'completed' => 'success',
		];

		$status_key = strtolower( $item['status'] ?? 'pending' );
		$type       = $map[ $status_key ] ?? 'default';

		ob_start();
		?>
		<div class="dgap-status-wrap" data-id="<?php echo esc_attr( $item['id'] ); ?>">

			<span class="dgap-badge dgap-badge-<?php echo esc_attr( $type ); ?>" data-status="<?php echo esc_attr( $status_key ); ?>">
				<?php echo esc_html( ucfirst( $status_key ) ); ?>
			</span>

			<button type="button" class="dgap-status-menu" title="<?php esc_attr_e( 'Change Status', 'digent-appointments' ); ?>">
				<span class="dashicons dashicons-ellipsis"></span>
			</button>

			<div class="dgap-status-popover" style="display:none;">
				<strong><?php esc_html_e( 'Change Status', 'digent-appointments' ); ?></strong>

				<select class="dgap-status-select">
					<option value="pending" <?php selected( $status_key, 'pending' ); ?>><?php esc_html_e( 'Pending', 'digent-appointments' ); ?></option>
					<option value="confirmed" <?php selected( $status_key, 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'digent-appointments' ); ?></option>
					<option value="cancelled" <?php selected( $status_key, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'digent-appointments' ); ?></option>
					<option value="completed" <?php selected( $status_key, 'completed' ); ?>><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></option>
				</select>
				<hr>
				<label class="dgap-notify">
					<input type="checkbox" class="dgap-status-notify">
					<?php esc_html_e( 'Notify customer', 'digent-appointments' ); ?>
				</label>

				<button type="button" class="button button-small button-primary dgap-status-update">
					<?php esc_html_e( 'Update', 'digent-appointments' ); ?>
				</button>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	public function column_actions( $item ) {
		return sprintf(
			'<div class="dgap-table-actions">
				<button type="button" class="button button-small dgap-edit dgap-action-btn" data-entity="booking" data-id="%1$d" title="%2$s" aria-label="%2$s">
					<span class="dashicons dashicons-edit"></span>
				</button>
				<button type="button" class="button button-small button-secondary dgap-delete dgap-action-btn is-delete" data-entity="booking" data-id="%1$d" title="%3$s" aria-label="%3$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_attr( $item['id'] ),
			esc_attr__( 'Edit', 'digent-appointments' ),
			esc_attr__( 'Delete', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No bookings match your current filter criteria.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return [
			'status'       => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			'location_id'  => isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0,
			'service_id'   => isset( $_GET['service_id'] ) ? absint( $_GET['service_id'] ) : 0,
			'staff_id'  => isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0,
			'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
			's'         => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
	}

	public function prepare_items() {
		$filters   = $this->get_active_filters();
		$all_items = DGAP_Booking_Repo::get_filtered( $filters );

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $all_items );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );

		$this->items           = array_slice( $all_items, ( $current_page - 1 ) * $per_page, $per_page );
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
