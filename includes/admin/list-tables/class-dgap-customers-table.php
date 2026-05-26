<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Customers_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'     => '<input type="checkbox" />',
			'name'   => esc_html__( 'Customer Name', 'digent-appointments' ),
			'email'  => esc_html__( 'Email', 'digent-appointments' ),
			'phone'  => esc_html__( 'Phone', 'digent-appointments' ),
			'status' => esc_html__( 'Status', 'digent-appointments' ),
			'actions'=> esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			esc_attr( $item['id'] )
		);
	}

	public function column_name( $item ) {
		// Display first + last name
		$full_name = trim( $item['first_name'] . ' ' . $item['last_name'] );
		return sprintf(
			'<strong class="dgap-customer-title">%s</strong>',
			esc_html( $full_name )
		);
	}

	public function column_email( $item ) {
		return esc_html( $item['email'] );
	}

	public function column_phone( $item ) {
		// Include dial code if available
		$phone_display = '';
		if ( ! empty( $item['phone_dial_code'] ) ) {
			$phone_display .= $item['phone_dial_code'] . ' ';
		}
		$phone_display .= $item['phone'] ?: '-';

		return esc_html( $phone_display );
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<button
				data-title="%4$s"
				data-entity="customer"
				class="button button-small dgap-edit"
				data-id="%1$d">%2$s</button>
			 <button
				data-entity="customer"
				class="button button-small button-secondary dgap-delete"
				data-id="%1$d">%3$s</button>',
			esc_attr( $item['id'] ),
			esc_html__( 'Edit', 'digent-appointments' ),
			esc_html__( 'Delete', 'digent-appointments' ),
			esc_attr__( 'Edit Customer', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Customer_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}
