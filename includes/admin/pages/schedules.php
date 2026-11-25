<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-schedule-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-schedules-table.php';

global $wpdb;
$onboarding_status   = dgap_get_onboarding_status();
$is_unlocked         = ! empty( $onboarding_status['steps']['schedules']['unlocked'] );
$total_schedules     = DGAP_Schedules_Repo::count_all();
$dgap_table          = new DGAP_Schedules_Table();
$filters             = $dgap_table->get_active_filters();
$has_filters         = ( $filters['status'] !== '' ) || ! empty( $filters['location_id'] ) || ! empty( $filters['service_id'] ) || ! empty( $filters['staff_id'] ) || ! empty( $filters['s'] );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_filter_submitted = isset( $_GET['s'] ) || isset( $_GET['status'] ) || isset( $_GET['location_id'] ) || isset( $_GET['service_id'] ) || isset( $_GET['staff_id'] );
$is_expanded         = $has_filters || $is_filter_submitted;

// Fetch filter dropdown options when schedules exist
$filter_locations = [];
$filter_services  = [];
$filter_staff     = [];
if ( $total_schedules > 0 || $is_expanded ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$filter_locations = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}dgap_locations ORDER BY name ASC", ARRAY_A );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$filter_services  = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}dgap_services ORDER BY name ASC", ARRAY_A );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$filter_staff     = $wpdb->get_results( "SELECT id, first_name, last_name FROM {$wpdb->prefix}dgap_staff ORDER BY first_name ASC", ARRAY_A );
}
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Schedules', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all schedules per location', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<?php if ( $is_unlocked ) : ?>
				<button class="dgap-btn button button-primary dgap-add" data-entity="schedule" data-title="<?php esc_attr_e( 'Add Schedule', 'digent-appointments' ); ?>">
					<?php esc_html_e( 'Add Schedule', 'digent-appointments' ); ?>
				</button>
			<?php else : ?>
				<button class="dgap-btn button button-secondary" disabled="disabled" title="<?php esc_attr_e( 'Please create at least 1 Location, Service, and Staff member first', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-lock" style="font-size:14px;line-height:28px;vertical-align:middle;margin-right:4px;"></span>
					<?php esc_html_e( 'Add Schedule', 'digent-appointments' ); ?>
				</button>
			<?php endif; ?>
			<?php dgap_render_upgrade_button(); ?>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php if ( $total_schedules > 0 || $is_expanded ) : ?>
		<div class="dgap-filter-wrapper <?php echo ! $is_expanded ? 'is-collapsed' : ''; ?>" id="dgap-filter-wrapper">

			<!-- Mini Bar (Default collapsed state) -->
			<div class="dgap-onboarding-minibar dgap-filter-minibar" id="dgap-filter-minibar" <?php echo $is_expanded ? 'style="display:none;"' : ''; ?>>
				<div class="dgap-minibar-left">
					<span class="dashicons dashicons-filter"></span>
					<strong><?php esc_html_e( 'Filter Schedules', 'digent-appointments' ); ?></strong>
					<span class="dgap-minibar-progress"><?php esc_html_e( 'Filter by location, service, staff, or status', 'digent-appointments' ); ?></span>
				</div>
				<div class="dgap-minibar-right">
					<button type="button" class="dgap-btn button button-secondary dgap-toggle-filters" data-action="expand">
						<span><?php esc_html_e( 'Show Filters', 'digent-appointments' ); ?></span>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</button>
				</div>
			</div>

			<!-- Full Filter Card -->
			<div class="dgap-filter-card" id="dgap-filter-card" <?php echo ! $is_expanded ? 'style="display:none;"' : ''; ?>>
				<div class="dgap-filter-card-header">
					<div class="dgap-filter-card-title">
						<span class="dashicons dashicons-filter"></span>
						<strong><?php esc_html_e( 'Filter Schedules', 'digent-appointments' ); ?></strong>
					</div>
					<button type="button" class="dgap-onboarding-dismiss-btn dgap-toggle-filters" data-action="collapse" title="<?php esc_attr_e( 'Hide Filters', 'digent-appointments' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
				</div>

				<form method="get" class="dgap-filter-bar">
					<input type="hidden" name="page" value="digent-appointments-schedules" />

					<div class="dgap-filter-fields">
						<div class="dgap-filter-group dgap-filter-search">
							<span class="dashicons dashicons-search"></span>
							<input
								type="search"
								name="s"
								value="<?php echo esc_attr( $filters['s'] ); ?>"
								placeholder="<?php esc_attr_e( 'Search location, service, or staff...', 'digent-appointments' ); ?>"
							/>
						</div>

						<div class="dgap-filter-group">
							<select name="status">
								<option value=""><?php esc_html_e( 'All Statuses', 'digent-appointments' ); ?></option>
								<option value="1" <?php selected( $filters['status'], '1' ); ?>><?php esc_html_e( 'Active', 'digent-appointments' ); ?></option>
								<option value="0" <?php selected( $filters['status'], '0' ); ?>><?php esc_html_e( 'Inactive', 'digent-appointments' ); ?></option>
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
							<select name="staff_id" class="dgap-filter-select2" data-placeholder="<?php esc_attr_e( 'All Staff', 'digent-appointments' ); ?>">
								<option value=""><?php esc_html_e( 'All Staff', 'digent-appointments' ); ?></option>
								<?php foreach ( $filter_staff as $st ) : ?>
									<option value="<?php echo esc_attr( $st['id'] ); ?>" <?php selected( $filters['staff_id'], (int) $st['id'] ); ?>>
										<?php echo esc_html( trim( $st['first_name'] . ' ' . $st['last_name'] ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="dgap-filter-actions">
						<button type="submit" class="button button-primary dgap-filter-submit">
							<span class="dashicons dashicons-filter"></span>
							<span><?php esc_html_e( 'Filter', 'digent-appointments' ); ?></span>
						</button>
						<?php if ( $is_expanded ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-schedules' ) ); ?>" class="button button-secondary dgap-filter-reset">
								<span class="dashicons dashicons-image-rotate"></span>
								<span><?php esc_html_e( 'Reset', 'digent-appointments' ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>

		<!-- Contextual Bulk Action Bar -->
		<div
			class="dgap-bulk-bar"
			id="dgap-bulk-bar"
			data-bulk-action="dgap_bulk_action_schedules"
			data-entity-label="<?php esc_attr_e( 'schedule', 'digent-appointments' ); ?>"
			style="display:none;"
		>
			<div class="dgap-bulk-left">
				<span class="dgap-bulk-badge">
					<strong id="dgap-bulk-count">0</strong>
					<span id="dgap-bulk-label"><?php esc_html_e( 'schedules selected', 'digent-appointments' ); ?></span>
				</span>
				<button type="button" class="button-link dgap-bulk-clear" id="dgap-bulk-clear">
					<?php esc_html_e( 'Clear selection', 'digent-appointments' ); ?>
				</button>
			</div>

			<div class="dgap-bulk-right">
				<div class="dgap-bulk-status-group">
					<select id="dgap-bulk-status" class="dgap-bulk-select">
						<option value=""><?php esc_html_e( 'Change Status to...', 'digent-appointments' ); ?></option>
						<option value="active"><?php esc_html_e( 'Active', 'digent-appointments' ); ?></option>
						<option value="inactive"><?php esc_html_e( 'Inactive', 'digent-appointments' ); ?></option>
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
	<?php elseif ( ! $is_unlocked ) : ?>
		<div class="dgap-empty-state-card dgap-guard-card">
			<div class="dgap-empty-state-icon is-warning">
				<span class="dashicons dashicons-lock"></span>
			</div>
			<h3 class="dgap-empty-state-title"><?php esc_html_e( 'Prerequisites Required Before Creating Schedules', 'digent-appointments' ); ?></h3>
			<p class="dgap-empty-state-desc">
				<?php esc_html_e( 'Schedules connect your Staff members to specific Services and Locations. To create a working schedule, please complete the following steps first:', 'digent-appointments' ); ?>
			</p>
			<div class="dgap-guard-checklist">
				<div class="dgap-guard-item <?php echo $onboarding_status['counts']['locations'] > 0 ? 'is-complete' : 'is-pending'; ?>">
					<span class="dgap-guard-status-icon dashicons <?php echo $onboarding_status['counts']['locations'] > 0 ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
					<div class="dgap-guard-info">
						<strong><?php esc_html_e( '1. Business Location', 'digent-appointments' ); ?></strong>
						<span><?php echo $onboarding_status['counts']['locations'] > 0 ? sprintf( esc_html__( '%d configured', 'digent-appointments' ), $onboarding_status['counts']['locations'] ) : esc_html__( '0 configured', 'digent-appointments' ); ?></span>
					</div>
					<?php if ( $onboarding_status['counts']['locations'] === 0 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-locations' ) ); ?>" class="dgap-btn-pill">
							+ <?php esc_html_e( 'Add Location', 'digent-appointments' ); ?>
						</a>
					<?php else : ?>
						<span class="dgap-badge dgap-badge-success"><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="dgap-guard-item <?php echo $onboarding_status['counts']['services'] > 0 ? 'is-complete' : 'is-pending'; ?>">
					<span class="dgap-guard-status-icon dashicons <?php echo $onboarding_status['counts']['services'] > 0 ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
					<div class="dgap-guard-info">
						<strong><?php esc_html_e( '2. Service', 'digent-appointments' ); ?></strong>
						<span><?php echo $onboarding_status['counts']['services'] > 0 ? sprintf( esc_html__( '%d configured', 'digent-appointments' ), $onboarding_status['counts']['services'] ) : esc_html__( '0 configured', 'digent-appointments' ); ?></span>
					</div>
					<?php if ( $onboarding_status['counts']['services'] === 0 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-services' ) ); ?>" class="dgap-btn-pill">
							+ <?php esc_html_e( 'Add Service', 'digent-appointments' ); ?>
						</a>
					<?php else : ?>
						<span class="dgap-badge dgap-badge-success"><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="dgap-guard-item <?php echo $onboarding_status['counts']['staff'] > 0 ? 'is-complete' : 'is-pending'; ?>">
					<span class="dgap-guard-status-icon dashicons <?php echo $onboarding_status['counts']['staff'] > 0 ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
					<div class="dgap-guard-info">
						<strong><?php esc_html_e( '3. Staff Member', 'digent-appointments' ); ?></strong>
						<span><?php echo $onboarding_status['counts']['staff'] > 0 ? sprintf( esc_html__( '%d configured', 'digent-appointments' ), $onboarding_status['counts']['staff'] ) : esc_html__( '0 configured', 'digent-appointments' ); ?></span>
					</div>
					<?php if ( $onboarding_status['counts']['staff'] === 0 ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-staff' ) ); ?>" class="dgap-btn-pill">
							+ <?php esc_html_e( 'Add Staff', 'digent-appointments' ); ?>
						</a>
					<?php else : ?>
						<span class="dgap-badge dgap-badge-success"><?php esc_html_e( 'Completed', 'digent-appointments' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php else : ?>
		<?php
		dgap_render_empty_state( [
			'icon'         => 'dashicons-clock',
			'title'        => __( 'No Schedules Configured Yet', 'digent-appointments' ),
			'desc'         => __( 'Configure working hours, shifts, and business availability across locations and team members.', 'digent-appointments' ),
			'button_text'  => __( 'Add Schedule', 'digent-appointments' ),
			'button_title' => __( 'Add Schedule', 'digent-appointments' ),
			'entity'       => 'schedule',
		] );
		?>
	<?php endif; ?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/schedule-slide-panel.php';
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/confirm-modal.php';
