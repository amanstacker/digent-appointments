<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Forms_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'        => '<input type="checkbox" />',
			'name'      => esc_html__( 'Form Name', 'digent-appointments' ),
			'layout'    => esc_html__( 'Layout', 'digent-appointments' ),
			'shortcode' => esc_html__( 'Shortcode', 'digent-appointments' ),
			'status'    => esc_html__( 'Status', 'digent-appointments' ),
			'actions'   => esc_html__( 'Actions', 'digent-appointments' ),
		];
	}

	protected function get_primary_column_name() {
		return 'name';
	}

	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ids[]" value="%d" />',
			(int) $item['id']
		);
	}

	public function column_name( $item ) {
		return sprintf(
			'<strong class="dgap-location-title">%s</strong>',
			esc_html( $item['name'] )
		);
	}

	public function column_layout( $item ) {
		return esc_html( ucfirst( str_replace( '-', ' ', $item['layout'] ) ) );
	}

	public function column_shortcode( $item ) {
		return sprintf(
			'<code>[dgap_booking_form id="%d"]</code>',
			(int) $item['id']
		);
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		$edit_url = admin_url( 'admin.php?page=digent-appointments-forms&action=edit&id=' . (int) $item['id'] );

		return sprintf(
			'<div class="dgap-row-actions-wrap">
				<a
					href="%1$s"
					class="button button-small dgap-action-btn"
					title="%2$s"
					aria-label="%2$s">
					<span class="dashicons dashicons-edit"></span>
				</a>
				<button
					type="button"
					class="button button-small button-secondary dgap-action-btn dgap-action-btn-danger dgap-delete"
					data-entity="form"
					data-id="%3$d"
					data-name="%4$s"
					title="%5$s"
					aria-label="%5$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_url( $edit_url ),
			esc_attr__( 'Edit Form', 'digent-appointments' ),
			(int) $item['id'],
			esc_attr( $item['name'] ),
			esc_attr__( 'Delete', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No booking forms found matching your filters.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public function prepare_items() {
		$filters  = $this->get_active_filters();
		$per_page = 20;
		$paged    = $this->get_pagenum();
		$offset   = ( $paged - 1 ) * $per_page;

		$query_args = array_merge(
			$filters,
			[
				'limit'  => $per_page,
				'offset' => $offset,
			]
		);

		$total_items = DGAP_Form_Repo::get_filtered( array_merge( $filters, [ 'count' => true ] ) );
		$this->items = DGAP_Form_Repo::get_filtered( $query_args );

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
			$this->get_primary_column_name(),
		];

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}
}
