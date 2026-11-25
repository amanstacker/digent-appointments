<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-schedule-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-schedules-table.php';

$onboarding_status = dgap_get_onboarding_status();
$is_unlocked       = $onboarding_status['unlocked']['schedules'];
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
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Schedules_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} elseif ( ! $is_unlocked ) {
		?>
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
		<?php
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-clock',
			'title'        => __( 'No Schedules Configured Yet', 'digent-appointments' ),
			'desc'         => __( 'Configure working hours, shifts, and business availability across locations and team members.', 'digent-appointments' ),
			'button_text'  => __( 'Add Schedule', 'digent-appointments' ),
			'button_title' => __( 'Add Schedule', 'digent-appointments' ),
			'entity'       => 'schedule',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/schedule-slide-panel.php';

