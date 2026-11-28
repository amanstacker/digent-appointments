<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Customers_Table extends WP_List_Table {

	/** @var array<int, array{total:int, upcoming:int}> */
	private $booking_counts = [];

	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'name'     => esc_html__( 'Customer Name', 'digent-appointments' ),
			'email'    => esc_html__( 'Email', 'digent-appointments' ),
			'phone'    => esc_html__( 'Phone', 'digent-appointments' ),
			'bookings' => esc_html__( 'Bookings', 'digent-appointments' ),
			'status'   => esc_html__( 'Status', 'digent-appointments' ),
			'actions'  => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function get_primary_column_name() {
		return 'name';
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			(int) $item['id']
		);
	}

	public function column_name( $item ) {
		$full_name = trim( $item['first_name'] . ' ' . $item['last_name'] );
		return sprintf(
			'<a href="#" class="dgap-edit dgap-row-edit-link" data-entity="customer" data-title="%2$s" data-id="%1$d"><strong class="dgap-location-title">%3$s</strong></a>',
			(int) $item['id'],
			esc_attr__( 'Edit Customer', 'digent-appointments' ),
			esc_html( $full_name )
		);
	}

	public function column_email( $item ) {
		return esc_html( $item['email'] );
	}

	public function column_phone( $item ) {
		$phone_display = '';
		if ( ! empty( $item['phone_dial_code'] ) ) {
			$phone_display .= $item['phone_dial_code'] . ' ';
		}
		$phone_display .= $item['phone'] ?: '-';

		return esc_html( $phone_display );
	}

	public function column_bookings( $item ) {
		$cid      = (int) $item['id'];
		$counts   = $this->booking_counts[ $cid ] ?? [ 'total' => 0, 'upcoming' => 0 ];
		$total    = (int) $counts['total'];
		$upcoming = (int) $counts['upcoming'];

		if ( $total === 0 ) {
			return '<span class="dgap-muted-text">0</span>';
		}

		$full_name = trim( $item['first_name'] . ' ' . $item['last_name'] );
		$label     = sprintf( '%d', $total );
		if ( $upcoming > 0 ) {
			$label .= sprintf( ' <span class="dgap-upcoming-pill">%d %s</span>', $upcoming, esc_html__( 'Upcoming', 'digent-appointments' ) );
		}

		return sprintf(
			'<button type="button" class="button-link dgap-customer-bookings-btn dgap-bookings-count-link" data-id="%1$d" data-name="%2$s">%3$s</button>',
			$cid,
			esc_attr( $full_name ),
			$label
		);
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		$full_name = trim( $item['first_name'] . ' ' . $item['last_name'] );
		return sprintf(
			'<div class="dgap-row-actions-wrap">
				<button
					type="button"
					class="button button-small dgap-action-btn dgap-customer-bookings-btn"
					data-id="%1$d"
					data-name="%3$s"
					title="%5$s"
					aria-label="%5$s">
					<span class="dashicons dashicons-calendar-alt"></span>
				</button>
				<button
					type="button"
					class="button button-small dgap-action-btn dgap-edit"
					data-entity="customer"
					data-title="%2$s"
					data-id="%1$d"
					title="%2$s"
					aria-label="%2$s">
					<span class="dashicons dashicons-edit"></span>
				</button>
				<button
					type="button"
					class="button button-small button-secondary dgap-action-btn dgap-action-btn-danger dgap-delete"
					data-entity="customer"
					data-id="%1$d"
					data-name="%3$s"
					title="%4$s"
					aria-label="%4$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_attr( $item['id'] ),
			esc_attr__( 'Edit Customer', 'digent-appointments' ),
			esc_attr( $full_name ),
			esc_attr__( 'Delete', 'digent-appointments' ),
			esc_attr__( 'Booking Log', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No customers found matching your filters.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public function prepare_items() {
		$filters  = $this->get_active_filters();
		$per_page = 20;
		$paged    = $this->get_pagenum();
		$offset   = ( $paged - 1 ) * $per_page;

		$query_args = array_merge(
			$filters,
			[
				'limit'  => $per_page,
				'offset' => $offset,
			]
		);

		$total_items = DGAP_Customer_Repo::get_filtered( array_merge( $filters, [ 'count' => true ] ) );
		$this->items = DGAP_Customer_Repo::get_filtered( $query_args );

		if ( ! empty( $this->items ) ) {
			require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
			$this->booking_counts = DGAP_Booking_Repo::get_counts_by_customers( wp_list_pluck( $this->items, 'id' ) );
		}

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
			$this->get_primary_column_name(),
		];

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}
}
