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
			'location'     => __( 'Location', 'digent-appointments' ),
			'staff'        => __( 'Staff', 'digent-appointments' ),
			'service'      => __( 'Service', 'digent-appointments' ),
			'availability' => __( 'Availability', 'digent-appointments' ),
			'capacity'     => __( 'Capacity', 'digent-appointments' ),
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

	public function column_location( $item ) {
		return esc_html( $item['location_name'] ?? '-' );
	}

	public function column_staff( $item ) {
		$staff = DGAP_Staff_Repo::get( $item['staff_id'] );
		return esc_html( $staff['name'] ?? '-' );
	}

	public function column_service( $item ) {
		$service = DGAP_Service_Repo::get( $item['service_id'] );
		return esc_html( $service['name'] ?? '-' );
	}

	/**
	 * Availability column (new)
	 */
	public function column_availability( $item ) {

		if ( empty( $item['availability'] ) ) {
			return '-';
		}

		$data = json_decode( $item['availability'], true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return '-';
		}

		$labels = [
			'monday'    => __( 'Mon', 'digent-appointments' ),
			'tuesday'   => __( 'Tue', 'digent-appointments' ),
			'wednesday' => __( 'Wed', 'digent-appointments' ),
			'thursday'  => __( 'Thu', 'digent-appointments' ),
			'friday'    => __( 'Fri', 'digent-appointments' ),
			'saturday'  => __( 'Sat', 'digent-appointments' ),
			'sunday'    => __( 'Sun', 'digent-appointments' ),
		];

		$output = [];

		foreach ( $data as $day => $info ) {

			if ( empty( $info['status'] ) || 'open' !== $info['status'] ) {
				continue;
			}

			$time = '-';

			if ( ! empty( $info['open'] ) && ! empty( $info['close'] ) ) {
				$time = esc_html( $info['open'] . ' → ' . $info['close'] );
			}

			$output[] = sprintf(
				'<strong>%s</strong>: %s',
				$labels[ $day ] ?? ucfirst( $day ),
				$time
			);
		}

		return ! empty( $output )
			? implode( '<br>', $output )
			: '-';
	}

	public function column_capacity( $item ) {
		return esc_html( $item['capacity'] ?: '-' );
	}

	public function column_status( $item ) {
		return ( (int) $item['status'] === 1 )
			? '<span class="dgap-badge dgap-badge-active">' . __( 'Active', 'digent-appointments' ) . '</span>'
			: '<span class="dgap-badge dgap-badge-inactive">' . __( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="%4$s" data-entity="schedule" class="button button-small dgap-edit" data-id="%1$d">%2$s</button>
			 <button data-entity="schedule" class="button button-small button-secondary dgap-delete" data-id="%1$d">%3$s</button>',
			$item['id'],
			__( 'Edit', 'digent-appointments' ),
			__( 'Delete', 'digent-appointments' ),
			esc_attr__( 'Edit Schedule', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Schedules_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
