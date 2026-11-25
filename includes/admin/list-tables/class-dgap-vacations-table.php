<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DGAP_Vacations_Table extends WP_List_Table {

	public function get_columns() {
		return [
			'cb'      => '<input type="checkbox" />',
			'name'    => esc_html__( 'Vacation Name', 'digent-appointments' ),
			'type'    => esc_html__( 'Applied On', 'digent-appointments' ),
			'dates'   => esc_html__( 'Dates', 'digent-appointments' ),
			'status'  => esc_html__( 'Status', 'digent-appointments' ),
			'actions' => esc_html__( 'Actions', 'digent-appointments' ),
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

	public function column_type( $item ) {
		$type       = $item['type'] ?? '';
		$entity_ids = json_decode( $item['entity_ids'] ?? '[]', true );
		$count      = is_array( $entity_ids ) ? count( $entity_ids ) : 0;
		$label      = ( 'service' === $type )
			? esc_html__( 'Service', 'digent-appointments' )
			: esc_html__( 'Staff', 'digent-appointments' );

		return sprintf(
			'<span>%s</span> <span class="dgap-muted">(%d)</span>',
			$label,
			$count
		);
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
		$first = ! empty( $dates[0]['date'] ) ? $dates[0]['date'] : '';

		$summary = sprintf(
			'%d %s',
			$count,
			$count === 1 ? esc_html__( 'day', 'digent-appointments' ) : esc_html__( 'days', 'digent-appointments' )
		);

		if ( $first ) {
			$summary .= ' · ' . esc_html( $first );
			if ( $count > 1 ) {
				$last = ! empty( $dates[ $count - 1 ]['date'] ) ? $dates[ $count - 1 ]['date'] : '';
				if ( $last && $last !== $first ) {
					$summary .= ' → ' . esc_html( $last );
				}
			}
		}

		return sprintf( '<span class="dgap-muted">%s</span>', $summary );
	}

	public function column_status( $item ) {
		if ( (int) $item['status'] === 1 ) {
			return '<span class="dgap-badge dgap-badge-active">' . esc_html__( 'Active', 'digent-appointments' ) . '</span>';
		}

		return '<span class="dgap-badge dgap-badge-inactive">' . esc_html__( 'Inactive', 'digent-appointments' ) . '</span>';
	}

	public function column_actions( $item ) {
		return sprintf(
			'<div class="dgap-row-actions-wrap">
				<button
					type="button"
					class="button button-small dgap-action-btn dgap-edit"
					data-entity="vacation"
					data-title="%2$s"
					data-id="%1$d"
					title="%2$s"
					aria-label="%2$s">
					<span class="dashicons dashicons-edit"></span>
				</button>
				<button
					type="button"
					class="button button-small button-secondary dgap-action-btn dgap-action-btn-danger dgap-delete"
					data-entity="vacation"
					data-id="%1$d"
					data-name="%3$s"
					title="%4$s"
					aria-label="%4$s">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</div>',
			esc_attr( $item['id'] ),
			esc_attr__( 'Edit Vacation', 'digent-appointments' ),
			esc_attr( $item['name'] ),
			esc_attr__( 'Delete', 'digent-appointments' )
		);
	}

	public function no_items() {
		esc_html_e( 'No vacations found matching your filters.', 'digent-appointments' );
	}

	public function get_active_filters() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			'type'   => isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public function prepare_items() {
		$filters  = $this->get_active_filters();
		$per_page = 20;
		$paged    = $this->get_pagenum();
		$offset   = ( $paged - 1 ) * $per_page;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$query_args = array_merge(
			$filters,
			[
				'orderby' => $orderby,
				'order'   => $order,
				'limit'   => $per_page,
				'offset'  => $offset,
			]
		);

		$total_items = DGAP_Vacation_Repo::get_filtered( array_merge( $filters, [ 'count' => true ] ) );
		$this->items = DGAP_Vacation_Repo::get_filtered( $query_args );

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
