<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$overview           = dgap_get_dashboard_overview();
$kpis               = $overview['kpis'];
$upcoming_bookings  = $overview['upcoming_bookings'];
$staff_duty         = $overview['staff_duty'];
$upcoming_vacations = $overview['upcoming_vacations'];
$top_services       = $overview['top_services'];
$booking_forms      = $overview['booking_forms'];

$today_date    = current_time( 'Y-m-d' );
$tomorrow_date = date_i18n( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );
$today_day     = current_time( 'l' );

$max_service_count = 1;
foreach ( $top_services as $srv ) {
	if ( (int) $srv['bookings_count'] > $max_service_count ) {
		$max_service_count = (int) $srv['bookings_count'];
	}
}
?>

<div class="dgap-dashboard-overview">

	<!-- 1. KPI Summary Strip -->
	<div class="dgap-kpi-grid">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-calendar-view' ) ); ?>" class="dgap-kpi-card">
			<div class="dgap-kpi-icon is-emerald">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="dgap-kpi-content">
				<span class="dgap-kpi-label"><?php esc_html_e( "Today's Appointments", 'digent-appointments' ); ?></span>
				<strong class="dgap-kpi-value"><?php echo esc_html( number_format_i18n( $kpis['today_bookings'] ) ); ?></strong>
			</div>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-list-view' ) ); ?>" class="dgap-kpi-card">
			<div class="dgap-kpi-icon <?php echo $kpis['pending_bookings'] > 0 ? 'is-amber' : 'is-slate'; ?>">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="dgap-kpi-content">
				<span class="dgap-kpi-label"><?php esc_html_e( 'Pending Approvals', 'digent-appointments' ); ?></span>
				<strong class="dgap-kpi-value"><?php echo esc_html( number_format_i18n( $kpis['pending_bookings'] ) ); ?></strong>
			</div>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-list-view' ) ); ?>" class="dgap-kpi-card">
			<div class="dgap-kpi-icon is-emerald">
				<span class="dashicons dashicons-chart-area"></span>
			</div>
			<div class="dgap-kpi-content">
				<span class="dgap-kpi-label"><?php esc_html_e( 'Monthly Revenue', 'digent-appointments' ); ?></span>
				<strong class="dgap-kpi-value">$<?php echo esc_html( number_format( (float) $kpis['monthly_revenue'], 2 ) ); ?></strong>
			</div>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-customers' ) ); ?>" class="dgap-kpi-card">
			<div class="dgap-kpi-icon is-indigo">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="dgap-kpi-content">
				<span class="dgap-kpi-label"><?php esc_html_e( 'Active Customers', 'digent-appointments' ); ?></span>
				<strong class="dgap-kpi-value"><?php echo esc_html( number_format_i18n( $kpis['total_customers'] ) ); ?></strong>
			</div>
		</a>
	</div>

	<!-- 2. Main Two-Column Overview Layout -->
	<div class="dgap-overview-columns">

		<!-- Left Column: Upcoming Appointments + Published Forms -->
		<div class="dgap-overview-main">

			<!-- Upcoming Appointments -->
			<div class="dgap-dash-card">
				<div class="dgap-dash-card-header">
					<div>
						<h3><?php esc_html_e( 'Upcoming Appointments', 'digent-appointments' ); ?></h3>
						<span class="dgap-dash-card-sub"><?php esc_html_e( 'Next scheduled bookings from today onwards', 'digent-appointments' ); ?></span>
					</div>
					<div class="dgap-dash-card-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-calendar-view' ) ); ?>" class="dgap-link-pill">
							<?php esc_html_e( 'Calendar View', 'digent-appointments' ); ?> &rarr;
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-list-view' ) ); ?>" class="dgap-link-pill is-secondary">
							<?php esc_html_e( 'List View', 'digent-appointments' ); ?>
						</a>
					</div>
				</div>

				<div class="dgap-dash-card-body">
					<?php if ( ! empty( $upcoming_bookings ) ) : ?>
						<div class="dgap-upcoming-list">
							<?php foreach ( $upcoming_bookings as $b ) :
								$cust_name  = trim( $b['customer_name'] ?? '' ) ?: __( 'Guest Customer', 'digent-appointments' );
								$initials   = strtoupper( mb_substr( $cust_name, 0, 1 ) );
								$b_date     = $b['booking_date'];
								if ( $b_date === $today_date ) {
									$date_label = __( 'Today', 'digent-appointments' );
								} elseif ( $b_date === $tomorrow_date ) {
									$date_label = __( 'Tomorrow', 'digent-appointments' );
								} else {
									$date_label = date_i18n( 'M j', strtotime( $b_date ) );
								}
								$time_str   = date_i18n( 'g:i A', strtotime( $b['start_time'] ) );
								$status_raw = strtolower( $b['status'] ?? 'pending' );
								$status_map = [
									'pending'   => 'is-warning',
									'confirmed' => 'is-success',
									'approved'  => 'is-success',
									'completed' => 'is-success',
									'cancelled' => 'is-danger',
								];
								$status_cls = $status_map[ $status_raw ] ?? 'is-neutral';
							?>
								<div class="dgap-upcoming-row">
									<div class="dgap-upcoming-time">
										<span class="dgap-time-day <?php echo $b_date === $today_date ? 'is-today' : ''; ?>"><?php echo esc_html( $date_label ); ?></span>
										<span class="dgap-time-clock"><?php echo esc_html( $time_str ); ?></span>
									</div>

									<div class="dgap-upcoming-client">
										<span class="dgap-avatar-mini"><?php echo esc_html( $initials ); ?></span>
										<div class="dgap-client-meta">
											<strong><?php echo esc_html( $cust_name ); ?></strong>
											<span>
												<?php echo esc_html( $b['service_name'] ?: __( 'Service', 'digent-appointments' ) ); ?>
												<?php if ( ! empty( $b['staff_name'] ) ) : ?>
													&bull; <?php echo esc_html( $b['staff_name'] ); ?>
												<?php endif; ?>
											</span>
										</div>
									</div>

									<div class="dgap-upcoming-right">
										<span class="dgap-upcoming-price">$<?php echo esc_html( number_format( (float) $b['price'], 2 ) ); ?></span>
										<span class="dgap-status-pill <?php echo esc_attr( $status_cls ); ?>">
											<?php echo esc_html( ucfirst( $status_raw ) ); ?>
										</span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="dgap-dash-empty">
							<span class="dashicons dashicons-calendar-alt"></span>
							<p><?php esc_html_e( 'No upcoming appointments scheduled yet.', 'digent-appointments' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-calendar-view' ) ); ?>" class="button button-secondary dgap-btn-sm-inline">
								<?php esc_html_e( 'Open Calendar', 'digent-appointments' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Active Booking Forms & Shortcodes -->
			<div class="dgap-dash-card">
				<div class="dgap-dash-card-header">
					<div>
						<h3><?php esc_html_e( 'Published Booking Forms', 'digent-appointments' ); ?></h3>
						<span class="dgap-dash-card-sub"><?php esc_html_e( 'Copy shortcodes and paste them on any WordPress page', 'digent-appointments' ); ?></span>
					</div>
					<div class="dgap-dash-card-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-forms&action=new' ) ); ?>" class="dgap-link-pill">
							+ <?php esc_html_e( 'New Form', 'digent-appointments' ); ?>
						</a>
					</div>
				</div>

				<div class="dgap-dash-card-body">
					<?php if ( ! empty( $booking_forms ) ) : ?>
						<div class="dgap-forms-embed-list">
							<?php foreach ( $booking_forms as $f ) :
								$sc = sprintf( '[dgap_booking_form id="%d"]', (int) $f['id'] );
								$layout_label = ucfirst( str_replace( '-', ' ', $f['layout'] ?: 'layout-1' ) );
							?>
								<div class="dgap-form-embed-row">
									<div class="dgap-form-embed-info">
										<strong><?php echo esc_html( $f['name'] ); ?></strong>
										<span class="dgap-layout-tag"><?php echo esc_html( $layout_label ); ?></span>
									</div>
									<div class="dgap-form-embed-actions">
										<code class="dgap-shortcode-snippet"><?php echo esc_html( $sc ); ?></code>
										<button type="button" class="button button-secondary dgap-copy-shortcode dgap-copy-mini" data-shortcode='<?php echo esc_attr( $sc ); ?>'>
											<span class="dashicons dashicons-clipboard"></span>
											<span><?php esc_html_e( 'Copy', 'digent-appointments' ); ?></span>
										</button>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-forms&action=edit&id=' . (int) $f['id'] ) ); ?>" class="dgap-icon-edit" title="<?php esc_attr_e( 'Edit Form', 'digent-appointments' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="dgap-dash-empty">
							<span class="dashicons dashicons-feedback"></span>
							<p><?php esc_html_e( 'No booking forms created yet.', 'digent-appointments' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-forms&action=new' ) ); ?>" class="button button-primary dgap-btn-sm-inline">
								<?php esc_html_e( 'Create Booking Form', 'digent-appointments' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<!-- Right Column: Staff On-Duty + Popular Services -->
		<div class="dgap-overview-side">

			<!-- Staff On-Duty Today -->
			<div class="dgap-dash-card">
				<div class="dgap-dash-card-header">
					<div>
						<h3><?php esc_html_e( 'Staff On-Duty Today', 'digent-appointments' ); ?></h3>
						<span class="dgap-dash-card-sub"><?php echo esc_html( $today_day ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-vacations' ) ); ?>" class="dgap-link-pill is-secondary">
						<?php esc_html_e( 'Vacations', 'digent-appointments' ); ?>
					</a>
				</div>

				<div class="dgap-dash-card-body">
					<?php if ( ! empty( $staff_duty ) ) : ?>
						<div class="dgap-duty-list">
							<?php foreach ( $staff_duty as $sd ) : ?>
								<div class="dgap-duty-row">
									<div class="dgap-duty-person">
										<span class="dgap-duty-dot <?php echo $sd['on_vacation'] ? 'is-amber' : ( $sd['is_working'] ? 'is-emerald' : 'is-slate' ); ?>"></span>
										<strong><?php echo esc_html( $sd['name'] ); ?></strong>
									</div>
									<div class="dgap-duty-status">
										<?php if ( $sd['on_vacation'] ) : ?>
											<span class="dgap-status-pill is-warning"><?php esc_html_e( 'On Vacation', 'digent-appointments' ); ?></span>
										<?php elseif ( $sd['is_working'] ) : ?>
											<span class="dgap-duty-hours"><?php echo esc_html( $sd['hours'] ); ?></span>
										<?php else : ?>
											<span class="dgap-duty-off"><?php esc_html_e( 'Off Today', 'digent-appointments' ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="dgap-dash-empty is-compact">
							<p><?php esc_html_e( 'No active staff members found.', 'digent-appointments' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Upcoming Vacations & Time Off -->
			<div class="dgap-dash-card">
				<div class="dgap-dash-card-header">
					<div>
						<h3><?php esc_html_e( 'Upcoming Vacations', 'digent-appointments' ); ?></h3>
						<span class="dgap-dash-card-sub"><?php esc_html_e( 'Scheduled staff & service time off', 'digent-appointments' ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-vacations' ) ); ?>" class="dgap-link-pill is-secondary">
						+ <?php esc_html_e( 'Add', 'digent-appointments' ); ?>
					</a>
				</div>

				<div class="dgap-dash-card-body">
					<?php if ( ! empty( $upcoming_vacations ) ) : ?>
						<div class="dgap-vacation-list">
							<?php foreach ( $upcoming_vacations as $vac ) :
								$vac_date = $vac['next_date'];
								if ( $vac_date === $today_date ) {
									$vac_date_label = __( 'Today', 'digent-appointments' );
								} elseif ( $vac_date === $tomorrow_date ) {
									$vac_date_label = __( 'Tomorrow', 'digent-appointments' );
								} else {
									$vac_date_label = date_i18n( 'M j, Y', strtotime( $vac_date ) );
								}
							?>
								<div class="dgap-vacation-row">
									<div class="dgap-vacation-icon">
										<span class="dashicons dashicons-palmtree"></span>
									</div>
									<div class="dgap-vacation-info">
										<strong><?php echo esc_html( $vac['name'] ); ?></strong>
										<span><?php echo esc_html( $vac['entities_text'] ); ?> &bull; <?php echo esc_html( $vac['time_label'] ); ?></span>
									</div>
									<div class="dgap-vacation-meta">
										<span class="dgap-status-pill is-warning"><?php echo esc_html( $vac_date_label ); ?></span>
										<?php if ( $vac['days_count'] > 1 ) : ?>
											<span class="dgap-vacation-days"><?php echo esc_html( sprintf( __( '%d days', 'digent-appointments' ), $vac['days_count'] ) ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="dgap-dash-empty is-compact">
							<span class="dashicons dashicons-palmtree"></span>
							<p><?php esc_html_e( 'No upcoming vacations or time off scheduled.', 'digent-appointments' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Popular Services -->
			<div class="dgap-dash-card">
				<div class="dgap-dash-card-header">
					<div>
						<h3><?php esc_html_e( 'Popular Services', 'digent-appointments' ); ?></h3>
						<span class="dgap-dash-card-sub"><?php esc_html_e( 'Most booked offerings', 'digent-appointments' ); ?></span>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-services' ) ); ?>" class="dgap-link-pill is-secondary">
						<?php esc_html_e( 'Manage', 'digent-appointments' ); ?>
					</a>
				</div>

				<div class="dgap-dash-card-body">
					<?php if ( ! empty( $top_services ) ) : ?>
						<div class="dgap-popular-list">
							<?php foreach ( $top_services as $srv ) :
								$cnt   = (int) $srv['bookings_count'];
								$width = $cnt > 0 ? max( 8, (int) round( ( $cnt / $max_service_count ) * 100 ) ) : 0;
							?>
								<div class="dgap-popular-item">
									<div class="dgap-popular-top">
										<span class="dgap-popular-name"><?php echo esc_html( $srv['name'] ); ?></span>
										<span class="dgap-popular-count">
											<?php echo esc_html( sprintf( _n( '%d booking', '%d bookings', $cnt, 'digent-appointments' ), $cnt ) ); ?>
										</span>
									</div>
									<div class="dgap-popular-bar-track">
										<div class="dgap-popular-bar-fill" style="width: <?php echo esc_attr( $width ); ?>%;"></div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="dgap-dash-empty is-compact">
							<p><?php esc_html_e( 'No services configured yet.', 'digent-appointments' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>

	</div>
</div>
