<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-bookings-table.php';

global $wpdb;
$total_bookings = DGAP_Booking_Repo::count_all();
$dgap_table          = new DGAP_Bookings_Table();
$filters             = $dgap_table->get_active_filters();
$has_filters         = ! empty( $filters['status'] ) || ! empty( $filters['location_id'] ) || ! empty( $filters['service_id'] ) || ! empty( $filters['worker_id'] ) || ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) || ! empty( $filters['s'] );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_filter_submitted = isset( $_GET['s'] ) || isset( $_GET['status'] ) || isset( $_GET['location_id'] ) || isset( $_GET['service_id'] ) || isset( $_GET['worker_id'] ) || isset( $_GET['date_from'] ) || isset( $_GET['date_to'] );
$is_expanded         = $has_filters || $is_filter_submitted;

// Fetch filter dropdown options
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$filter_locations = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}dgap_locations ORDER BY name ASC", ARRAY_A );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$filter_services  = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}dgap_services ORDER BY name ASC", ARRAY_A );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$filter_worker     = $wpdb->get_results( "SELECT id, first_name, last_name FROM {$wpdb->prefix}dgap_workers ORDER BY first_name ASC", ARRAY_A );
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Bookings', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage, filter, and update all customer bookings', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<?php if ( $total_bookings > 0 ) :
				$export_args = array_merge( [ 'action' => 'dgap_export_bookings' ], array_filter( $filters ) );
				$export_url  = wp_nonce_url( add_query_arg( $export_args, admin_url( 'admin-post.php' ) ), 'dgap_export_bookings', '_dgap_nonce' );
			?>
				<a
					href="<?php echo esc_url( $export_url ); ?>"
					class="dgap-btn button button-secondary dgap-export-btn"
					title="<?php echo $has_filters ? esc_attr__( 'Export filtered bookings as CSV', 'digent-appointments' ) : esc_attr__( 'Export all bookings as CSV', 'digent-appointments' ); ?>"
				>
					<span class="dashicons dashicons-download"></span>
					<span><?php echo $has_filters ? esc_html__( 'Export Filtered', 'digent-appointments' ) : esc_html__( 'Export CSV', 'digent-appointments' ); ?></span>
				</a>
			<?php endif; ?>
			<button
				class="dgap-btn button button-primary dgap-add"
				data-entity="booking"
				data-title="<?php esc_attr_e( 'Add Booking', 'digent-appointments' ); ?>"
			>
				<?php esc_html_e( 'Add Booking', 'digent-appointments' ); ?>
			</button>
			<?php dgap_render_upgrade_button(); ?>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php if ( $total_bookings > 0 || $is_expanded ) : ?>
		<div class="dgap-filter-wrapper <?php echo ! $is_expanded ? 'is-collapsed' : ''; ?>" id="dgap-filter-wrapper">

			<!-- Mini Bar (Default collapsed state) -->
			<div class="dgap-onboarding-minibar dgap-filter-minibar" id="dgap-filter-minibar" <?php echo $is_expanded ? 'style="display:none;"' : ''; ?>>
				<div class="dgap-minibar-left">
					<span class="dashicons dashicons-filter"></span>
					<strong><?php esc_html_e( 'Filter Bookings', 'digent-appointments' ); ?></strong>
					<span class="dgap-minibar-progress"><?php esc_html_e( 'Filter by customer, status, location, service, worker, or date range', 'digent-appointments' ); ?></span>
				</div>
				<div class="dgap-minibar-right">
					<button type="button" class="dgap-btn button button-secondary dgap-toggle-filters" data-action="expand">
						<span><?php esc_html_e( 'Show Filters', 'digent-appointments' ); ?></span>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</button>
				</div>
			</div>

			<!-- Full Filter Card (Hidden by default unless URL has filter params) -->
			<div class="dgap-filter-card" id="dgap-filter-card" <?php echo ! $is_expanded ? 'style="display:none;"' : ''; ?>>
				<div class="dgap-filter-card-header">
					<div class="dgap-filter-card-title">
						<span class="dashicons dashicons-filter"></span>
						<strong><?php esc_html_e( 'Filter Bookings', 'digent-appointments' ); ?></strong>
					</div>
					<button type="button" class="dgap-onboarding-dismiss-btn dgap-toggle-filters" data-action="collapse" title="<?php esc_attr_e( 'Hide Filters', 'digent-appointments' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
				</div>

				<form method="get" class="dgap-filter-bar">
					<input type="hidden" name="page" value="digent-appointments-list-view" />

					<div class="dgap-filter-fields">
						<div class="dgap-filter-group dgap-filter-search">
							<span class="dashicons dashicons-search"></span>
							<input
								type="search"
								name="s"
								value="<?php echo esc_attr( $filters['s'] ); ?>"
								placeholder="<?php esc_attr_e( 'Search customer, service, or #ID...', 'digent-appointments' ); ?>"
							/>
						</div>

						<div class="dgap-filter-group">
							<select name="status">
								<option value=""><?php esc_html_e( 'All Statuses', 'digent-appointments' ); ?></option>
								<option value="pending" <?php selected( $filters['status'], 'pending' ); ?>><?php esc_html_e( 'Pending', 'digent-appointments' ); ?></option>
								<option value="confirmed" <?php selected( $filters['status'], 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'digent-appointments' ); ?></option>
								<option value="completed" <?php selected( $filters['status'], 'completed' ); ?>><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></option>
								<option value="cancelled" <?php selected( $filters['status'], 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'digent-appointments' ); ?></option>
							</select>
						</div>

						<div class="dgap-filter-group dgap-filter-select-wrap">
							<select name="location_id" class="dgap-filter-select2" data-placeholder="<?php esc_attr_e( 'All Locations', 'digent-appointments' ); ?>">
								<option value=""><?php esc_html_e( 'All Locations', 'digent-appointments' ); ?></option>
								<?php foreach ( $filter_locations as $loc ) : ?>
									<option value="<?php echo esc_attr( $loc['id'] ); ?>" <?php selected( $filters['location_id'], (int) $loc['id'] ); ?>>
										<?php echo esc_html( $loc['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="dgap-filter-group dgap-filter-select-wrap">
							<select name="service_id" class="dgap-filter-select2" data-placeholder="<?php esc_attr_e( 'All Services', 'digent-appointments' ); ?>">
								<option value=""><?php esc_html_e( 'All Services', 'digent-appointments' ); ?></option>
								<?php foreach ( $filter_services as $srv ) : ?>
									<option value="<?php echo esc_attr( $srv['id'] ); ?>" <?php selected( $filters['service_id'], (int) $srv['id'] ); ?>>
										<?php echo esc_html( $srv['name'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="dgap-filter-group dgap-filter-select-wrap">
							<select name="worker_id" class="dgap-filter-select2" data-placeholder="<?php esc_attr_e( 'All Worker', 'digent-appointments' ); ?>">
								<option value=""><?php esc_html_e( 'All Worker', 'digent-appointments' ); ?></option>
								<?php foreach ( $filter_worker as $st ) : ?>
									<option value="<?php echo esc_attr( $st['id'] ); ?>" <?php selected( $filters['worker_id'], (int) $st['id'] ); ?>>
										<?php echo esc_html( trim( $st['first_name'] . ' ' . $st['last_name'] ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="dgap-filter-group dgap-filter-daterange">
							<input
								type="date"
								name="date_from"
								value="<?php echo esc_attr( $filters['date_from'] ); ?>"
								title="<?php esc_attr_e( 'From Date', 'digent-appointments' ); ?>"
								aria-label="<?php esc_attr_e( 'From Date', 'digent-appointments' ); ?>"
							/>
							<span class="dgap-filter-dash">&ndash;</span>
							<input
								type="date"
								name="date_to"
								value="<?php echo esc_attr( $filters['date_to'] ); ?>"
								title="<?php esc_attr_e( 'To Date', 'digent-appointments' ); ?>"
								aria-label="<?php esc_attr_e( 'To Date', 'digent-appointments' ); ?>"
							/>
						</div>
					</div>

					<div class="dgap-filter-actions">
						<button type="submit" class="button button-primary dgap-filter-submit">
							<span class="dashicons dashicons-filter"></span>
							<span><?php esc_html_e( 'Filter', 'digent-appointments' ); ?></span>
						</button>
						<?php if ( $is_expanded ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-list-view' ) ); ?>" class="button button-secondary dgap-filter-reset">
								<span class="dashicons dashicons-image-rotate"></span>
								<span><?php esc_html_e( 'Reset', 'digent-appointments' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>

		<!-- Contextual Bulk Action Bar (Shown when checkboxes are selected) -->
		<div
			class="dgap-bulk-bar"
			id="dgap-bulk-bar"
			data-bulk-action="dgap_bulk_action_bookings"
			data-entity-label="<?php esc_attr_e( 'booking', 'digent-appointments' ); ?>"
			style="display:none;"
		>
			<div class="dgap-bulk-left">
				<span class="dgap-bulk-badge">
					<strong id="dgap-bulk-count">0</strong>
					<span id="dgap-bulk-label"><?php esc_html_e( 'bookings selected', 'digent-appointments' ); ?></span>
				</span>
				<button type="button" class="button-link dgap-bulk-clear" id="dgap-bulk-clear">
					<?php esc_html_e( 'Clear selection', 'digent-appointments' ); ?>
				</button>
			</div>

			<div class="dgap-bulk-right">
				<div class="dgap-bulk-status-group">
					<select id="dgap-bulk-status" class="dgap-bulk-select">
						<option value=""><?php esc_html_e( 'Change Status to...', 'digent-appointments' ); ?></option>
						<option value="confirmed"><?php esc_html_e( 'Confirmed', 'digent-appointments' ); ?></option>
						<option value="completed"><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'digent-appointments' ); ?></option>
						<option value="cancelled"><?php esc_html_e( 'Cancelled', 'digent-appointments' ); ?></option>
					</select>
					<button type="button" class="button button-primary dgap-bulk-apply" id="dgap-bulk-status-btn" disabled>
						<span class="dashicons dashicons-yes"></span>
						<span><?php esc_html_e( 'Apply', 'digent-appointments' ); ?></span>
					</button>
				</div>

				<button type="button" class="button button-secondary dgap-bulk-delete-btn" id="dgap-bulk-delete-btn">
					<span class="dashicons dashicons-trash"></span>
					<span><?php esc_html_e( 'Delete Selected', 'digent-appointments' ); ?></span>
				</button>
			</div>
		</div>

		<?php
		$dgap_table->prepare_items();
		$dgap_table->display();
		?>
	<?php else : ?>
		<?php
		dgap_render_empty_state( [
			'icon'         => 'dashicons-calendar-alt',
			'title'        => __( 'No Bookings Recorded Yet', 'digent-appointments' ),
			'desc'         => __( 'When clients book appointments online or when you add them manually, they will appear here.', 'digent-appointments' ),
			'button_text'  => __( 'Add Booking', 'digent-appointments' ),
			'button_title' => __( 'Add Booking', 'digent-appointments' ),
			'entity'       => 'booking',
		] );
		?>
	<?php endif; ?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/booking-slide-panel.php';
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/confirm-modal.php';
