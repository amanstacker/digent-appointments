<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize business hour breaks.
 *
 * @param array $breaks Raw breaks array.
 * @return array
 */
function dgap_sanitize_breaks( $breaks ) {
	
	$sanitized_breaks = [];

	if ( ! is_array( $breaks ) ) {
		return $sanitized_breaks;
	}

	foreach ( $breaks as $break ) {
		if ( ! is_array( $break ) ) {
			continue;
		}

		$start = sanitize_text_field( $break['start'] ?? '' );
		$end   = sanitize_text_field( $break['end'] ?? '' );

		// Skip empty values.
		if ( empty( $start ) || empty( $end ) ) {
			continue;
		}

		$sanitized_breaks[] = [
			'start' => $start,
			'end'   => $end,
		];
	}

	return array_values( $sanitized_breaks );
}

function dgap_get_form_default_settings() {
		
	$default_fields 	=	[
		"_dgap_form_name" 		=> [
			"name" 			=>	"_dgap_form_name",
			"label" 		=>	esc_html__( "Name", 'digent-appointments' ),
			"type" 			=>	"text",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
		"_dgap_form_phone" 	=> [
			"name" 			=>	"_dgap_form_phone",
			"label" 		=>	esc_html__( "Phone", 'digent-appointments' ),
			"type" 			=>	"phone",
			"required" 		=>	false,
			"is_default"	=>	true,
		],
		"_dgap_form_email" 	=> [
			"name" 			=>	"_dgap_form_email",
			"label" 		=>	esc_html__( "Email", 'digent-appointments' ),
			"type" 			=>	"email",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
		"_dgap_form_description" 	=> [
			"name" 			=>	"_dgap_form_description",
			"label" 		=>	esc_html__( "Description", 'digent-appointments' ),
			"type" 			=>	"textarea",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
	];	

	$default_settings 					=	[];
	$default_settings['layout'] 		=	'layout-1';
	$default_settings['custom_fields'] 	=	$default_fields;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.
	if ( ! empty( $_GET['page'] ) && ! empty( $_GET['id'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.		
		$page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.
		$id   = absint( wp_unslash( $_GET['id'] ?? 0 ) );

		if ( $page === 'digent-appointments-forms' && $id > 0 ) {
			
			$saved_settings = DGAP_Form_Repo::get( $id );	
			
			if ( ! empty( $saved_settings['settings'] ) ) {
				$default_settings['settings'] 	=	maybe_unserialize( $saved_settings['settings'] );
			}
			if ( ! empty( $saved_settings['custom_fields'] ) ) {
				$default_settings['custom_fields'] 	=	maybe_unserialize( $saved_settings['custom_fields'] );
			}

		}
	}
	
	return $default_settings;

}

function dgap_get_form_settings() {
	
	$default_settings 	=	dgap_get_form_default_settings();	

	return $default_settings;

}

/**
 * Render a modern empty state card for list tables when no items exist.
 *
 * @param array $args Configuration array for empty state display.
 */
function dgap_render_empty_state( $args = [] ) {
	$defaults = [
		'icon'         => 'dashicons-info-outline',
		'title'        => __( 'No items found', 'digent-appointments' ),
		'desc'         => __( 'Get started by creating your first record.', 'digent-appointments' ),
		'button_text'  => __( 'Add New', 'digent-appointments' ),
		'button_title' => '',
		'entity'       => '',
		'action_url'   => '',
	];

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['button_title'] ) ) {
		$args['button_title'] = $args['button_text'];
	}
	?>
	<div class="dgap-empty-state">
		<div class="dgap-empty-state-icon">
			<span class="dashicons <?php echo esc_attr( $args['icon'] ); ?>"></span>
		</div>
		<h3 class="dgap-empty-state-title"><?php echo esc_html( $args['title'] ); ?></h3>
		<p class="dgap-empty-state-desc"><?php echo esc_html( $args['desc'] ); ?></p>
		<div class="dgap-empty-state-action">
			<?php if ( ! empty( $args['action_url'] ) ) : ?>
				<a href="<?php echo esc_url( $args['action_url'] ); ?>" class="dgap-btn button button-primary">
					<span class="dashicons dashicons-plus-alt2"></span>
					<span class="dgap-btn-text"><?php echo esc_html( $args['button_text'] ); ?></span>
				</a>
			<?php else : ?>
				<button type="button" class="dgap-btn button button-primary dgap-add" data-entity="<?php echo esc_attr( $args['entity'] ); ?>" data-title="<?php echo esc_attr( $args['button_title'] ); ?>">
					<span class="dashicons dashicons-plus-alt2"></span>
					<span class="dgap-btn-text"><?php echo esc_html( $args['button_text'] ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Get the current onboarding & setup guide status.
 * Evaluates the required dependency chain:
 * Location -> Service -> Staff -> Schedule -> Booking Form.
 *
 * @return array Onboarding progress metrics and step data.
 */
function dgap_get_onboarding_status() {
	global $wpdb;

	$loc_count      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_locations" );
	$service_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_services" );
	$staff_count    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_staff" );
	$schedule_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_schedules" );
	$form_count     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_forms" );

	$steps = [
		'locations' => [
			'step'        => 1,
			'title'       => __( 'Add Location', 'digent-appointments' ),
			'desc'        => __( 'Set up your clinic, salon, branch, or virtual office.', 'digent-appointments' ),
			'entity'      => 'location',
			'btn_title'   => __( 'Add Location', 'digent-appointments' ),
			'url'         => admin_url( 'admin.php?page=digent-appointments-locations' ),
			'count'       => $loc_count,
			'completed'   => $loc_count > 0,
			'unlocked'    => true,
			'icon'        => 'dashicons-location-alt',
		],
		'services'  => [
			'step'        => 2,
			'title'       => __( 'Create Services', 'digent-appointments' ),
			'desc'        => __( 'Define services with duration, buffer time & pricing.', 'digent-appointments' ),
			'entity'      => 'service',
			'btn_title'   => __( 'Add Service', 'digent-appointments' ),
			'url'         => admin_url( 'admin.php?page=digent-appointments-services' ),
			'count'       => $service_count,
			'completed'   => $service_count > 0,
			'unlocked'    => true,
			'icon'        => 'dashicons-admin-tools',
		],
		'staff'     => [
			'step'        => 3,
			'title'       => __( 'Add Staff Members', 'digent-appointments' ),
			'desc'        => __( 'Add team members or specialists who provide services.', 'digent-appointments' ),
			'entity'      => 'staff',
			'btn_title'   => __( 'Add Staff Member', 'digent-appointments' ),
			'url'         => admin_url( 'admin.php?page=digent-appointments-staff' ),
			'count'       => $staff_count,
			'completed'   => $staff_count > 0,
			'unlocked'    => true,
			'icon'        => 'dashicons-groups',
		],
		'schedules' => [
			'step'        => 4,
			'title'       => __( 'Configure Schedules', 'digent-appointments' ),
			'desc'        => __( 'Link staff & services to locations with shift hours & time slots.', 'digent-appointments' ),
			'entity'      => 'schedule',
			'btn_title'   => __( 'Add Schedule', 'digent-appointments' ),
			'url'         => admin_url( 'admin.php?page=digent-appointments-schedules' ),
			'count'       => $schedule_count,
			'completed'   => $schedule_count > 0,
			'unlocked'    => ( $loc_count > 0 && $service_count > 0 && $staff_count > 0 ),
			'icon'        => 'dashicons-clock',
		],
		'forms'     => [
			'step'        => 5,
			'title'       => __( 'Publish Booking Form', 'digent-appointments' ),
			'desc'        => __( 'Build your customer form & embed it on your website with shortcode.', 'digent-appointments' ),
			'entity'      => 'form',
			'btn_title'   => __( 'Create Form', 'digent-appointments' ),
			'url'         => admin_url( 'admin.php?page=digent-appointments-forms&action=new' ),
			'count'       => $form_count,
			'completed'   => $form_count > 0,
			'unlocked'    => true,
			'icon'        => 'dashicons-feedback',
		],
	];

	$completed_count = 0;
	foreach ( $steps as $s ) {
		if ( ! empty( $s['completed'] ) ) {
			$completed_count++;
		}
	}

	$percent   = (int) round( ( $completed_count / 5 ) * 100 );
	$is_ready  = ( $completed_count === 5 );
	$dismissed = (bool) get_user_meta( get_current_user_id(), 'dgap_dismiss_onboarding', true );

	$first_form_id = 0;
	if ( $form_count > 0 ) {
		$first_form_id = (int) $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}dgap_forms ORDER BY id ASC LIMIT 1" );
	}

	return [
		'steps'           => $steps,
		'completed_count' => $completed_count,
		'total_steps'     => 5,
		'percent'         => $percent,
		'is_ready'        => $is_ready,
		'dismissed'       => $dismissed,
		'first_form_id'   => $first_form_id,
		'counts'          => [
			'locations' => $loc_count,
			'services'  => $service_count,
			'staff'     => $staff_count,
			'schedules' => $schedule_count,
			'forms'     => $form_count,
		],
	];
}

/**
 * Handle AJAX request to toggle or dismiss the onboarding guide.
 */
add_action( 'wp_ajax_dgap_toggle_onboarding_guide', function () {
	check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized', 'digent-appointments' ) ] );
	}

	$dismiss = isset( $_POST['dismiss'] ) ? (int) $_POST['dismiss'] : 0;
	update_user_meta( get_current_user_id(), 'dgap_dismiss_onboarding', $dismiss );

	wp_send_json_success( [ 'dismissed' => (bool) $dismiss ] );
} );

/**
 * Retrieve aggregated real-time metrics and operational feeds for the Dashboard.
 *
 * @return array
 */
function dgap_get_dashboard_overview() {
	global $wpdb;

	$today       = current_time( 'Y-m-d' );
	$month_start = current_time( 'Y-m-01' );
	$weekday     = strtolower( current_time( 'l' ) );

	// 1. KPI Metrics
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$today_bookings = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}dgap_bookings WHERE booking_date = %s AND status != 'cancelled'",
			$today
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$pending_bookings = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->prefix}dgap_bookings WHERE status = 'pending'"
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$monthly_revenue = (float) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(price), 0) FROM {$wpdb->prefix}dgap_bookings WHERE status IN ('confirmed', 'completed') AND booking_date >= %s",
			$month_start
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_customers = (int) $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->prefix}dgap_customers WHERE status = 1"
	);

	// 2. Upcoming Appointments (Next 6)
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$upcoming_bookings = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT
				b.id,
				b.booking_date,
				b.start_time,
				b.end_time,
				b.price,
				b.status,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				s.name AS service_name,
				l.name AS location_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
			LEFT JOIN {$wpdb->prefix}dgap_staff st ON st.id = b.staff_id
			WHERE b.booking_date >= %s
			ORDER BY b.booking_date ASC, b.start_time ASC
			LIMIT 5
			",
			$today
		),
		ARRAY_A
	);

	// 3. Staff On-Duty Today & Vacations
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$staff_list = $wpdb->get_results(
		"SELECT id, first_name, last_name, email FROM {$wpdb->prefix}dgap_staff WHERE status = 1 ORDER BY id ASC LIMIT 8",
		ARRAY_A
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$active_schedules = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT staff_id, availability FROM {$wpdb->prefix}dgap_schedules WHERE status = 1 AND date_start <= %s AND (is_infinite = 1 OR date_end IS NULL OR date_end >= %s)",
			$today,
			$today
		),
		ARRAY_A
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$active_vacations = $wpdb->get_results(
		"SELECT id, name, type, entity_ids, dates FROM {$wpdb->prefix}dgap_vacations WHERE status = 1",
		ARRAY_A
	);

	$staff_name_map = [];
	foreach ( $staff_list as $st ) {
		$staff_name_map[ (int) $st['id'] ] = trim( $st['first_name'] . ' ' . $st['last_name'] );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$all_services_rows = $wpdb->get_results(
		"SELECT id, name FROM {$wpdb->prefix}dgap_services WHERE status = 1",
		ARRAY_A
	);
	$service_name_map = [];
	foreach ( $all_services_rows as $sr ) {
		$service_name_map[ (int) $sr['id'] ] = $sr['name'];
	}

	$vacation_staff_ids = [];
	$upcoming_vacations = [];

	foreach ( $active_vacations as $to ) {
		$dates = json_decode( $to['dates'] ?? '[]', true );
		$ids   = maybe_unserialize( $to['entity_ids'] );
		if ( ! is_array( $ids ) ) {
			$ids = json_decode( $to['entity_ids'] ?? '[]', true );
		}

		if ( ! is_array( $dates ) ) {
			$dates = [];
		}
		if ( ! is_array( $ids ) ) {
			$ids = [];
		}

		$upcoming_dates = [];
		foreach ( $dates as $d ) {
			if ( empty( $d['date'] ) ) {
				continue;
			}
			if ( $to['type'] === 'staff' && $d['date'] === $today ) {
				foreach ( $ids as $sid ) {
					$vacation_staff_ids[ (int) $sid ] = true;
				}
			}
			if ( $d['date'] >= $today ) {
				$upcoming_dates[] = $d;
			}
		}

		if ( ! empty( $upcoming_dates ) ) {
			usort(
				$upcoming_dates,
				function ( $a, $b ) {
					return strcmp( $a['date'], $b['date'] );
				}
			);

			$entity_labels = [];
			$lookup_map    = ( $to['type'] === 'staff' ) ? $staff_name_map : $service_name_map;
			foreach ( $ids as $eid ) {
				$eid = (int) $eid;
				if ( isset( $lookup_map[ $eid ] ) ) {
					$entity_labels[] = $lookup_map[ $eid ];
				}
			}

			$first_day  = $upcoming_dates[0];
			$days_count = count( $upcoming_dates );
			$is_partial = ( ( $first_day['mode'] ?? 'all' ) === 'time' && ! empty( $first_day['time_start'] ) && ! empty( $first_day['time_end'] ) );

			$upcoming_vacations[] = [
				'id'            => (int) $to['id'],
				'name'          => $to['name'],
				'type'          => $to['type'],
				'entities_text' => ! empty( $entity_labels ) ? implode( ', ', array_slice( $entity_labels, 0, 2 ) ) . ( count( $entity_labels ) > 2 ? ' +' . ( count( $entity_labels ) - 2 ) : '' ) : ucfirst( $to['type'] ),
				'next_date'     => $first_day['date'],
				'days_count'    => $days_count,
				'time_label'    => $is_partial
					? date_i18n( 'g:i A', strtotime( $first_day['time_start'] ) ) . ' – ' . date_i18n( 'g:i A', strtotime( $first_day['time_end'] ) )
					: __( 'Full Day', 'digent-appointments' ),
			];
		}
	}

	usort(
		$upcoming_vacations,
		function ( $a, $b ) {
			return strcmp( $a['next_date'], $b['next_date'] );
		}
	);
	$upcoming_vacations = array_slice( $upcoming_vacations, 0, 5 );

	$staff_shifts = [];
	foreach ( $active_schedules as $sched ) {
		$sid   = (int) $sched['staff_id'];
		$avail = json_decode( $sched['availability'] ?? '{}', true );
		if ( isset( $avail[ $weekday ] ) && ( $avail[ $weekday ]['status'] ?? '' ) === 'open' ) {
			$open  = $avail[ $weekday ]['open'] ?? '';
			$close = $avail[ $weekday ]['close'] ?? '';
			if ( $open && $close ) {
				$staff_shifts[ $sid ] = [
					'open'  => $open,
					'close' => $close,
				];
			}
		}
	}

	$staff_duty = [];
	foreach ( $staff_list as $st ) {
		$sid        = (int) $st['id'];
		$full_name  = trim( $st['first_name'] . ' ' . $st['last_name'] );
		$on_vac     = ! empty( $vacation_staff_ids[ $sid ] );
		$shift      = $staff_shifts[ $sid ] ?? null;

		$staff_duty[] = [
			'id'          => $sid,
			'name'        => $full_name,
			'on_vacation' => $on_vac,
			'is_working'  => ! $on_vac && ! empty( $shift ),
			'hours'       => $shift ? date_i18n( 'g:i A', strtotime( $shift['open'] ) ) . ' – ' . date_i18n( 'g:i A', strtotime( $shift['close'] ) ) : __( 'Off Today', 'digent-appointments' ),
		];
	}

	// 4. Top Performing Services (Top 4)
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$top_services = $wpdb->get_results(
		"
		SELECT
			s.id,
			s.name,
			s.price,
			s.duration,
			COUNT(b.id) AS bookings_count
		FROM {$wpdb->prefix}dgap_services s
		LEFT JOIN {$wpdb->prefix}dgap_bookings b ON b.service_id = s.id AND b.status != 'cancelled'
		WHERE s.status = 1
		GROUP BY s.id
		ORDER BY bookings_count DESC, s.id ASC
		LIMIT 4
		",
		ARRAY_A
	);

	// 5. Active Booking Forms
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$booking_forms = $wpdb->get_results(
		"SELECT id, name, layout FROM {$wpdb->prefix}dgap_forms WHERE status = 1 ORDER BY id ASC LIMIT 5",
		ARRAY_A
	);

	return [
		'kpis' => [
			'today_bookings'   => $today_bookings,
			'pending_bookings' => $pending_bookings,
			'monthly_revenue'  => $monthly_revenue,
			'total_customers'  => $total_customers,
		],
		'upcoming_bookings'  => $upcoming_bookings ?: [],
		'staff_duty'         => $staff_duty,
		'upcoming_vacations' => $upcoming_vacations,
		'top_services'       => $top_services ?: [],
		'booking_forms'      => $booking_forms ?: [],
	];
}


