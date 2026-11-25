<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-staff-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-staff-table.php';

$total_staff         = DGAP_Staff_Repo::count_all();
$dgap_table          = new DGAP_Staff_Table();
$filters             = $dgap_table->get_active_filters();
$has_filters         = ( $filters['status'] !== '' ) || ! empty( $filters['s'] );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_filter_submitted = isset( $_GET['s'] ) || isset( $_GET['status'] );
$is_expanded         = $has_filters || $is_filter_submitted;
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Staff Members', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage your team members and availability', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="staff" data-title="<?php esc_attr_e( 'Add Staff Member', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Staff', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php if ( $total_staff > 0 || $is_expanded ) : ?>
		<div class="dgap-filter-wrapper <?php echo ! $is_expanded ? 'is-collapsed' : ''; ?>" id="dgap-filter-wrapper">

			<!-- Mini Bar (Default collapsed state) -->
			<div class="dgap-onboarding-minibar dgap-filter-minibar" id="dgap-filter-minibar" <?php echo $is_expanded ? 'style="display:none;"' : ''; ?>>
				<div class="dgap-minibar-left">
					<span class="dashicons dashicons-filter"></span>
					<strong><?php esc_html_e( 'Filter Staff', 'digent-appointments' ); ?></strong>
					<span class="dgap-minibar-progress"><?php esc_html_e( 'Search by name, email, phone, or status', 'digent-appointments' ); ?></span>
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
						<strong><?php esc_html_e( 'Filter Staff', 'digent-appointments' ); ?></strong>
					</div>
					<button type="button" class="dgap-onboarding-dismiss-btn dgap-toggle-filters" data-action="collapse" title="<?php esc_attr_e( 'Hide Filters', 'digent-appointments' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
				</div>

				<form method="get" class="dgap-filter-bar">
					<input type="hidden" name="page" value="digent-appointments-staff" />

					<div class="dgap-filter-fields">
						<div class="dgap-filter-group dgap-filter-search">
							<span class="dashicons dashicons-search"></span>
							<input
								type="search"
								name="s"
								value="<?php echo esc_attr( $filters['s'] ); ?>"
								placeholder="<?php esc_attr_e( 'Search staff name, email, or phone...', 'digent-appointments' ); ?>"
							/>
						</div>

						<div class="dgap-filter-group">
							<select name="status">
								<option value=""><?php esc_html_e( 'All Statuses', 'digent-appointments' ); ?></option>
								<option value="1" <?php selected( $filters['status'], '1' ); ?>><?php esc_html_e( 'Active', 'digent-appointments' ); ?></option>
								<option value="0" <?php selected( $filters['status'], '0' ); ?>><?php esc_html_e( 'Inactive', 'digent-appointments' ); ?></option>
							</select>
						</div>
					</div>

					<div class="dgap-filter-actions">
						<button type="submit" class="button button-primary dgap-filter-submit">
							<span class="dashicons dashicons-filter"></span>
							<span><?php esc_html_e( 'Filter', 'digent-appointments' ); ?></span>
						</button>
						<?php if ( $is_expanded ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-staff' ) ); ?>" class="button button-secondary dgap-filter-reset">
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
			data-bulk-action="dgap_bulk_action_staff"
			data-entity-label="<?php esc_attr_e( 'staff member', 'digent-appointments' ); ?>"
			style="display:none;"
		>
			<div class="dgap-bulk-left">
				<span class="dgap-bulk-badge">
					<strong id="dgap-bulk-count">0</strong>
					<span id="dgap-bulk-label"><?php esc_html_e( 'staff members selected', 'digent-appointments' ); ?></span>
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
	<?php else : ?>
		<?php
		dgap_render_empty_state( [
			'icon'         => 'dashicons-groups',
			'title'        => __( 'No Staff Members Added Yet', 'digent-appointments' ),
			'desc'         => __( 'Add team members, doctors, or specialists who deliver services and manage their individual availability.', 'digent-appointments' ),
			'button_text'  => __( 'Add Staff', 'digent-appointments' ),
			'button_title' => __( 'Add Staff Member', 'digent-appointments' ),
			'entity'       => 'staff',
		] );
		?>
	<?php endif; ?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/staff-slide-panel.php';
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/confirm-modal.php';
