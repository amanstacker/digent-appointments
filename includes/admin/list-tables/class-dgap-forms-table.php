<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Forms_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'        => '<input type="checkbox" />',
			'name'      => __( 'Form Name', 'digent-appointments' ),
			'layout'    => __( 'Layout', 'digent-appointments' ),
			'shortcode' => __( 'Shortcode', 'digent-appointments' ),
			'status'    => __( 'Status', 'digent-appointments' ),
			'actions'   => __( 'Actions', 'digent-appointments' ),
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
			'<strong>%s</strong>',
			esc_html( $item['name'] )
		);
	}

	public function column_layout( $item ) {
		return esc_html( ucfirst( str_replace('-', ' ', $item['layout'] ) ) );
	}

	public function column_shortcode( $item ) {
		return sprintf(
			'<code>[dgap_booking_form id="%d"]</code>',
			$item['id']
		);
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">Active</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">Inactive</span>';
	}

	public function column_actions( $item ) {
		$edit_url = admin_url( 'admin.php?page=digent-forms&action=edit&id=' . $item['id'] );

		return sprintf(
			'<a href="%1$s" class="button button-small">%2$s</a>
			 <button class="button button-small button-secondary dgap-delete" data-entity="form" data-id="%3$d">%4$s</button>',
			esc_url( $edit_url ),
			__( 'Edit', 'digent-appointments' ),
			$item['id'],
			__( 'Delete', 'digent-appointments' )
		);
	}

	public function prepare_items() {
		$this->items = DGAP_Form_Repo::get_all();
		$this->_column_headers = [ $this->get_columns(), [], [] ];
	}
}