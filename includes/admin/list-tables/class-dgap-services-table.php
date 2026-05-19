<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Services_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'name'     => __( 'Service', 'digent-appointments' ),
			'duration' => __( 'Duration', 'digent-appointments' ),
			'price'    => __( 'Price', 'digent-appointments' ),
			'status'   => __( 'Status', 'digent-appointments' ),
			'actions'  => __( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" value="%d" />',
			$item['id']
		);
	}

	public function column_name( $item ) {
		return '<strong>' . esc_html( $item['name'] ) . '</strong>';
	}

	public function column_duration( $item ) {
		return esc_html( $item['duration'] ) . ' min';
	}

	public function column_price( $item ) {
		return  $item['price'];
	}

	public function column_status( $item ) {
		return (int) $item['status'] === 1
			? '<span class="dgap-badge dgap-badge-active">Active</span>'
			: '<span class="dgap-badge dgap-badge-inactive">Inactive</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="Edit Service" data-entity="service" class="button button-small dgap-edit" data-id="%1$d">Edit</button>
			 <button data-entity="service" class="button button-small dgap-delete" data-id="%1$d">Delete</button>',
			$item['id']
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Service_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
