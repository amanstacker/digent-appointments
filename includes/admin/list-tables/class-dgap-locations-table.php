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
			esc_attr( $item['id'] )
		);
	}

	public function column_name( $item ) {
		return sprintf(
			'<a href="#" class="dgap-edit dgap-row-edit-link" data-entity="location" data-title="%2$s" data-id="%1$d"><strong class="dgap-location-title">%3$s</strong></a>',
			(int) $item['id'],
			esc_attr__( 'Edit Location', 'digent-appointments' ),
			esc_html( $item['name'] )
		);
	}

	public function column_address( $item ) {
		return '<span class="dgap-muted">' . esc_html( $item['address'] ?: '—' ) . '</span>';
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<div class="dgap-table-actions">
				<button type="button" data-title="%2$s" data-entity="location" class="button button-small dgap-edit dgap-action-btn" data-id="%1$d" title="%3$s" aria-label="%3$s">
					<span class="dashicons dashicons-edit"></span>
				</button>
				<button type="button" data-entity="location" class="button button-small button-secondary dgap-delete dgap-action-btn is-delete" data-id="%1$d" title="%4$s" aria-label="%4$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_attr( $item['id'] ),
			esc_attr__( 'Edit Location', 'digent-appointments' ),
			esc_attr__( 'Edit', 'digent-appointments' ),
			esc_attr__( 'Delete', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No locations match your current filter criteria.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
	}

	public function prepare_items() {
		$filters   = $this->get_active_filters();
		$all_items = DGAP_Location_Repo::get_filtered( $filters );

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
