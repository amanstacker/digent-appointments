<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_onboarding = dgap_get_onboarding_status();
$is_dismissed    = $dgap_onboarding['dismissed'];
$is_ready        = $dgap_onboarding['is_ready'];
$percent         = $dgap_onboarding['percent'];
$completed_count = $dgap_onboarding['completed_count'];
$total_steps     = $dgap_onboarding['total_steps'];
?>

<div class="dgap-onboarding-wrapper <?php echo $is_dismissed ? 'dgap-onboarding-collapsed' : ''; ?>" id="dgap-onboarding-wrapper">

	<!-- Mini Bar (When collapsed or dismissed) -->
	<div class="dgap-onboarding-minibar" id="dgap-onboarding-minibar" <?php echo ! $is_dismissed ? 'style="display:none;"' : ''; ?>>
		<div class="dgap-minibar-left">
			<span class="dashicons <?php echo $is_ready ? 'dashicons-yes-alt dgap-text-emerald' : 'dashicons-flag'; ?>"></span>
			<strong><?php echo $is_ready ? esc_html__( 'Setup Complete (5/5)', 'digent-appointments' ) : esc_html__( 'Booking Setup Guide', 'digent-appointments' ); ?></strong>
			<span class="dgap-minibar-progress"><?php echo esc_html( "{$percent}% — {$completed_count} of {$total_steps} completed" ); ?></span>
		</div>
		<div class="dgap-minibar-right">
			<button type="button" class="dgap-btn button button-secondary dgap-toggle-onboarding" data-action="expand">
				<?php esc_html_e( 'Show Setup Guide', 'digent-appointments' ); ?> <span class="dashicons dashicons-arrow-down-alt2"></span>
			</button>
		</div>
	</div>

	<!-- Full Onboarding Card -->
	<div class="dgap-onboarding-card" id="dgap-onboarding-card" <?php echo $is_dismissed ? 'style="display:none;"' : ''; ?>>
		
		<!-- Header -->
		<div class="dgap-onboarding-header">
			<div class="dgap-onboarding-header-left">
				<div class="dgap-onboarding-badge-icon <?php echo $is_ready ? 'is-complete' : ''; ?>">
					<span class="dashicons <?php echo $is_ready ? 'dashicons-yes-alt' : 'dashicons-flag'; ?>"></span>
				</div>
				<div>
					<h2 class="dgap-onboarding-title">
						<?php echo $is_ready ? esc_html__( '🎉 Your Booking System is Ready!', 'digent-appointments' ) : esc_html__( 'Set Up Digent Appointments', 'digent-appointments' ); ?>
					</h2>
					<p class="dgap-onboarding-subtitle">
						<?php echo $is_ready ? esc_html__( 'All 5 prerequisites are configured. Clients can now book appointments online. Just place form shortcode on pages where you want to accept bookings.', 'digent-appointments' ) : esc_html__( 'Follow these 5 essential steps in order to configure locations, services, worker, connections, and booking forms.', 'digent-appointments' ); ?>
					</p>
				</div>
			</div>

			<div class="dgap-onboarding-header-right">
				<div class="dgap-onboarding-metric">
					<span class="dgap-metric-percent"><?php echo esc_html( $percent ); ?>%</span>
					<span class="dgap-metric-label"><?php echo esc_html( "{$completed_count}/{$total_steps} Completed" ); ?></span>
				</div>
				<button type="button" class="dgap-onboarding-dismiss-btn dgap-toggle-onboarding" data-action="collapse" title="<?php esc_attr_e( 'Minimize Guide', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-arrow-up-alt2"></span>
				</button>
			</div>
		</div>

		<!-- Progress Bar Track -->
		<div class="dgap-onboarding-progress-track">
			<div class="dgap-onboarding-progress-fill" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
		</div>

		<!-- Steps List -->
		<div class="dgap-onboarding-steps">
			<?php foreach ( $dgap_onboarding['steps'] as $key => $step ) : 
				$is_step_completed = ! empty( $step['completed'] );
				$is_step_unlocked  = ! empty( $step['unlocked'] );
				$step_class        = $is_step_completed ? 'is-completed' : ( $is_step_unlocked ? 'is-active' : 'is-locked' );
			?>
				<div class="dgap-onboarding-step <?php echo esc_attr( $step_class ); ?>">
					
					<div class="dgap-step-left">
						<div class="dgap-step-indicator">
							<?php if ( $is_step_completed ) : ?>
								<span class="dashicons dashicons-yes"></span>
							<?php elseif ( ! $is_step_unlocked ) : ?>
								<span class="dashicons dashicons-lock"></span>
							<?php else : ?>
								<span><?php echo esc_html( $step['step'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="dgap-step-body">
						<div class="dgap-step-title-wrap">
							<h4 class="dgap-step-title">
								<?php echo esc_html( $step['title'] ); ?>
							</h4>
							<?php if ( $is_step_completed ) : ?>
								<span class="dgap-badge dgap-badge-active">
									<?php echo esc_html( sprintf( _n( '%d Added', '%d Added', $step['count'], 'digent-appointments' ), $step['count'] ) ); ?>
								</span>
							<?php elseif ( ! $is_step_unlocked ) : ?>
								<span class="dgap-badge dgap-badge-locked">
									<?php esc_html_e( 'Prerequisite Needed', 'digent-appointments' ); ?>
								</span>
							<?php else : ?>
								<span class="dgap-badge dgap-badge-pending">
									<?php esc_html_e( 'Required', 'digent-appointments' ); ?>
								</span>
							<?php endif; ?>
						</div>
						<p class="dgap-step-desc"><?php echo esc_html( $step['desc'] ); ?></p>
					</div>

					<div class="dgap-step-action">
						<?php if ( $is_step_completed ) : ?>
							<a href="<?php echo esc_url( $step['url'] ); ?>" class="dgap-btn button button-secondary dgap-btn-sm">
								<?php esc_html_e( 'Manage', 'digent-appointments' ); ?>
							</a>
						<?php elseif ( ! $is_step_unlocked ) : ?>
							<button type="button" class="dgap-btn button button-secondary dgap-btn-sm dgap-btn-disabled" disabled title="<?php esc_attr_e( 'Complete Location, Service, and Worker steps first', 'digent-appointments' ); ?>">
								<span class="dashicons dashicons-lock"></span>
								<span><?php esc_html_e( 'Locked', 'digent-appointments' ); ?></span>
							</button>
						<?php else : ?>
							<a href="<?php echo esc_url( $step['url'] ); ?>" class="dgap-btn button button-primary dgap-btn-sm">
								<span class="dashicons dashicons-plus-alt2"></span>
								<span class="dgap-btn-text"><?php echo esc_html( $step['btn_title'] ); ?></span>
							</a>
						<?php endif; ?>
					</div>

				</div>
			<?php endforeach; ?>
		</div>

	</div>
</div>
