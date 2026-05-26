<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Staff_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'      => '<input type="checkbox" />',
			'name'    => esc_html__( 'Staff', 'digent-appointments' ),
			'email'   => esc_html__( 'Email', 'digent-appointments' ),
			'phone'   => esc_html__( 'Phone', 'digent-appointments' ),
			'status'  => esc_html__( 'Status', 'digent-appointments' ),
			'actions' => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			esc_attr( $item['id'] )
		);
	}

	public function column_name( $item ) {

		$name = trim(
			( $item['first_name'] ?? '' ) . ' ' . ( $item['last_name'] ?? '' )
		);

		return sprintf(
			'<strong class="dgap-staff-title">%s</strong>',
			esc_html( $name ?: '—' )
		);
	}

	public function column_email( $item ) {
		return sprintf(
			'<a href="mailto:%1$s">%1$s</a>',
			esc_html( $item['email'] )
		);
	}

	public function column_phone( $item ) {

	$dial  = ! empty( $item['phone_dial_code'] ) ? $item['phone_dial_code'] : '';
	$phone = ! empty( $item['phone'] ) ? $item['phone'] : '';

	if ( empty( $phone ) ) {
		return '—';
	}

	return sprintf(
		'<span class="dgap-muted">%1$s&nbsp;%2$s</span>',
		esc_html( $dial ),
		esc_html( $phone )
	);
}


	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="' . esc_attr__( 'Edit Staff', 'digent-appointments' ) . '" data-entity="staff" class="button button-small dgap-edit" data-id="%1$d">' . esc_html__( 'Edit', 'digent-appointments' ) . '</button>
			 <button data-entity="staff" class="button button-small button-secondary dgap-delete" data-id="%1$d">' . esc_html__( 'Delete', 'digent-appointments' ) . '</button>',
			esc_attr( $item['id'] ),
			esc_html__( 'Edit', 'digent-appointments' ),
			esc_html__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Staff_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
