<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Services_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'name'     => esc_html__( 'Service', 'digent-appointments' ),
			'duration' => esc_html__( 'Duration', 'digent-appointments' ),
			'price'    => esc_html__( 'Price', 'digent-appointments' ),
			'status'   => esc_html__( 'Status', 'digent-appointments' ),
			'actions'  => esc_html__( 'Actions', 'digent-appointments' ),
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
		return  esc_html( $item['price'] );
	}

	public function column_status( $item ) {
		return (int) $item['status'] === 1
			? '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>'
			: '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="' . esc_attr__( 'Edit Service', 'digent-appointments' ) . '" data-entity="service" class="button button-small dgap-edit" data-id="%1$d">' . esc_html__( 'Edit', 'digent-appointments' ) . '</button>
			 <button data-entity="service" class="button button-small dgap-delete" data-id="%1$d">' . esc_html__( 'Delete', 'digent-appointments' ) . '</button>',
			$item['id']
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Service_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
