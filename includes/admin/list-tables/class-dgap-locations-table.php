<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Locations_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'      => '<input type="checkbox" />',
			'name'    => esc_html__( 'Location', 'digent-appointments' ),
			'address' => esc_html__( 'Address', 'digent-appointments' ),
			'status'  => esc_html__( 'Status', 'digent-appointments' ),
			'actions' => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			$item['id']
		);
	}

	public function column_name( $item ) {
		return sprintf(
			'<strong class="dgap-location-title">%s</strong>',
			esc_html( $item['name'] )
		);
	}

	public function column_address( $item ) {
		return '<span class="dgap-muted">' . esc_html( $item['address'] ) . '</span>';
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="Edit Location" data-entity="location" class="button button-small dgap-edit" data-id="%1$d">%2$s</button>
			 <button data-entity="location" class="button button-small button-secondary dgap-delete" data-id="%1$d">%3$s</button>',
			$item['id'],
			esc_html__( 'Edit', 'digent-appointments' ),
			esc_html__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Location_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
