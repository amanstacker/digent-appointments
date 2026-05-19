<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Timeoff_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'     => '<input type="checkbox" />',
			'name'   => __( 'Time Off', 'digent-appointments' ),
			'type'   => __( 'Applied On', 'digent-appointments' ),
			'dates'  => __( 'Dates', 'digent-appointments' ),
			'status' => __( 'Status', 'digent-appointments' ),
			'actions'=> __( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			(int) $item['id']
		);
	}

	public function column_name( $item ) {
		return sprintf(
			'<strong class="dgap-timeoff-title">%s</strong>',
			esc_html( $item['name'] )
		);
	}

	public function column_type( $item ) {
		return ucfirst( esc_html( $item['type'] ) );
	}

	public function column_dates( $item ) {

		$dates = [];

		if ( is_array( $item['dates'] ) ) {
			$dates = $item['dates'];
		} elseif ( ! empty( $item['dates'] ) ) {
			$decoded = json_decode( $item['dates'], true );
			$dates   = is_array( $decoded ) ? $decoded : [];
		}

		$count = count( $dates );

		return sprintf(
			'<span class="dgap-muted">%d %s</span>',
			$count,
			$count === 1 ? __( 'day', 'digent-appointments' ) : __( 'days', 'digent-appointments' )
		);
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . __( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . __( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button
				class="button button-small dgap-edit"
				data-entity="timeoff"
				data-title="%2$s"
				data-id="%1$d">
				%3$s
			</button>
			<button
				class="button button-small button-secondary dgap-delete"
				data-entity="timeoff"
				data-id="%1$d">
				%4$s
			</button>',
			(int) $item['id'],
			esc_attr__( 'Edit Time Off', 'digent-appointments' ),
			__( 'Edit', 'digent-appointments' ),
			__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Timeoff_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
