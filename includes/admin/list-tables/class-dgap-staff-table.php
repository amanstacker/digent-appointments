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
			'name'    => __( 'Staff', 'digent-appointments' ),
			'email'   => __( 'Email', 'digent-appointments' ),
			'phone'   => __( 'Phone', 'digent-appointments' ),
			'status'  => __( 'Status', 'digent-appointments' ),
			'actions' => __( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			$item['id']
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
			return '<span class="dgap-badge dgap-badge-active">' . __( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . __( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button data-title="Edit Staff" data-entity="staff" class="button button-small dgap-edit" data-id="%1$d">%2$s</button>
			 <button data-entity="staff" class="button button-small button-secondary dgap-delete" data-id="%1$d">%3$s</button>',
			$item['id'],
			__( 'Edit', 'digent-appointments' ),
			__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Staff_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
