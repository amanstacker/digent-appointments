<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Schedules_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'           => '<input type="checkbox" />',
			'location'     => esc_html__( 'Location', 'digent-appointments' ),
			'staff'        => esc_html__( 'Staff', 'digent-appointments' ),
			'service'      => esc_html__( 'Service', 'digent-appointments' ),
			'availability' => esc_html__( 'Availability', 'digent-appointments' ),
			'capacity'     => esc_html__( 'Capacity', 'digent-appointments' ),
			'status'       => esc_html__( 'Status', 'digent-appointments' ),
			'actions'      => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			esc_attr( $item['id'] )
		);
	}

	public function column_location( $item ) {
		return sprintf(
			'<a href="#" class="dgap-edit dgap-row-edit-link" data-entity="schedule" data-title="%2$s" data-id="%1$d"><strong class="dgap-location-title">%3$s</strong></a>',
			(int) $item['id'],
			esc_attr__( 'Edit Schedule', 'digent-appointments' ),
			esc_html( $item['location_name'] ?? '—' )
		);
	}

	public function column_staff( $item ) {
		$name = trim( (string) ( $item['staff_name'] ?? '' ) );
		return '<strong>' . esc_html( $name ?: '—' ) . '</strong>';
	}

	public function column_service( $item ) {
		return '<span class="dgap-muted">' . esc_html( $item['service_name'] ?? '—' ) . '</span>';
	}

	public function column_availability( $item ) {
		if ( empty( $item['availability'] ) ) {
			return '—';
		}

		$data = json_decode( $item['availability'], true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return '—';
		}

		$labels = [
			'monday'    => esc_html__( 'Mon', 'digent-appointments' ),
			'tuesday'   => esc_html__( 'Tue', 'digent-appointments' ),
			'wednesday' => esc_html__( 'Wed', 'digent-appointments' ),
			'thursday'  => esc_html__( 'Thu', 'digent-appointments' ),
			'friday'    => esc_html__( 'Fri', 'digent-appointments' ),
			'saturday'  => esc_html__( 'Sat', 'digent-appointments' ),
			'sunday'    => esc_html__( 'Sun', 'digent-appointments' ),
		];

		$output = [];
		foreach ( $data as $day => $info ) {
			if ( empty( $info['status'] ) || 'open' !== $info['status'] ) {
				continue;
			}
			$time = '—';
			if ( ! empty( $info['open'] ) && ! empty( $info['close'] ) ) {
				$time = esc_html( $info['open'] . ' → ' . $info['close'] );
			}
			$output[] = sprintf(
				'<strong>%s</strong>: %s',
				$labels[ $day ] ?? ucfirst( $day ),
				$time
			);
		}

		return ! empty( $output ) ? implode( '<br>', $output ) : '—';
	}

	public function column_capacity( $item ) {
		return '<strong>' . esc_html( $item['capacity'] ?: '—' ) . '</strong>';
	}

	public function column_status( $item ) {
		return ( (int) $item['status'] === 1 )
			? '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>'
			: '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<div class="dgap-table-actions">
				<button type="button" data-title="%2$s" data-entity="schedule" class="button button-small dgap-edit dgap-action-btn" data-id="%1$d" title="%3$s" aria-label="%3$s">
					<span class="dashicons dashicons-edit"></span>
				</button>
				<button type="button" data-entity="schedule" class="button button-small button-secondary dgap-delete dgap-action-btn is-delete" data-id="%1$d" title="%4$s" aria-label="%4$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_attr( $item['id'] ),
			esc_attr__( 'Edit Schedule', 'digent-appointments' ),
			esc_attr__( 'Edit', 'digent-appointments' ),
			esc_attr__( 'Delete', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No schedules match your current filter criteria.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return [
			'status'      => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			'location_id' => isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0,
			'service_id'  => isset( $_GET['service_id'] ) ? absint( $_GET['service_id'] ) : 0,
			'staff_id'    => isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0,
			's'           => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
	}

	public function prepare_items() {
		$filters   = $this->get_active_filters();
		$all_items = DGAP_Schedules_Repo::get_filtered( $filters );

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
